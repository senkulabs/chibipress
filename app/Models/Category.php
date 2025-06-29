<?php

namespace App\Models;

use App\Traits\HasUniqueSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Category extends Model
{
    use HasUniqueSlug;

    protected $guarded = [];

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
