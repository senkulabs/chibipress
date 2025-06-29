<?php

namespace App\Models;

use App\Traits\HasUniqueSlug;
use Illuminate\Support\Facades\Auth;

class Page extends Post
{
    use HasUniqueSlug;

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
            $page->slug = $page->generateUniqueSlug($page->title);
            $page->type = self::PAGE;
            $page->author_id = Auth::user()->id;
            $page->parent = 0;
        });
    }
}
