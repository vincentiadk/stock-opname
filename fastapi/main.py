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
    sql = text(f"""
        SELECT {H_LOC_NAME_COL}
          FROM {H_LOCATIONS_TABLE}
         WHERE {H_LOC_ID_COL} = :id
    """)
    r = conn.execute(sql, {"id": loc_id}).fetchone()
    return (r[0] if r and r[0] else f"{loc_id}")

# ---- Helper: ambil ID collections dari daftar barcode ----
def _get_collections_by_barcodes(conn, barcodes: list[str]) -> list[dict]:
    if not barcodes:
        return []
    # Pecah jadi batch kecil kalau list panjang (opsional)
    sql = text("""
        SELECT ID, NOMORBARCODE
          FROM collections
         WHERE NOMORBARCODE IN :bcs
    """)
    # SQLAlchemy butuh tuple untuk IN (:bcs)
    result = conn.execute(sql, {"bcs": tuple(barcodes)}).mappings().all()
    return [dict(row) for row in result]

# ---- Helper: ambil ID collection dari satu barcode ----
def _get_collection_id_by_barcode(conn, barcode: str) -> int | None:
    sql = text("""
        SELECT ID
          FROM collections
         WHERE NOMORBARCODE = :bc
    """)
    r = conn.execute(sql, {"bc": barcode}).fetchone()
    return int(r[0]) if r and r[0] is not None else None

# ---- Helper: insert satu baris history ----
def _insert_history(conn, idref: int, note: str, actionby: str, terminal: str):
    sql = text(f"""
        INSERT INTO {HISTORY_TABLE}
            (TABLENAME, ACTION, IDREF, NOTE, ACTIONBY, ACTIONDATE, ACTIONTERMINAL)
        VALUES
            (:tablename, :action, :idref, :note, :actionby, CURRENT_TIMESTAMP, :terminal)
    """)
    conn.execute(sql, {
        "tablename": "collections",
        "action": "Edit",
        "idref": idref,
        "note": note,
        "actionby": actionby or "fastapi",
        "terminal": terminal or "-",
    })
# --- ambil job sebagai mapping (bukan tuple) ---
def _fetch_job(engine: Engine, stockopname_id: str) -> dict:
    """
    Ambil 1 row STOCKOPNAMEJOBS sebagai dict/mapping.
    Pastikan minimal ada: LISTDATA, JENIS. Kolom lain dipakai utk update_set.
    """
    sql = text("""
        SELECT *
          FROM STOCKOPNAMEJOBS
         WHERE STOCKOPNAMEID = :id
    """)
    with engine.connect() as conn:
        row = conn.execute(sql, {"id": stockopname_id}).mappings().fetchone()
    if not row:
        raise HTTPException(status_code=404, detail=f"STOCKOPNAMEJOBS id={stockopname_id} tidak ditemukan")
    # casting ke dict agar aman
    return dict(row)

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
    params_template = {"sid": stockopname_id, "sby": (requested_by or "fastapi"), "sip" : (requested_ip or "127.0.0.1")}
    for col in update_set:
        set_clauses.append(f"{col} = :{col}")
        params_template[col] = update_set[col]

    # kolom standar untuk jejak opname
    set_clauses.extend([
        "STOCKOPNAME_ID = :sid",
        "UPDATEDATE = CURRENT_TIMESTAMP",
        "UPDATEBY = :sby",
        "UPDATETERMINAL = :sip",
        "SHELVING_DATE = CURRENT_TIMESTAMP",
        "SHELVINGBY = :sby"
    ])
    set_sql = ", ".join(set_clauses)

    sql = text(f"""
        UPDATE collections
           SET {set_sql}
         WHERE NOMORBARCODE = :barcode
    """)

    # executemany
    batch_params = []
    for bc in barcodes:
        p = dict(params_template)
        p["barcode"] = bc
        batch_params.append(p)

    result = conn.execute(sql, batch_params)
    # di sebagian driver rowcount untuk executemany = total affected rows
    return result.rowcount or 0

def _update_by_rfid(conn, stockopname_id: str, serial_number: str, update_set: dict, requested_by: Optional[str], requested_ip: Optional[str]) -> int:
    """
    Cari RFID_NO via SERIAL_NUMBER, lalu update collections WHERE NOMORBARCODE = RFID_NO
    memakai update_set yang sama.
    """
    q_rfid = text("""
        SELECT RFID_NO
          FROM RFID_COLLECTIONS
         WHERE SERIAL_NUMBER = :sn
    """)
    r = conn.execute(q_rfid, {"sn": serial_number}).fetchone()
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
        "STOCKOPNAME_ID = :sid",
        "UPDATEDATE = CURRENT_TIMESTAMP",
        "UPDATEBY = :sby",
        "UPDATETERMINAL = :sip",
        "SHELVING_DATE = CURRENT_TIMESTAMP",
        "SHELVINGBY = :sby"
    ])
    set_sql = ", ".join(set_clauses)

    sql = text(f"""
        UPDATE collections
           SET {set_sql}
         WHERE NOMORBARCODE = :rfid_no
    """)
    res = conn.execute(sql, params)
    return res.rowcount or 0

def run_sync_job(stockopname_id: str, payload: SyncPayload) -> SyncResult:
    if ENGINE is None:
        raise HTTPException(status_code=500, detail="DATABASE_URL belum dikonfigurasi")

    started = time.time()
    log.info("Start sync | id=%s | by=%s | ip=%s", stockopname_id, payload.requested_by, payload.request_ip)

    job = _fetch_job(ENGINE, stockopname_id)
    listdata_str = (job.get("LISTDATA") or "")
    jenis = (job.get("JENIS") or "").upper()
    items = _split_listdata(listdata_str)
    update_set = _build_update_set(job)

    updated = 0
    not_found = 0
    errors: list[str] = []

    with ENGINE.begin() as conn:
        if jenis == "BARQR":
            # Batch BARQR sekali jalan (lebih cepat)
            try:
                count = _bulk_update_by_barcodes(conn, stockopname_id, items, update_set, payload.requested_by, payload.request_ip)
                updated += count
                not_found += max(0, len(items) - count)  # pendekatan kasar; jika perlu presisi, cek satu2
            except Exception as e:
                errors.append(f"BARQR_BATCH:{type(e).__name__}:{str(e)[:200]}")
        elif jenis == "RFID":
            # RFID: tetap per item (atau bisa di-batch mapping dulu kalau mau optimasi)
            for idx, sn in enumerate(items, start=1):
                try:
                    count = _update_by_rfid(conn, stockopname_id, sn, update_set, payload.requested_by, payload.request_ip)
                    if count > 0:
                        updated += count
                    else:
                        not_found += 1
                except Exception as e:
                    errors.append(f"{idx}:{sn}:{type(e).__name__}:{str(e)[:200]}")
        else:
            raise HTTPException(status_code=422, detail=f"Jenis tidak didukung: {jenis}")

    duration = round(time.time() - started, 3)
    log.info("Done sync | id=%s | jenis=%s | items=%s | updated=%s | not_found=%s | dur=%.3fs",
             stockopname_id, jenis, len(items), updated, not_found, duration)

    return SyncResult(
        id=stockopname_id,
        status="success" if not errors else "partial",
        processed_at=time.time(),
        detail={
            "jenis": jenis,
            "total_items": len(items),
            "updated_rows": updated,
            "not_found": not_found,
            "updated_columns": sorted(list(update_set.keys()) + ["STOCKOPNAME_ID","STOCKOPNAME_AT","STOCKOPNAME_BY"]),
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
