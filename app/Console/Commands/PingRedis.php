<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class PingRedis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:ping-redis';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ping redis connection.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            Redis::set('ping_connection', 'working');
            $value = Redis::get('ping_connection');

            if ($value === 'working') {
                Log::info('Redis connection success');
            } else {
                Log::error('Redis connection failed');
            }
        } catch (\Throwable $th) {
            Log::info('Redis connection configuration', config('database.redis'));
            Log::info('Redis trace connection', $th->getTrace());
            Log::error("Redis connection error: " . $th->getMessage());
        }
    }
}
