<?php

namespace App\Models;

use App\Traits\HasUniqueSlug;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Scout\Searchable;

class Post extends Model
{
    use HasUniqueSlug, Searchable;

    const TYPE = 'post';
    const DRAFT = 'draft';
    const PUBLISHED = 'publish';
    const TRASH = 'trash';
    protected $guarded = [];
    protected $casts = [
        'meta_value' => 'integer'
    ];

    protected function casts(): array
    {
        return [
            'meta_value' => $this->meta_key === '_thumbnail_id' ? 'integer' : 'string',
        ];
    }

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

    protected static function booted() : void
    {
        static::addGlobalScope('type', function ($query) {
            $query->where('type', self::TYPE);
        });

        static::creating(function (Post $post) {
            $post->slug = $post->generateUniqueSlug($post->title);
            $post->type = self::TYPE;
            if (empty($post->author_id)) {
                $post->author_id = Auth::user()->id;
            }
            $post->parent = 0;
        });
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id', 'id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * Get all meta data for the post model
     */
    public function metas(): HasMany
    {
        return $this->hasMany(PostMeta::class, 'post_id');
    }

    /**
     * Get the thumbnail for regular post
     */
    public function thumbnail()
    {
        // $this->metas();
        // $driver = DB::connection()->getDriverName();

        // return $this->hasOneThrough(
        //     Attachment::class,
        //     PostMeta::class,
        //     'post_id', // Foreign key on post_metas table
        //     'id', // Foreign key on posts table
        //     'id', // Local key on posts table
        //     'meta_value' // Local key on post_metas table
        // )
        // ->where('post_metas.meta_key', '_thumbnail_id');
    }

    /**
     * Get thumbnail meta record
     */
    public function thumbnailMeta(): HasOne
    {
        return $this->hasOne(PostMeta::class)->where('meta_key', '_thumbnail_id');
    }

    /**
     * Get thumbnail ID as attribute
     */
    public function getThumbnailIdAttribute(): ?int
    {
        return $this->thumbnailMeta?->meta_value;
    }

    /**
     * Check if this post has a thumbnail
     */
    public function hasThumbnail(): bool
    {
        return $this->thumbnailMeta()->exists();
    }

    /**
     * Set thumbnail for the regular post
     */
    public function setThumbnail(string $mediaId): PostMeta
    {
        return $this->metas()->updateOrCreate(
            ['meta_key' => '_thumbnail_id'],
            ['meta_value' => $mediaId]
        );
    }

    /**
     * Remove thumbnail from this post
     */
    public function removeThumbnail(): bool
    {
        return $this->metas()
            ->where('meta_key', '_thumbnail_id')
            ->delete() > 0;
    }

    /**
     * Get meta value by key
     */
    public function getMeta(string $key): ?string
    {
        return $this->metas()->where('meta_key', $key)->value('meta_value');
    }

    /**
     * Set meta value
     */
    public function setMeta(string $key, string $value): PostMeta
    {
        return $this->metas()->updateOrCreate(
            ['meta_key' => $key],
            ['meta_value' => $value]
        );
    }

    public function isPublished()
    {
        return $this->status === self::PUBLISHED;
    }

    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('status', '=', self::PUBLISHED);
    }

    #[Scope]
    protected function draft(Builder $query): void
    {
        $query->where('status', '=', self::DRAFT);
    }

    #[Scope]
    protected function trashed(Builder $query): void
    {
        $query->where('status', '=', self::TRASH);
    }

    #[Scope]
    protected function notTrashed(Builder $query): void
    {
        $query->whereIn('status', array(self::DRAFT, self::PUBLISHED));
    }
}
