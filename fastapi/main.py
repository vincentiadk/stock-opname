import os
import time
import logging
from typing import Optional, Dict, Any
from db import get_oracle_connection

from fastapi import FastAPI, BackgroundTasks, Depends, Header, HTTPException, status, Query, Path, Request
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel, Field
from dotenv import load_dotenv

# --- env & logging ---
load_dotenv()
APP_NAME = os.getenv("APP_NAME", "StockOpname Sync API")
FASTAPI_TOKEN = os.getenv("FASTAPI_TOKEN", "").strip()
# Oracle

ALLOWED_UPDATE_COLUMNS = {
    "LOCATION_ID",
    "LOCATION_SHELF_ID",
    "LOCATION_RUGS_ID",
    # tambah kolom lain yang memang boleh di-push dari job:
    # "LOCATION_FLOOR_ID", "IS_STOCKTAKEN", "NOTE", dst...
}

HISTORY_TABLE = "HISTORYDATA"
H_LOCATIONS_TABLE = "LOCATIONS"
H_LOC_ID_COL = "ID"
H_LOC_NAME_COL = "NAME"

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s | %(levelname)s | %(name)s | %(message)s",
)
log = logging.getLogger("stockopname.sync")

# --- app ---
app = FastAPI(title=APP_NAME, version="1.0.0")

# aktifkan CORS bila perlu
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],            # sesuaikan untuk produksi
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# --- models ---

class SyncPayload(BaseModel):
    requested_by: Optional[str] = Field(default=None, description="Username yang memicu sync")
    request_ip:   Optional[str] = Field(default=None, description="IP pemicu sync")
    extra:        Optional[Dict[str, Any]] = Field(default=None, description="Data tambahan opsional")

class SyncResult(BaseModel):
    id: str
    status: str
    processed_at: float
    detail: Dict[str, Any]

class EnqueueResult(BaseModel):
    id: str
    status: str
    queued: bool
    message: str

# --- auth dependency (opsional Bearer) ---
def verify_bearer_token(authorization: Optional[str] = Header(default=None)) -> None:
    """
    Jika FASTAPI_TOKEN di-set, maka semua request harus bawa Authorization: Bearer <token>.
    Kalau FASTAPI_TOKEN kosong, auth dimatikan (allow all).
    """
    if FASTAPI_TOKEN:
        if not authorization or not authorization.lower().startswith("bearer "):
            raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Missing bearer token")
        token = authorization.split(" ", 1)[1].strip()
        if token != FASTAPI_TOKEN:
            raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Invalid bearer token")


# ---- Helper: ambil nama lokasi sekali dari job.LOCATION_ID ----
def _get_location_name(conn, job_row: dict) -> str:
    loc_id = job_row.get("LOCATION_ID")
    if not loc_id:
        return "-"
    cur = conn.cursor()
    cur.execute("""
        SELECT NAME
          FROM LOCATIONS
         WHERE ID = :id
    """, {"id": loc_id})
    r = cur.fetchone()
    return r[0] if r and r[0] else f"{loc_id}"

# ---- Helper: ambil ID collections dari daftar barcode ----
def _get_collections_by_barcodes(conn, barcodes: list[str]) -> list[dict]:
    if not barcodes:
        return []
    cur = conn.cursor()
    # oracledb: IN membutuhkan placeholder dinamis
    placeholders = ",".join([f":b{i}" for i in range(len(barcodes))])
    params = {f"b{i}": bc for i, bc in enumerate(barcodes)}
    cur.execute(
        f"""
        SELECT ID, NOMORBARCODE
          FROM collections
         WHERE NOMORBARCODE IN ({placeholders})
        """,
        params,
    )
    rows = cur.fetchall()
    return [{"ID": r[0], "NOMORBARCODE": r[1]} for r in rows]

