<?php

use App\Livewire\Forms\CategoryForm;
use App\Models\Category;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Livewire\Volt\Component;

new class extends Component {
    use WithFileUploads;

    public CategoryForm $form;

    function mount(Category $category)
    {
        $this->form->setCategory($category);
    }

    public function submit()
    {
        $this->form->update();
    }
}; ?>

<div>
    <div class="title flex items-center gap-2 mb-4">
        <h1 class="inline-block text-3xl">{{ __('Edit Category') }}</h1>
    </div>
    <div class="form flex flex-col">
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
            <flux:button type="submit" variant="primary" class="cursor-pointer">Update</flux:button>
            <flux:button type="button" variant="danger" class="cursor-pointer">Delete</flux:button>
        </form>
    </div>
</div>
