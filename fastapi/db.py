import os
import oracledb
import logging

# --- Env & Konstanta ---
ORACLE_DSN  = os.getenv("ORACLE_DSN", "(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=127.0.0.1)(PORT=1521))(CONNECT_DATA=(SERVER=DEDICATED)(SID=ORCL)))")
ORACLE_USER = os.getenv("ORACLE_USER", "inlis")
ORACLE_PASS = os.getenv("ORACLE_PASS", "inlis2016xs")
ORACLE_LIB  = os.getenv("ORACLE_LIB", r"E:\oracle\instantclient_23_8")  # sesuaikan path instantclient

log = logging.getLogger("oracle.conn")

# --- Inisialisasi sekali di awal ---
try:
    oracledb.init_oracle_client(lib_dir=ORACLE_LIB)
    log.info("Oracle Client initialized.")
except Exception as e:
    log.warning("Oracle client init skipped/failed: %s", e)

# --- Fungsi koneksi ---
def get_oracle_connection():
    """
    Membuat koneksi baru ke Oracle DB.
    Caller bertanggung jawab untuk menutup connection (conn.close()).
    """
    try:
        conn = oracledb.connect(
            user=ORACLE_USER,
            password=ORACLE_PASS,
            dsn=ORACLE_DSN,
            encoding="UTF-8",
            nencoding="UTF-8"
        )
        log.info("Berhasil konek ke Oracle.")
        return conn
    except Exception as e:
        log.error("Gagal konek Oracle: %s", e)
        raise