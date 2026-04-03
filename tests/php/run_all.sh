#!/usr/bin/env bash
# dev/tests/php/run_all.sh
# Führt alle PHP-Testskripte aus und gibt kombinierten Exit-Code zurück.

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
FAILED=0

run_test() {
    echo "--- $1 ---"
    php "$SCRIPT_DIR/$1" || FAILED=1
    echo ""
}

run_test test_password.php
run_test test_forcelogin.php
run_test test_meta_contracts.php

if [ $FAILED -ne 0 ]; then
    echo "❌ Einige Tests schlugen fehl."
    exit 1
else
    echo "✅ Alle PHP-Tests bestanden."
    exit 0
fi
