<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('migrate:fresh --seed --force')->daily();

Schedule::command('app:ping-database')->everyTenSeconds();
Schedule::command('app:ping-redis')->everyTenSeconds();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
