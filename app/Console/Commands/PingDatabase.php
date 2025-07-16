<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PingDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:ping-database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ping database connection.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            DB::getPdo();
            Log::info("Connected successfully to database: " . DB::getDatabaseName());
        } catch (\Throwable $th) {
            $default = config('database.default');
            Log::info('Database connection', config("database.connections.{$default}"));
            Log::info('Database trace connection', $th->getTrace());
            Log::error("Connection failed: " . $th->getMessage());
        }
    }
}
