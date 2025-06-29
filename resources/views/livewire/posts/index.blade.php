<?php

use App\Models\Post;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {

    use WithPagination;

    #[Url(as: 'post_status')]
    public $status = '';

    #[Url(as: 'q')]
    public $search = '';

    #[Url(as: 'category_name')]
    public $categoryName = '';

    function with() : array {
        $posts = Post::with('author');

        switch ($this->status) {
            case 'publish':
                $posts = $posts->published();
                break;
            case 'draft':
                $posts = $posts->draft();
                break;
            case 'trash':
                $posts = $posts->trashed();
                break;
            default:
                $posts = $posts->notTrashed();
                break;
        }

        if ($this->categoryName) {
            $posts = Post::with('author')->whereHas('categories', function ($query) {
                $query->where('slug', '=', $this->categoryName);
            });
        }

        if ($this->search) {
            $posts->where('title', 'like', "%{$this->search}%");
        }

        return [
            'posts' => $posts->orderBy('updated_at', 'desc')->paginate(10),
            'totalPostsCount' => Post::notTrashed()->count(),
            'trashedPostsCount' => Post::trashed()->count(),
            'publishedPostsCount' => Post::published()->count(),
            'draftPostsCount' => Post::draft()->count(),
        ];
    }

    public function moveToTrash($id)
    {
        $post = Post::find($id);
        $post->status = 'trash';
        $post->save();
    }

    public function restoreFromTrash($id)
    {
        $post = Post::find($id);
        $post->status = 'draft';
        $post->save();
    }

    public function permanentlyDelete($id)
    {
        $post = Post::find($id);
        $post->delete();
    }
}; ?>

<div>
    <div class="title flex items-center gap-2 mb-4">
        <h1 class="inline-block text-3xl">{{ __('Posts') }}</h1>
        <flux:button href="{{ route('posts.create') }}">{{ __('Add Post') }}</flux:button>
    </div>
    <div class="control flex justify-between items-center mb-4">
        <div class="filter">
            <ul class="flex gap-2">
                <li>
                    <a href="{{ route('posts.index', ['post_status' => 'all']) }}"
                        class="{{ request()->fullUrlIs(route('posts.index', ['post_status' => 'all'])) || request()->fullUrlIs(route('posts.index')) ? 'font-bold' : 'text-blue-400' }}">All</a> <span>({{ $totalPostsCount }})</span> |
                </li>
                @if ($publishedPostsCount)
                <li>
                    <a href="{{ route('posts.index', ['post_status' => 'publish']) }}"
                        class="{{ request()->fullUrlIs(route('posts.index', ['post_status' => 'publish'])) ? 'font-bold' : 'text-blue-400' }}">Published</a> <span>({{ $publishedPostsCount }})</span> |
                </li>
                @endif
                @if ($draftPostsCount)
                <li>
                    <a href="{{ route('posts.index', ['post_status' => 'draft']) }}"
                    class="{{ request()->fullUrlIs(route('posts.index', ['post_status' => 'draft'])) ? 'font-bold' : 'text-blue-400' }}">{{ ($draftPostsCount > 1) ? 'Drafts' : 'Draft' }}</a> <span>({{ $draftPostsCount }})</span> |
                </li>
                @endif
                @if ($trashedPostsCount)
                <li>
                    <a href="{{ route('posts.index', ['post_status' => 'trash']) }}"
                    class="{{ request()->fullUrlIs(route('posts.index', ['post_status' => 'trash'])) ? 'font-bold' : 'text-blue-400' }}">Trash</a> <span>({{ $trashedPostsCount }})</span>
                </li>
                @endif
            </ul>
        </div>
        <div class="search">
            <flux:input.group>
                <flux:input wire:model.change="search" />
                <flux:button>Search Posts</flux:button>
            </flux:input.group>
        </div>
    </div>
    <div class="table-control">
        <div class="grid overflow-x-auto">
            <table class="w-full table-auto divide-y">
                <thead>
                    <tr>
                        <th class="py-3 px-3 text-left">Title</th>
                        <th class="py-3 px-3 text-left">Author</th>
                        <th class="py-3 px-3 text-left">Categories</th>
                        <th class="py-3 px-3 text-left">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($posts as $post)
                    <tr wire:key="{{ $post->id }}" class="divide-y">
                        <td class="py-3 px-3">
                            {{ $post->title }} {{ ($post->status == 'draft' ? '- Draft' : '') }}
                            @if($post->status == 'trash')
                            <div>
                                <a href="#" class="text-blue-400 hover:underline" wire:click="restoreFromTrash({{ $post->id }})">Recover</a> |
                                <a href="#" class="text-red-500 hover:underline" wire:click="permanentlyDelete({{ $post->id }})" wire:confirm="Are you sure to delete this post?">Delete permanently</a>
                            </div>
                            @else
                            <div>
                                <a href="{{ route('posts.edit', $post) }}" class="text-blue-400 hover:underline">Edit</a> |
                                <a href="#" wire:click="moveToTrash({{ $post->id }})" class="text-red-500 hover:underline">Trash</a>
                            </div>
                            @endif
                        </td>
                        <td class="py-3 px-3" style="width: 15%;">{{ $post->author->name }}</td>
                        <td class="py-3 px-3" style="width: 25%;">
                            {!! $post->categories->map(function ($category) {
                                return '<a href="'.route('posts.index', ['category_name' => $category->slug]).'" class="text-blue-400 hover:underline">'.$category->name.'</a>';
                            })->implode(', ') !!}
                        </td>
                        <td class="py-3 px-3" style="width: 14%;">
                            @php
                                $word = 'Last Modified';
                                if ($post->isPublished()) {
                                    $word = 'Published';
                                }
                            @endphp
                            {{ $word }} {{ Carbon\Carbon::parse($post->updated_at)->format('Y/m/d \a\t g:i a') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="table-pagination">
            {{ $posts->links() }}
        </div>
    </div>
</div>