# ---- Helper: ambil ID collection dari satu barcode ----
def _get_collection_id_by_barcode(conn, barcode: str) -> int | None:
    """
    Ambil ID koleksi dari NOMORBARCODE. Return None kalau tidak ketemu.
    """
    bc = (barcode or "").strip()
    if not bc:
        return None
    cur = conn.cursor()
    cur.execute(
        """
        SELECT ID
          FROM collections
         WHERE NOMORBARCODE = :bc
        """,
        {"bc": bc},
    )
    r = cur.fetchone()
    return int(r[0]) if r and r[0] is not None else None

# ---- Helper: insert banyak history sekaligus ----
def _bulk_insert_history(conn, idrefs: list[int], note: str, actionby: str, terminal: str) -> None:
    if not idrefs:
        return
    cur = conn.cursor()
    sql = f"""
        INSERT INTO {HISTORY_TABLE}
            (TABLENAME, ACTION, IDREF, NOTE, ACTIONBY, ACTIONDATE, ACTIONTERMINAL)
        VALUES
            (:tablename, :action, :idref, :note, :actionby, SYSTIMESTAMP, :terminal)
    """
    batch = []
    for _id in idrefs:
        batch.append({
            "tablename": "collections",
            "action": "Edit",
            "idref": int(_id),
            "note": note,
            "actionby": actionby or "fastapi",
            "terminal": terminal or "-",
        })
    cur.executemany(sql, batch)

# --- ambil job sebagai mapping (bukan tuple) ---
def _fetch_job(stockopname_id: str) -> dict:
    cols = ["ID","LISTDATA", "JENIS", "LOCATION_ID", "LOCATION_SHELF_ID", "LOCATION_RUGS_ID", "STOCKOPNAMEID"]
    col_list = ", ".join(cols)
    with get_oracle_connection() as conn:
        cur = conn.cursor()
        cur.execute(f"""
            SELECT {col_list}
            FROM STOCKOPNAMEJOBS
            WHERE ID = :id
        """, {"id": stockopname_id})
        row = cur.fetchone()

        if not row:
            log.warning("Job tidak ditemukan | id=%s (dicoba kolom STOCKOPNAMEID/ID)", stockopname_id)
            raise HTTPException(status_code=404, detail=f"STOCKOPNAMEJOBS id={stockopname_id} tidak ditemukan")

        # row = tuple -> map ke dict pakai zip
        job = {col: row[i] for i, col in enumerate(cols)}
        return job

def _build_update_set(job_row: dict) -> dict:
    """
    Dari row job, ambil hanya kolom yang ada di whitelist.
    Hasilnya dipakai untuk SET ... di UPDATE collections.
    """
    update_set = {k: job_row[k] for k in ALLOWED_UPDATE_COLUMNS if k in job_row}
    return update_set

def _bulk_update_by_barcodes(conn, stockopname_id: str, barcodes: list[str], update_set: dict, requested_by: Optional[str], requested_ip: Optional[str]) -> int:
    """
    Bulk update collections WHERE NOMORBARCODE IN (list), namun tetap aman pakai executemany
    agar kompatibel lintas RDBMS dan menghindari batas panjang query.
    Kolom SET diambil dari update_set + metadata opname.
    """
    if not barcodes:
        return 0

    # Siapkan bagian SET dinamis
    set_clauses = []
    params_template = {
        #"sid": stockopname_id, 
        "sby": (requested_by or "fastapi"), 
        "sip" : (requested_ip or "127.0.0.1")
        }
    for col in update_set:
        set_clauses.append(f"{col} = :{col}")
        params_template[col] = update_set[col]

    # kolom standar untuk jejak opname
    set_clauses.extend([
        #"STOCKOPNAME_ID = :sid",
        "UPDATEDATE = CURRENT_TIMESTAMP",
        "UPDATEBY = :sby",
        "UPDATETERMINAL = :sip",
        "SHELVING_DATE = CURRENT_TIMESTAMP",
        "SHELVING_BY = :sby"
    ])
    set_sql = ", ".join(set_clauses)

    sql = f"""
        UPDATE collections
           SET {set_sql}
         WHERE NOMORBARCODE = :barcode
    """
    cur = conn.cursor()
    # executemany
    batch_params = []
    for bc in barcodes:
        p = dict(params_template)
        p["barcode"] = bc
        batch_params.append(p)
    cur.executemany(sql, batch_params)
    return cur.rowcount if cur.rowcount is not None else 0

