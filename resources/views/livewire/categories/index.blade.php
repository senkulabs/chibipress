<?php

use App\Livewire\Forms\CategoryForm;
use App\Models\Category;
use App\Models\Post;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {

    use WithPagination;

    public CategoryForm $form;

    #[Url(as: 'q')]
    public $search = '';

    public function mount(?Category $category)
    {
        $this->form->setCategory($category);
    }

    function with() : array
    {
        $categories = Category::query();

        if (!empty($this->search)) {
            $categories->where('name', 'like', "%{$this->search}%");
        }

        return [
            'categories' => $categories->paginate(10),
        ];
    }

    public function submit()
    {
        $this->form->store();
    }

    public function delete($id)
    {
        $category = Category::find($id);
        $category->delete();
    }
}; ?>

<div>
    <div class="title flex items-center gap-2 mb-4">
        <h1 class="inline-block text-3xl">{{ __('Categories') }}</h1>
    </div>
    <div class="control flex justify-end items-center mb-4">
        <div class="search">
            <flux:input.group>
                <flux:input wire:model.change="search" />
                <flux:button>Search Categories</flux:button>
            </flux:input.group>
        </div>
    </div>
    <div class="category-container grid grid-cols-3 gap-4">
        <div class="form-container">
            <h2 class="inline-block text-xl mb-4">{{ __('Add Category') }}</h2>
            <form wire:submit="submit">
                <flux:field class="mb-4">
                    <flux:label>Name</flux:label>
                    <flux:input wire:model="form.name"/>
                    <flux:description>The name is how it appears on your site.</flux:description>
                    @error('form.name')
                        <flux:error name="form.name" message="{{ $message }}" />
                    @enderror
                </flux:field>
                <flux:field class="mb-4">
                    <flux:label>Slug</flux:label>
                    <flux:input wire:model="form.slug"/>
                    <flux:description>The “slug” is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.</flux:description>
                    @error('form.slug')
                        <flux:error name="form.slug" message="{{ $message }}" />
                    @enderror
                </flux:field>
                <flux:button type="submit" variant="primary" class="cursor-pointer">Add Category</flux:button>
            </form>
        </div>
        <div class="table-container col-span-2">
            <div class="grid overflow-x-auto">
                <table class="w-full table-auto divide-y">
                    <thead>
                        <tr>
                            <th class="py-3 px-3 text-left">Name</th>
                            <th class="py-3 px-3 text-left">Slug</th>
                            <th class="py-3 px-3 text-left">Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categories as $category)
                        <tr wire:key="{{ $category->id }}" class="divide-y">
                            <td class="py-3 px-3">
                                {{ $category->name }}
                                <div>
                                    <a href="{{ route('categories.edit', $category) }}" class="text-blue-400 hover:underline">Edit</a> |
                                    <a href="#" wire:click="delete({{ $category->id }})" wire:confirm="Are you sure to delete this category? This action cannot be undo." class="text-red-500 hover:underline">Delete</a>
                                </div>
                            </td>
                            <td class="py-3 px-3" style="width: 15%;">{{ $category->slug }}</td>
                            <td class="py-3 px-3">
                                <a href="{{ route('posts.index', ['category_name' => $category->slug]) }}" class="text-blue-400 hover:underline">{{ $category->posts()->count() }}</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="table-pagination">
                {{ $categories->links() }}
            </div>
        </div>
    </div>
</div>
