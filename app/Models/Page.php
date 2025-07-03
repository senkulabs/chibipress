<?php

namespace App\Models;

use App\Traits\HasUniqueSlug;
use Illuminate\Support\Facades\Auth;
use Laravel\Scout\Searchable;

class Page extends Post
{
    use HasUniqueSlug, Searchable;

    const PAGE = 'page';

    protected $table = 'posts';

    protected $attributes = [
        'type' => self::PAGE
    ];

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
        ];
    }

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
