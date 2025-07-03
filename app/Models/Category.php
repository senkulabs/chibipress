<?php

namespace App\Models;

use App\Traits\HasUniqueSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Laravel\Scout\Searchable;

class Category extends Model
{
    use HasUniqueSlug, Searchable;

    protected $guarded = [];

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (Category $category) {
            $category->slug = empty($category->slug) ? $category->generateUniqueSlug($category->name) : $category->slug;
            Log::info('slug', ['slug' => $category->slug]);
        });

        static::creating(function (Category $category) {
            $category->slug = empty($category->slug) ? $category->generateUniqueSlug($category->name) : $category->slug;
        });
    }

    public function posts()
    {
        return $this->belongsToMany(Post::class);
    }
}