def _update_by_rfid(conn, stockopname_id: str, serial_number: str, update_set: dict, requested_by: Optional[str], requested_ip: Optional[str]) -> int:
    """
    Cari RFID_NO via SERIAL_NUMBER, lalu update collections WHERE NOMORBARCODE = RFID_NO
    memakai update_set yang sama.
    """
    cur = conn.cursor()
    cur.execute("""
        SELECT RFID_NO
          FROM RFID_COLLECTIONS
         WHERE SERIAL_NUMBER = :sn
    """,{"sn": serial_number.strip()})
    r = cur.fetchone()
    if not r or not r[0]:
        return 0
    rfid_no = r[0]

    # build SET dinamis (reuse logic dari bulk, tapi single target)
    set_clauses = []
    params = {"sid": stockopname_id, "rfid_no": rfid_no, "sby": (requested_by or "fastapi"), "sip" : (requested_ip or "127.0.0.1")}
    
    for col in update_set:
        set_clauses.append(f"{col} = :{col}")
        params[col] = update_set[col]
    set_clauses.extend([
        #"STOCKOPNAME_ID = :sid",
        "UPDATEDATE = CURRENT_TIMESTAMP",
        "UPDATEBY = :sby",
        "UPDATETERMINAL = :sip",
        "SHELVING_DATE = CURRENT_TIMESTAMP",
        "SHELVING_BY = :sby"
    ])
    set_sql = ", ".join(set_clauses)

    cur.execute(f"""
        UPDATE collections
           SET {set_sql}
         WHERE NOMORBARCODE = :rfid_no
    """, params)
    log.info("RENDERED:\n%s", render_sql(f"""
        UPDATE collections
           SET {set_sql}
         WHERE NOMORBARCODE = :rfid_no
    """, params))
    res = conn.execute(sql, params)
    return cur.rowcount if cur.rowcount is not None else 0

def _split_listdata(listdata: str) -> list[str]:
    """
    Pecah LISTDATA (string) jadi list, delimiter koma.
    Buang spasi kosong & entry kosong.
    """
    if not listdata:
        return []
    return [x.strip() for x in (listdata or "").split(",") if x.strip()]

def _column_exists(conn, table: str, column: str) -> bool:
    cur = conn.cursor()
    cur.execute("""
        SELECT 1
          FROM user_tab_columns
         WHERE table_name = :t AND column_name = :c
    """, {"t": table.upper(), "c": column.upper()})
    return cur.fetchone() is not None

