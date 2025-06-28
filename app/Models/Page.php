<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;

class Page extends Post
{
    const PAGE = 'page';

    protected $table = 'posts';

    protected $attributes = [
        'type' => self::PAGE
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('type', function ($query) {
            $query->where('type', self::PAGE);
        });

        static::creating(function (Page $page) {
            $page->type = self::PAGE;
            $page->author = Auth::user()->id;
        });
    }
}
