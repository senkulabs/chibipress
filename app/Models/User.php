<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
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
            'name' => $this->name,
            'email' => $this->email,
        ];
    }

    protected static function booted() : void
    {
        // Let's say by default the email is verified.
        static::creating(function (User $user) {
            $user->email_verified_at = now()->format('Y-m-d H:i:s');
        });
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function posts()
    {
        return $this->hasMany(Post::class, 'author_id', 'id');
    }

    /**
     * Delete user and transfer all posts to another user
     */
    public function deleteWithTransfer(User $transferToUser): bool
    {
        return DB::transaction(function () use ($transferToUser) {
            // Transfer all posts
            $this->posts()->withoutGlobalScope('type')
                ->update(['author_id' => $transferToUser->id]);

            // Delete the user
            return $this->delete();
        });
    }

    /**
     * Delete user along with all posts and related data
     */
    public function deleteWithPosts(): bool
    {
        return DB::transaction(function () {
            $postIds = $this->posts()->withoutGlobalScope('type')->pluck('id');

            if ($postIds->isNotEmpty()) {
                // Delete post meta
                PostMeta::whereIn('post_id', $postIds)->delete();

                // Handle attachment and media
                Attachment::whereIn('id', $postIds)
                    ->get()
                    ->each(function ($attachment) {
                        $attachment->clearMediaCollection('file');
                    });

                // Delete all posts
                $this->posts()->withoutGlobalScope('type')->delete();
            }

            // Delete the user
            return $this->delete();
        });
    }
}