def run_sync_job(stockopname_id: str, payload: SyncPayload) -> SyncResult:
    started = time.time()
    log.info("Start sync | id=%s | by=%s | ip=%s",
             stockopname_id, payload.requested_by, payload.request_ip)

    job = _fetch_job(stockopname_id)
    listdata_str = (job.get("LISTDATA") or "")
    jenis = (job.get("JENIS") or "").upper()
    items = _split_listdata(listdata_str)
    update_set = _build_update_set(job)

    updated = 0
    not_found = 0
    errors: list[str] = []

    with get_oracle_connection() as conn:
        try:
            lokasi_name = _get_location_name(conn, job)  # "Ruang Baca 1", dll
            note = f"Koleksi dijajarkan pada lokasi = {lokasi_name}"
            # --- 1) proses update koleksi ---
            if jenis == "BARQR":
                try:
                    # filter & normalisasi list
                    items_clean = [x.strip() for x in items if x and x.strip()]

                    # ambil ID yang eksis untuk barcodes tsb
                    rows = _get_collections_by_barcodes(conn, items_clean)  # [{ID, NOMORBARCODE}, ...]
                    ids_exist = [r["ID"] for r in rows]
                    exist_barcodes = {r["NOMORBARCODE"] for r in rows}
                    not_found += max(0, len(items_clean) - len(exist_barcodes))

                    # jalankan update hanya untuk barcode yang eksis
                    if exist_barcodes:
                        count = _bulk_update_by_barcodes(
                            conn, stockopname_id, items, update_set,
                            payload.requested_by, payload.request_ip
                        )
                        updated += count
                        # pendekatan kasar; untuk presisi, preselect existing barcodes
                        not_found += max(0, len(items) - count)
                        # tulis history untuk setiap ID yang terlibat
                        _bulk_insert_history(
                            conn,
                            ids_exist,
                            note,
                            payload.requested_by,
                            payload.request_ip
                        )

                except Exception as e:
                    errors.append(f"BARQR_BATCH:{type(e).__name__}:{str(e)[:200]}")
            elif jenis == "RFID":
                for idx, sn in enumerate(items, start=1):
                    try:
                        count = _update_by_rfid(
                            conn, stockopname_id, sn, update_set,
                            payload.requested_by, payload.request_ip
                        )
                        if count > 0:
                            updated += count
                            rfid_no = None
                            cur = conn.cursor()
                            cur.execute("SELECT RFID_NO FROM RFID_COLLECTIONS WHERE SERIAL_NUMBER = :sn", {"sn": sn.strip()})
                            row = cur.fetchone()
                            if row and row[0]:
                                rfid_no = str(row[0]).strip()
                            if rfid_no:
                                cid = _get_collection_id_by_barcode(conn, rfid_no)
                                if cid:
                                    _insert_history(conn, cid, note, payload.requested_by, payload.request_ip)
                        else:
                            not_found += 1
                    except Exception as e:
                        errors.append(f"{idx}:{sn}:{type(e).__name__}:{str(e)[:200]}")
            else:
                raise HTTPException(status_code=422, detail=f"Jenis tidak didukung: {jenis}")

            # --- 2) update status job = SELESAI / PARTIAL ---
            status_value = "SELESAI" if not errors else "SELESAI_PARTIAL"

            set_parts = ["STATUS = :p_status"]
            params = {
                "p_status": status_value,
                "p_id": stockopname_id,
                "p_by": (payload.requested_by or "fastapi"),
            }

            # kalau kolom FINISHDATE & FINISHBY ada, ikut diisi
            if _column_exists(conn, "STOCKOPNAMEJOBS", "FINISHDATE"):
                set_parts.append("FINISHDATE = CURRENT_TIMESTAMP")
            if _column_exists(conn, "STOCKOPNAMEJOBS", "FINISHBY"):
                set_parts.append("FINISHBY = :p_by")

            sql_update_job = f"""
                UPDATE STOCKOPNAMEJOBS
                   SET {", ".join(set_parts)}
                 WHERE ID = :p_id
            """
            cur = conn.cursor()
            cur.execute(sql_update_job, params)

            # --- 3) commit semua perubahan ---
            conn.commit()

        except Exception as e:
            conn.rollback()
            log.exception("Sync failed, rolled back | id=%s | err=%s", stockopname_id, e)
            # tandai job gagal jika mau (opsional):
            with get_oracle_connection() as conn2:
                try:
                    cur2 = conn2.cursor()
                    cur2.execute("""
                        UPDATE STOCKOPNAMEJOBS
                           SET STATUS = 'GAGAL'
                         WHERE ID = :p_id
                    """, {"p_id": stockopname_id})
                    conn2.commit()
                except Exception:
                    conn2.rollback()
            raise  # biar FastAPI balikin error

    duration = round(time.time() - started, 3)
    log.info(
        "Done sync | id=%s | jenis=%s | items=%s | updated=%s | not_found=%s | dur=%.3fs",
        stockopname_id, jenis, len(items), updated, not_found, duration
    )

    return SyncResult(
        id=stockopname_id,
        status="success" if not errors else "partial",
        processed_at=time.time(),
        detail={
            "jenis": jenis,
            "total_items": len(items),
            "updated_rows": updated,
            "not_found": not_found,
            "updated_columns": sorted(
                list(update_set.keys()) +
                ["UPDATEDATE", "UPDATEBY", "UPDATETERMINAL", "SHELVING_DATE", "SHELVING_BY"]
            ),
            "errors": errors[:50],
            "duration_sec": duration,
            "requested_by": payload.requested_by,
            "request_ip": payload.request_ip,
        }
    )
