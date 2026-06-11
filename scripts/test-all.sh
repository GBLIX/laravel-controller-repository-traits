#!/bin/sh
# Runs every test matrix/combination inside docker. Single entry point for
# local runs and CI — nothing executes on the host besides docker itself.
set -eu

cd "$(dirname "$0")/.."

docker compose build test-l13 test-l12 test-l12-prettus2

echo "==> Matrix 1/3: PHP 8.4 / Laravel 13 / prettus 4.x"
docker compose run --rm test-l13

echo "==> Matrix 2/3: PHP 8.3 / Laravel 12 / prettus 4.x"
docker compose run --rm test-l12

echo "==> Matrix 3/3: PHP 8.3 / Laravel 12 / prettus 2.x"
docker compose run --rm test-l12-prettus2

echo "==> All matrices passed"
