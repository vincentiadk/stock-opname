import oracledb
import os
import logging
from dotenv import load_dotenv

log = logging.getLogger("oracle.conn")

# 1) Load .env di sini, sebelum os.getenv dipanggil
load_dotenv()

ORACLE_DSN  = os.getenv("ORACLE_DSN", "(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=127.0.0.1)(PORT=1521))(CONNECT_DATA=(SERVER=DEDICATED)(SID=ORCL)))")
ORACLE_USER = os.getenv("ORACLE_USER", "inlis")
ORACLE_PASS = os.getenv("ORACLE_PASS", "inlis2016xs")
ORACLE_LIB  = os.getenv("ORACLE_LIB", r"E:\oracle\instantclient_23_8")

log = logging.getLogger("oracle.conn")

try:
    oracledb.init_oracle_client(lib_dir=ORACLE_LIB)  # aktifkan thick mode
    log.info("Oracle Client initialized.")
except Exception as e:
    log.warning("Oracle client init skipped/failed: %s", e)

def get_oracle_connection():
    try:
        conn = oracledb.connect(
            user=ORACLE_USER,
            password=ORACLE_PASS,
            dsn=ORACLE_DSN
            # encoding="UTF-8",   <-- buang ini
            # nencoding="UTF-8"   <-- buang ini
        )
        log.info("Berhasil konek ke Oracle.")
        return conn
    except Exception as e:
        log.error("Gagal konek Oracle: %s", e)
        raise