<?php

namespace App\Models;

use App\Traits\HasUniqueSlug;
use Illuminate\Support\Facades\Auth;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Attachment extends Post implements HasMedia
{
    use InteractsWithMedia, HasUniqueSlug;

    const TYPE = 'attachment';

    protected $table = 'posts';

    protected $attributes = [
        'type' => self::TYPE
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('type', function ($query) {
            $query->where('type', self::TYPE);
        });

        static::creating(function ($model) {
            $model->slug = $model->generateUniqueSlug($model->title);
            $model->type = self::TYPE;
            $model->author_id = Auth::user()->id;
            $model->status = 'inherit';
            $model->parent = 0;
        });
    }

    /**
     * Single file collection
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('file')->singleFile();
    }

    public function mediaFile()
    {
        return $this->getFirstMedia('file');
    }
}
