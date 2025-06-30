#!/bin/sh

set -eu

./server php-cli artisan migrate --force
./server php-server
