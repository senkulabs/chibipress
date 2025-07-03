<?php

namespace App\Models;

use App\Traits\HasUniqueSlug;
use Illuminate\Support\Facades\Auth;
use Laravel\Scout\Searchable;

class Page extends Post
{
    use HasUniqueSlug, Searchable;

    const TYPE = 'page';

    protected $table = 'posts';

    protected $attributes = [
        'type' => self::TYPE
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
            $query->where('type', self::TYPE);
        });

        static::creating(function (Page $page) {
            $page->slug = $page->generateUniqueSlug($page->title);
            $page->type = self::TYPE;
            if (empty($page->author_id)) {
                $page->author_id = Auth::user()->id;
            }
            $page->parent = 0;
        });
    }
}
