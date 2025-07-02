<?php

use App\Livewire\Forms\PostForm;
use App\Models\Category;
use App\Models\Post;
use Livewire\Attributes\On;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Livewire\Volt\Component;

new class extends Component {
    use WithFileUploads;

    public PostForm $form;

    public $showMediaLibrary = false;

    function mount(Post $post)
    {
        $this->form->setPost($post);
    }

    public function openMediaLibrary()
    {
        $this->showMediaLibrary = true;
    }

    public function closeMediaLibrary()
    {
        $this->showMediaLibrary = false;
    }

    #[On('fileConfirmed')]
    public function setFeaturedImage($fileData)
    {
        $this->form->featuredImage = $fileData;
        $this->showMediaLibrary = false;
    }

    public function removeFeaturedImage()
    {
        $this->form->featuredImage = null;
    }

    function with(): array {
        return [
            'categories' => Category::all()
        ];
    }

    public function submit()
    {
        $this->form->update();
    }
}; ?>

<div>
    <div class="title flex items-center gap-2 mb-4">
        <h1 class="inline-block text-3xl">{{ __('Edit Post') }}</h1>
        <flux:button href="{{ route('posts.create') }}">{{ __('Add Post') }}</flux:button>
    </div>
    <div class="form flex flex-col">
        <form wire:submit="submit">
            <flux:field class="mb-4">
                <flux:input wire:model="form.title" placeholder="Add title"/>
                @error('form.title')
                    <flux:error name="form.title" message="{{ $message }}" />
                @enderror
            </flux:field>
            <flux:field class="mb-4">
                <flux:label for="content">Content</flux:label>
                <x-editor wire:model="form.content" />
                @error('form.content')
                    <flux:error name="form.content" message="{{ $message }}" />
                @enderror
            </flux:field>
            <flux:field class="mb-4">
                <flux:label>Summary</flux:label>
                <flux:description>Create summary of post</flux:description>
                <flux:textarea rows="2"/>
            </flux:field>
            <flux:checkbox.group wire:model="form.categories" label="Categories" class="mb-4">
                @foreach ($categories as $category)
                    <flux:checkbox label="{{ $category->name }}" value="{{ $category->slug }}" />
                @endforeach
            </flux:checkbox.group>
            <flux:field class="mb-4">
                <flux:label>Status</flux:label>
                <flux:select wire:model="form.status" placeholder="Status">
                    @foreach (get_post_status() as $value)
                        @php
                            $selected_value = 'draft';
                            $selected_attr = '';
                            if ($value === $selected_value) {
                                $selected_attr = 'selected';
                            }
                        @endphp
                        <option value="{{ $value }}" {{ $selected_attr }}>{{ ucfirst($value) }}</option>
                    @endforeach
                    @error('form.status')
                        <flux:error name="form.status" message="{{ $message }}" />
                    @enderror
                </flux:select>
            </flux:field>
            @if($form->featuredImage)
            <flux:field class="mb-4">
                <div class="featured-image-preview">
                    <img src="{{ $form->featuredImage['url'] }}" alt="{{ $form->featuredImage['name'] }}" style="max-width: 300px; height: auto;">
                    <div class="image-actions">
                        <span class="image-name">{{ $form->featuredImage['name'] }}</span>
                        <flux:button variant="primary" type="button" wire:click="openMediaLibrary">Change Image</flux:button>
                        <flux:button variant="danger" type="button" wire:click="removeFeaturedImage">Remove</flux:button>
                    </div>
                </div>
            </flux:field>
            @else
            <div class="no-featured-image mb-4 flex gap-2 items-center">
                <p>No featured image selected</p>
                <flux:button variant="primary" type="button" wire:click="openMediaLibrary">Select Featured Image</flux:button>
            </div>
            @endif
            <flux:button type="submit" variant="primary" class="cursor-pointer">Submit</flux:button>
            <flux:button href="{{ route('posts.index') }}">Cancel</flux:button>
        </form>
    </div>
    <!-- Media Library Modal -->
    @if($showMediaLibrary)
    <div class="modal-overlay" wire:ignore>
        <div class="modal-content">
            <div class="modal-header">
                <h3>Select Featured Image</h3>
                <button type="button" wire:click="closeMediaLibrary" class="close-btn">&times;</button>
            </div>
            <div class="modal-body">
                <livewire:media-library
                    :selection-mode="true"
                    :selected-file-id="$featuredImage['id'] ?? null"
                    :allowed-types="['image']"
                    :max-selections="1"
                    @file-confirmed="setFeaturedImage($event.detail)"
                    @media-library-cancelled="closeMediaLibrary"
                />
            </div>
        </div>
    </div>
    @endif
</div>

@assets
<style>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    border-radius: 8px;
    width: 90%;
    max-width: 1200px;
    max-height: 80vh;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
}

.close-btn {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #999;
}

.modal-body {
    padding: 0;
    max-height: calc(80vh - 80px);
    overflow-y: auto;
}

.error {
    color: #dc3545;
    font-size: 12px;
    margin-top: 5px;
}
</style>
@endassets
