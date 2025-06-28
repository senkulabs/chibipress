<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasUniqueSlug
{
    /**
     * Generate unique slug with created date
     */
    public function generateUniqueSlug($title, $id = null)
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;

        $counter = 1;

        while ($this->slugExists($slug, $id)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function slugExists($slug, $excludeId = null)
    {
        $query = static::where('slug', $slug);

        if ($excludeId) {
            $query->where('id', '!==', $excludeId);
        }

        return $query->exists();
    }
}
