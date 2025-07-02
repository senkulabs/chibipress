<?php

namespace App\Livewire\Forms;

use App\Models\Attachment;
use App\Models\Category;
use App\Models\Post;
use DateTime;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PostForm extends Form
{
    public ?Post $post;

    #[Validate('required|min:5')]
    public $title = '';

    #[Validate]
    public $content = '';

    public $excerpt = '';

    public $featuredImage = null;

    public $categories = [];

    #[Validate('required')]
    public $status = 'draft';

    public $existingFeaturedImage;

    public function setPost(Post $post)
    {
        $this->post = $post;
        $this->title = $post->title;
        $this->content = $post->content;
        $this->excerpt = $post->excerpt;
        $this->status = $post->status;
        $this->categories = $post->categories->pluck('slug')->toArray();
        if ($post->hasThumbnail()) {
            $attachment = Attachment::find($post->getMeta('_thumbnail_id'));
            $mediaData = $attachment->mediaFile()->toArray();
            $transformedData = [
                'id' => $mediaData['id'],
                'name' => $mediaData['name'],
                'type' => $mediaData['mime_type'],
                'size' => $mediaData['size'],
                'url' => $attachment->mediaFile()->getUrl(),
                'uploaded' => (new DateTime($mediaData['created_at']))->format('c'),
                'dimensions' => null,
            ];
            $this->featuredImage = $transformedData;
        }
    }

    protected function rules()
    {
        return [
            'content' => [
                'required',
                'min:5',
                function ($attribute, $value, $fail) {
                    // Check if content is empty or just empty lists
                    $textContent = strip_tags($value);
                    $textContent = trim($textContent);

                    if (empty($textContent)) {
                        $fail('The content field is required and cannot be empty');
                    }

                    if (strlen($textContent) < 5) {
                        $fail('The minimum character of content is 5 characters.');
                    }
                }
            ]
        ];
    }

    public function store()
    {
        $this->validate();

        $post = Post::create(
            $this->only(['title', 'content', 'excerpt', 'status'])
        );

        if (!is_null($this->featuredImage)) {
            $media = Media::find($this->featuredImage['id']);
            $post->setThumbnail($media->model_id);
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

        if (!is_null($this->featuredImage)) {
            $media = Media::find($this->featuredImage['id']);
            $this->post->setThumbnail($media->model_id);
        }

        if (!empty($this->categories)) {
            $categoryIds = Category::whereIn('slug', $this->categories)
                ->pluck('id')
                ->toArray();

            // Sync categories to the post
            $this->post->categories()->sync($categoryIds);
        }
    }
}
