<?php

use App\Models\Page;
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
        $pages = Page::with('author');

        switch ($this->status) {
            case 'publish':
                $pages = $pages->published();
                break;
            case 'draft':
                $pages = $pages->draft();
                break;
            case 'trash':
                $pages = $pages->trashed();
                break;
            default:
                $pages = $pages->notTrashed();
                break;
        }

        if ($this->categoryName) {
            $pages = Page::with('author')->whereHas('categories', function ($query) {
                $query->where('slug', '=', $this->categoryName);
            });
        }

        if ($this->search) {
            $pages->where('title', 'like', "%{$this->search}%");
        }

        return [
            'pages' => $pages->orderBy('updated_at', 'desc')->paginate(10),
            'totalCount' => Page::notTrashed()->count(),
            'trashedCount' => Page::trashed()->count(),
            'publishedCount' => Page::published()->count(),
            'draftCount' => Page::draft()->count(),
        ];
    }

    public function moveToTrash($id)
    {
        $page = Page::find($id);
        $page->status = 'trash';
        $page->save();
    }

    public function restoreFromTrash($id)
    {
        $page = Page::find($id);
        $page->status = 'draft';
        $page->save();
    }

    public function permanentlyDelete($id)
    {
        $page = Page::find($id);
        $page->delete();
    }
}; ?>

<div>
    <div class="title flex items-center gap-2 mb-4">
        <h1 class="inline-block text-3xl">{{ __('Pages') }}</h1>
        <flux:button href="{{ route('pages.create') }}">{{ __('Add Page') }}</flux:button>
    </div>
    <div class="control flex justify-between items-center mb-4">
        <div class="filter">
            <ul class="flex gap-2">
                <li>
                    <a href="{{ route('pages.index', ['post_status' => 'all']) }}"
                        class="{{ request()->fullUrlIs(route('pages.index', ['post_status' => 'all'])) || request()->fullUrlIs(route('pages.index')) ? 'font-bold' : 'text-blue-400' }}">All</a> <span>({{ $totalCount }})</span> |
                </li>
                @if ($publishedCount)
                <li>
                    <a href="{{ route('pages.index', ['post_status' => 'publish']) }}"
                        class="{{ request()->fullUrlIs(route('pages.index', ['post_status' => 'publish'])) ? 'font-bold' : 'text-blue-400' }}">Published</a> <span>({{ $publishedCount }})</span> |
                </li>
                @endif
                @if ($draftCount)
                <li>
                    <a href="{{ route('pages.index', ['post_status' => 'draft']) }}"
                    class="{{ request()->fullUrlIs(route('pages.index', ['post_status' => 'draft'])) ? 'font-bold' : 'text-blue-400' }}">{{ ($draftCount > 1) ? 'Drafts' : 'Draft' }}</a> <span>({{ $draftCount }})</span> |
                </li>
                @endif
                @if ($trashedCount)
                <li>
                    <a href="{{ route('pages.index', ['post_status' => 'trash']) }}"
                    class="{{ request()->fullUrlIs(route('pages.index', ['post_status' => 'trash'])) ? 'font-bold' : 'text-blue-400' }}">Trash</a> <span>({{ $trashedCount }})</span>
                </li>
                @endif
            </ul>
        </div>
        <div class="search">
            <flux:input.group>
                <flux:input wire:model.change="search" />
                <flux:button>Search Pages</flux:button>
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
                        <th class="py-3 px-3 text-left">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pages as $page)
                    <tr wire:key="{{ $page->id }}" class="divide-y">
                        <td class="py-3 px-3">
                            {{ $page->title }} {{ ($page->status == 'draft' ? '- Draft' : '') }}
                            @if($page->status == 'trash')
                            <div>
                                <a href="#" class="text-blue-400 hover:underline" wire:click="restoreFromTrash({{ $page->id }})">Recover</a> |
                                <a href="#" class="text-red-500 hover:underline" wire:click="permanentlyDelete({{ $page->id }})" wire:confirm="Are you sure to delete this page?">Delete permanently</a>
                            </div>
                            @else
                            <div>
                                <a href="{{ route('pages.edit', $page) }}" class="text-blue-400 hover:underline">Edit</a> |
                                <a href="#" wire:click="moveToTrash({{ $page->id }})" class="text-red-500 hover:underline">Trash</a>
                            </div>
                            @endif
                        </td>
                        <td class="py-3 px-3" style="width: 20%;">{{ $page->author->name }}</td>
                        <td class="py-3 px-3" style="width: 18%;">
                            @php
                                $word = $page->isPublished() ? 'Published' : 'Last Modified';
                            @endphp
                            {{ $word }} {{ Carbon\Carbon::parse($page->updated_at)->format('Y/m/d \a\t g:i a') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="table-pagination">
            {{ $pages->links() }}
        </div>
    </div>
</div>
