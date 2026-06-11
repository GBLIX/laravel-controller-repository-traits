#!/bin/sh
# Resolves dependencies for the requested matrix and runs the phpunit suite.
# Meant to run inside the docker compose services (test-l12 / test-l13) only.
#
# Env vars:
#   MATRIX               "default" uses composer.json/vendor; any other value uses
#                        isolated composer-<MATRIX>.json/.lock and vendor-<MATRIX>/
#   TESTBENCH_CONSTRAINT orchestra/testbench constraint for this run (^10.0 = L12, ^11.0 = L13)
#   PRETTUS_CONSTRAINT   optional, pins prettus/l5-repository to one side of the
#                        dual "^2.10 || ^4.0" constraint (e.g. ^2.10)
set -eu

: "${TESTBENCH_CONSTRAINT:=^11.0}"
: "${MATRIX:=default}"

if [ "$MATRIX" = "default" ]; then
    VENDOR_DIR=vendor
else
    # Isolated composer.json copy so this matrix gets its own lock and vendor dir
    # (composer derives the lock name from the COMPOSER env var).
    export COMPOSER="composer-${MATRIX}.json"
    export COMPOSER_VENDOR_DIR="vendor-${MATRIX}"
    VENDOR_DIR="vendor-${MATRIX}"
    cp composer.json "$COMPOSER"
fi

if [ -n "${PRETTUS_CONSTRAINT:-}" ]; then
    composer update --with "orchestra/testbench:${TESTBENCH_CONSTRAINT}" \
        --with "prettus/l5-repository:${PRETTUS_CONSTRAINT}" \
        --prefer-dist --no-interaction --no-progress
else
    composer update --with "orchestra/testbench:${TESTBENCH_CONSTRAINT}" \
        --prefer-dist --no-interaction --no-progress
fi

composer validate --no-check-publish

echo "--- Resolved versions (matrix: ${MATRIX}, testbench ${TESTBENCH_CONSTRAINT}, prettus ${PRETTUS_CONSTRAINT:-unpinned}) ---"
composer show laravel/framework orchestra/testbench prettus/l5-repository 2>/dev/null \
    | grep -E '^(name|versions)' || true

"./${VENDOR_DIR}/bin/phpunit" --bootstrap "${VENDOR_DIR}/autoload.php" --colors=always "$@"
