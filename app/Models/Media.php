<?php

namespace App\Models;

use App\Traits\HasUniqueSlug;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;

class Media extends Post
{
    use HasUniqueSlug;

    const MEDIA = 'media';

    protected $table = 'posts';

    protected $attributes = [
        'type' => self::MEDIA
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('type', function ($query) {
            $query->where('type', self::MEDIA);
        });

        static::creating(function (Media $media) {
            $media->slug = $media->generateUniqueSlug($media->title);
            $media->type = self::MEDIA;
            $media->author_id = Auth::user()->id;
        });
    }

    public function metas(): HasMany
    {
        return $this->hasMany(PostMeta::class, 'post_id');
    }

    /**
     * Get media path meta
     */
    public function mediaPathMeta(): HasOne
    {
        return $this->hasOne(PostMeta::class, 'post_id')->where('meta_key', '_media_path');
    }

    /**
     * Get media mime type
     */
    public function mediaMimeTypeMeta(): HasOne
    {
        return $this->hasOne(PostMeta::class, 'post_id')->where('meta_key', '_media_mime_type');
    }

    /**
     * Get media path as attribute
     */
    public function getMediaPathAttribute(): ?string
    {
        return $this->mediaPathMeta?->meta_value;
    }

    /**
     * Get media mime type as attribute
     */
    public function getMediaMimeTypeAttribute(): ?string
    {
        return $this->mediaMimeTypeMeta?->meta_value;
    }

    /**
     * Set media path
     */
    public function setMediaPath(string $path): PostMeta
    {
        return $this->metas()->updateOrCreate(
            ['meta_key' => '_media_path'],
            ['meta_value' => $path]
        );
    }

    /**
     * Set media mime type
     */
    public function setMediaMimeType(string $mimeType): PostMeta
    {
        return $this->metas()->updateOrCreate(
            ['meta_key' => '_media_mime_type'],
            ['meta_value' => $mimeType]
        );
    }
}
