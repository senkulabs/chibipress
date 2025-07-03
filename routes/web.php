<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

Route::middleware(['auth'])->group(function () {
    Volt::route('posts', 'posts.index')->name('posts.index')->middleware('permission:manage-posts');
    Volt::route('posts/create', 'posts.create')->name('posts.create')->middleware('permission:manage-posts');
    Volt::route('posts/{post}/edit', 'posts.edit')->name('posts.edit')->middleware('permission:manage-posts');

    Volt::route('categories', 'categories.index')->name('categories.index')->middleware('permission:manage-categories');
    Volt::route('categories/{category}/edit', 'categories.edit')->name('categories.edit')->middleware('permission:manage-categories');

    Volt::route('pages', 'pages.index')->name('pages.index')->middleware('permission:manage-pages');
    Volt::route('pages/create', 'pages.create')->name('pages.create')->middleware('permission:manage-pages');
    Volt::route('pages/{page}/edit', 'pages.edit')->name('pages.edit')->middleware('permission:manage-pages');

    Volt::route('media', 'media.index')->name('media.index')->middleware('permission:manage-media');

    Volt::route('users', 'users.index')->name('users.index')->middleware('permission:manage-users');
    Volt::route('users/create', 'users.create')->name('users.create')->middleware('permission:manage-users');
    Volt::route('users/{user}/edit', 'users.edit')->name('users.edit')->middleware('permission:manage-users');
    Volt::route('users/{user}/delete', 'users.delete')->name('users.delete')->middleware('permission:manage-users');
});

Route::get('/health/redis', function () {
    try {
        Redis::set('test_connection', 'working');
        $value = Redis::get('test_connection');

        if ($value == 'working') {
            Log::info('Redis connection successful!');
            return "Redis connection successful";
        } else {
            Log::error("Redis connection failed!");
            return "Redis connection failed";
        }
    } catch (\Throwable $th) {
        Log::info('Redis connection configuration', config('database.redis'));
        Log::error("Redis connection error: " . $th->getMessage());
        return "Redis connection error: ". $th->getMessage();
    }
});

Route::get('/health/pgsql', function () {
    try {
        DB::connection('pgsql')->getPdo();
        Log::info("Connected successfully to database: " . DB::connection('pgsql')->getDatabaseName());
        return "Connected successfully to database: " . DB::connection('pgsql')->getDatabaseName();
    } catch (\Throwable $th) {
        Log::info('Database connection configuration', config('database.connections.pgsql'));
        Log::error('Connection failed: '. $th->getMessage());
        return "Connection failed: " . $th->getMessage();
    }
});

Route::get('/health/s3', function () {
    try {
        Storage::disk('s3')->put('file.txt', 'Hello supabase storage. Date: '.now()->format('Y-m-d H:i:s'));
    } catch (\Throwable $th) {
        Log::error('warning', ['error' => $th->getMessage(), 'code' => $th->getCode()]);
        throw $th;
    }
});

require __DIR__.'/auth.php';
