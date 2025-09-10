import random
# worker.py
import os, time, logging
from dotenv import load_dotenv
from db import get_oracle_connection
from main import run_sync_job, SyncPayload

load_dotenv()
logging.basicConfig(level=logging.INFO, format="%(asctime)s | %(levelname)s | %(name)s | %(message)s")
log = logging.getLogger("stockopname.worker")

CLAIM_BATCH = int(os.getenv("WORKER_CLAIM_BATCH", "10"))
SLEEP_SEC   = float(os.getenv("WORKER_SLEEP_SEC", "3"))
WORKER_NAME = os.getenv("WORKER_NAME", "fastapi-worker")

def claim_jobs(limit: int):
    ids = []
    with get_oracle_connection() as conn:
        cur = conn.cursor()
        # lock baris agar multi-worker aman
        cur.execute("""
            SELECT ID FROM STOCKOPNAMEJOBS
             WHERE STATUS = 'PENDING'
             FOR UPDATE SKIP LOCKED
        """)
        rows = cur.fetchmany(limit)
        if not rows:
            conn.rollback()
            return []
        ids = [int(r[0]) for r in rows if r and r[0] is not None]
        if ids:
            ph = ",".join([f":id{i}" for i in range(len(ids))])
            params = {f"id{i}": i for i in ids}
            params['by'] = WORKER_NAME
            cur.execute(f"""
                UPDATE STOCKOPNAMEJOBS
                   SET STATUS='PROCESSING',
                       START_AT=SYSTIMESTAMP,
                       START_BY=:by,
                       ERROR_MSG=NULL
                 WHERE ID IN ({ph})
            """, params)
        conn.commit()
    return ids

def finalize_job(job_id: int, status: str, err: str|None):
    with get_oracle_connection() as conn:
        cur = conn.cursor()
        cur.execute("""
            UPDATE STOCKOPNAMEJOBS
               SET STATUS=:st,
                   FINISHDATE=SYSTIMESTAMP,
                   FINISHBY=:by,
                   ERROR_MSG=:err
             WHERE ID = :id
        """, {"st": status, "by": WORKER_NAME, "err": err, "id": job_id})
        conn.commit()
def main_loop():
    log.info("Worker started | batch=%s", CLAIM_BATCH)
    sleep_min = float(os.getenv("WORKER_SLEEP_MIN", "0.5"))
    sleep_max = float(os.getenv("WORKER_SLEEP_MAX", "30"))
    sleep_cur = sleep_min

    while True:
        try:
            ids = claim_jobs(CLAIM_BATCH)
            if not ids:
                # no jobs → backoff + jitter
                jitter = random.uniform(0, sleep_cur * 0.1)
                time.sleep(sleep_cur + jitter)
                # naikkan interval sampai batas max
                sleep_cur = min(sleep_cur * 2, sleep_max)
                continue

            # ada jobs → proses dan reset interval
            sleep_cur = sleep_min
            for job_id in ids:
                try:
                    res = run_sync_job(str(job_id), SyncPayload(
                        requested_by=WORKER_NAME, request_ip="127.0.0.1"
                    ))
                    status = "SELESAI" if res.status == "success" else "SELESAI_PARTIAL"
                    err = None if res.status == "success" else str(res.detail.get("errors"))[:1000]
                    finalize_job(job_id, status, err)
                except Exception as e:
                    finalize_job(job_id, "GAGAL", str(e)[:1000])
                    log.exception("Job %s failed", job_id)

        except Exception:
            log.exception("Worker loop error")
            time.sleep(3)
            # optional: jangan reset sleep_cur di sini
