<?php

use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
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
    Volt::route('posts', 'posts.index')->name('posts.index');
    Volt::route('posts/create', 'posts.create')->name('posts.create');
    Volt::route('posts/{post}/edit', 'posts.edit')->name('posts.edit');

    Volt::route('categories', 'categories.index')->name('categories.index');
    Volt::route('categories/{category}/edit', 'categories.edit')->name('categories.edit');

    Volt::route('pages', 'pages.index')->name('pages.index');
    Volt::route('pages/create', 'pages.create')->name('pages.create');
    Volt::route('pages/{page}/edit', 'pages.edit')->name('pages.edit');

    Volt::route('media', 'media.index')->name('media.index');

    Volt::route('users', 'users.index')->name('users.index');
    Volt::route('users/create', 'users.create')->name('users.create');
    Volt::route('users/{user}/edit', 'users.edit')->name('users.edit');
    Volt::route('users/{user}/delete', 'users.delete')->name('users.delete');
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

Route::get('/media-library', function () {
    return view('media-library');
});

require __DIR__.'/auth.php';
