<?php

use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
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
});

require __DIR__.'/auth.php';
