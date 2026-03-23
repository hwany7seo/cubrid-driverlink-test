#!/usr/bin/env python3
"""
Mechanical CUBRID PHP API -> ODBC-ish rewrites for ODBC driverlink tests.
Some APIs have no ODBC equivalent; tests may fail until fixed manually.
"""
from __future__ import annotations

import re
import sys
from pathlib import Path

# Default DSN template (host/port can be overridden via env in shell scripts)
DSN_EXPR = (
    '"Driver={CUBRID Driver};server=test-db-server;port=33000;uid=dba;pwd=;database=" . $db'
)


def convert_text(s: str) -> str:
    # Order matters: longer / more specific patterns first
    out = s

    # cubrid_connect_with_url variants (tests2)
    out = re.sub(
        r"cubrid_connect_with_url\(\s*\$connect_url\s*,\s*\$user\s*,\s*\$passwd\s*\)",
        f"odbc_connect({DSN_EXPR}, \"\", \"\")",
        out,
    )
    out = re.sub(
        r"cubrid_connect_with_url\(\s*\$connect_url\s*\)",
        f"odbc_connect({DSN_EXPR}, \"\", \"\")",
        out,
    )
    # String literal URL (keep query params stripped — ODBC DSN is simplified)
    out = re.sub(
        r'cubrid_connect_with_url\(\s*"CUBRID:\$host:\$port:\$db:[^"]*"\s*\)',
        f"odbc_connect({DSN_EXPR}, \"\", \"\")",
        out,
    )

    # Standard 5-arg connect (allow extra spaces)
    out = re.sub(
        r"cubrid_connect\(\s*\$host\s*,\s*\$port\s*,\s*\$db\s*,\s*\$user\s*,\s*\$passwd\s*\)",
        f"odbc_connect({DSN_EXPR}, \"\", \"\")",
        out,
    )
    out = re.sub(
        r"cubrid_connect\(\s*\$host\s*,\s*\$port\s*,\s*\$db\s*,\s*\$user\s*,\s*\$passwd\s*,\s*(?:TRUE|FALSE)\s*\)",
        f"odbc_connect({DSN_EXPR}, \"\", \"\")",
        out,
    )

    # Common request / statement handles
    out = out.replace("cubrid_close_request(", "odbc_free_result(")
    out = out.replace("cubrid_close_prepare(", "odbc_free_result(")
    out = out.replace("cubrid_free_result(", "odbc_free_result(")

    out = out.replace("cubrid_fetch_assoc(", "odbc_fetch_array(")
    out = out.replace("cubrid_fetch_array(", "odbc_fetch_array(")
    out = out.replace("cubrid_fetch_row(", "odbc_fetch_row(")

    out = out.replace("cubrid_disconnect(", "odbc_close(")
    out = out.replace("cubrid_close(", "odbc_close(")

    out = out.replace("cubrid_prepare(", "odbc_prepare(")

    # Two-arg execute on connection -> odbc_exec
    out = re.sub(r"\bcubrid_execute\(\s*\$conn\s*,", r"odbc_exec($conn,", out)
    out = re.sub(r"\bcubrid_execute\(\s*\$conn_handle\s*,", r"odbc_exec($conn_handle,", out)

    # Single-arg execute on statement (prepared)
    out = re.sub(r"\bcubrid_execute\(\s*\$req\s*\)", r"odbc_execute($req)", out)
    out = re.sub(r"\bcubrid_execute\(\s*\$cubrid_req\s*\)", r"odbc_execute($cubrid_req)", out)
    out = re.sub(r"\bcubrid_execute\(\s*\$stmt\s*\)", r"odbc_execute($stmt)", out)

    out = out.replace("cubrid_commit(", "odbc_commit(")
    out = out.replace("cubrid_rollback(", "odbc_rollback(")

    out = re.sub(r"\bcubrid_errno\(\s*\$conn\s*\)", r"odbc_error($conn)", out)
    out = re.sub(r"\bcubrid_errno\(\s*\)", r"odbc_error()", out)
    out = re.sub(r"\bcubrid_error\(\s*\$conn\s*\)", r"odbc_errormsg($conn)", out)
    out = re.sub(r"\bcubrid_error\(\s*\)", r"odbc_errormsg()", out)
    out = out.replace("cubrid_error_msg(", "odbc_errormsg(")

    out = out.replace("cubrid_num_rows(", "odbc_num_rows(")
    out = out.replace("cubrid_num_cols(", "odbc_num_fields(")
    out = out.replace("cubrid_num_fields(", "odbc_num_fields(")

    return out


def main() -> int:
    roots = [Path(p) for p in sys.argv[1:]]
    if not roots:
        print("usage: convert_cubrid_phpt_to_odbc.py DIR [DIR...]", file=sys.stderr)
        return 1
    exts = {".phpt", ".inc", ".php"}
    for root in roots:
        for path in root.rglob("*"):
            if not path.is_file():
                continue
            if path.suffix.lower() not in exts:
                continue
            data = path.read_text(encoding="utf-8", errors="surrogateescape")
            new = convert_text(data)
            if new != data:
                path.write_text(new, encoding="utf-8", errors="surrogateescape")
                print(path)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
