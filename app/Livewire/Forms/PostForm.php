<?php

namespace App\Livewire\Forms;

use App\Models\Category;
use App\Models\Media;
use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Validate;
use Livewire\Form;

class PostForm extends Form
{
    public ?Post $post;

    #[Validate('required|min:5')]
    public $title = '';

    #[Validate('required|min:5')]
    public $content = '';

    public $excerpt = '';

    #[Validate('nullable', 'image', 'extensions:jpg,jpeg,png')]
    public $featured_image;

    public $categories = [];

    #[Validate('required')]
    public $status = 'draft';

    public $existing_featured_image;

    public function setPost(Post $post)
    {
        $this->post = $post;
        $this->title = $post->title;
        $this->content = $post->content;
        $this->excerpt = $post->excerpt;
        $this->status = $post->status;
        $this->categories = $post->categories->pluck('slug')->toArray();
        if ($post->hasThumbnail()) {
            $this->existing_featured_image = $post->thumbnail->media_path;
        }
    }

    public function store()
    {
        $this->validate();

        $post = Post::create(
            $this->only(['title', 'content', 'excerpt', 'status'])
        );

        if (!is_null($this->featured_image)) {
            $featured_image_path = $this->featured_image->storePublicly(path: 'media', options: 'public');

            $media = Media::create([
                'title' => basename($featured_image_path),
                'status' => 'inherit', // belongs to the posts,
                'parent' => $post->id,
            ]);

            $media->setMediaPath($featured_image_path);
            $media->setMediaMimeType(mime_content_type(Storage::disk('public')->path($featured_image_path)));

            // Associate the post with the featured image
            $post->setThumbnail($media->id);
        }

        if (!empty($this->categories)) {
            $categoryIds = Category::whereIn('slug', $this->categories)
                ->pluck('id')
                ->toArray();

            // Sync categories to the post
            $post->categories()->sync($categoryIds);
        }

        $this->reset();

        $this->post = new Post();
    }

    public function update()
    {
        $this->validate();

        $this->post->update(
            $this->only(['title', 'content', 'excerpt', 'status'])
        );

        if (!is_null($this->featured_image)) {
            // Remove existing media that attached into post
            $previousMedia = Media::find($this->post->thumbnail->id);
            $previousMedia->parent = 0;
            $previousMedia->save();

            $featured_image_path = $this->featured_image->storePublicly(path: 'media', options: 'public');

            $media = Media::create([
                'title' => basename($featured_image_path),
                'status' => 'inherit', // belongs to the posts,
                'parent' => $this->post->id,
            ]);

            $media->setMediaPath($featured_image_path);
            $media->setMediaMimeType(mime_content_type(Storage::disk('public')->path($featured_image_path)));

            // Associate the post with the featured image
            $this->post->setThumbnail($media->id);
        }
    }
}
