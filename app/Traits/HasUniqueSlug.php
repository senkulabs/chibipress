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

        // Start from 2 because the original start from 1
        // E.g
        // slug: abc => original, abc-2 => duplicate
        $counter = 2;

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
