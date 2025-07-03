#!/bin/sh

set -eu

./server php-cli artisan migrate:fresh --force
./server php-cli artisan db:seed --force
./server php-server