def background_sync_job(stockopname_id: str, payload: SyncPayload) -> None:
    try:
        _ = run_sync_job(stockopname_id, payload)
    except Exception as e:
        log.exception("Background sync failed | id=%s | err=%s", stockopname_id, e)

import re
DEBUG_SQL = os.getenv("DEBUG_SQL", "0") == "1"
def render_sql(sql: str, params: dict) -> str:
    # sangat sederhana; cukup untuk debugging
    def q(v):
        if v is None: return "NULL"
        if isinstance(v, (int, float)): return str(v)
        s = str(v).replace("'", "''")
        return f"'{s}'"
    def repl(m):
        key = m.group(1)
        return q(params.get(key))
    return re.sub(r":([A-Za-z0-9_]+)", repl, sql)



# --- endpoints ---

@app.get("/health")
def health() -> Dict[str, str]:
    return {"status": "ok"}

@app.post("/sync/{id}", response_model=SyncResult, dependencies=[Depends(verify_bearer_token)])
def sync_now(
    request: Request,
    id: str = Path(..., description="Stock Opname ID"),
    q_async: int = Query(default=0, alias="async", ge=0, le=1, description="1=background, 0=sync"),
    body: SyncPayload = None
):
    """
    Sinkronisasi StockOpname.
    - Default synchronous: tunggu sampai selesai dan kembalikan result.
    - Tambahkan `?async=1` untuk mengeksekusi di background (return cepat).

    Laravel contoh:
    - Sinkron: POST {BASE}/sync/{id}
    - Async:   POST {BASE}/sync/{id}?async=1
    """
    payload = body or SyncPayload()
    # autopopulate IP kalau belum diisi
    if not payload.request_ip:
        payload.request_ip = request.client.host if request.client else None

    if q_async == 1:
        # enqueue background
        # Gunakan BackgroundTasks bila mau confirm ke caller langsung.
        # Di sini kita langsung jalanin thread background yang ringan.
        from fastapi import BackgroundTasks
        bg = BackgroundTasks()
        bg.add_task(background_sync_job, id, payload)
        # Return respons cepat, tanpa menunggu hasil
        # Note: untuk response model berbeda (enqueue), Anda bisa pisahkan endpoint kalau mau strict schema.
        # Agar sesuai response_model=SyncResult, kita kembalikan stub status "queued".
        return SyncResult(
            id=id,
            status="queued",
            processed_at=time.time(),
            detail={
                "message": "Job enqueued; running in background",
                "requested_by": payload.requested_by,
                "request_ip": payload.request_ip,
            }
        )

    # synchronous
    return run_sync_job(id, payload)

# (opsional) GET untuk cek cepat
@app.get("/sync/{id}", response_model=SyncResult, dependencies=[Depends(verify_bearer_token)])
def sync_now_get(
    request: Request,
    id: str = Path(..., description="Stock Opname ID"),
):
    """
    Versi GET untuk kemudahan testing/manual trigger.
    Tetap menjalankan proses sinkron (sinkron).
    """
    payload = SyncPayload(
        requested_by="fastapi",
        request_ip=request.client.host if request.client else None
    )
    return run_sync_job(id, payload)

# --- uvicorn runner hint ---
# Jalankan:
# uvicorn main:app --host 0.0.0.0 --port 8000 --reload
