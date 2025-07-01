<?php

use App\Livewire\Forms\PostForm;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {

    use WithFileUploads;

    public PostForm $form;

    function with(): array {
        return [
            'categories' => Category::all()
        ];
    }

    function submit()
    {
        $this->form->store();

        return $this->redirect('/posts');
    }
}; ?>

<div>
    <div class="title flex items-center gap-2">
        <h1 class="inline-block text-3xl mb-4">{{ __('Add Post') }}</h1>
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
            <flux:field class="mb-4">
                <flux:input id="featured-image" wire:model="form.featured_image" type="file" accept="image/jpeg,image/png,/image/jpg" label="Featured Image"/>
            </flux:field>
            <div class="featured-image-container mb-4" wire:ignore>
                <img id="featured-image-result" alt="Featured image">
            </div>
            <flux:button type="submit" variant="primary" class="cursor-pointer">Submit</flux:button>
            <flux:button href="{{ route('posts.index') }}">Cancel</flux:button>
        </form>
    </div>
</div>

@push('body.scripts')
<script>
    document.getElementById('featured-image').onchange = function(e) {
        readUrl(e.target);
    };

    function readUrl(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();

            reader.onload = function (e) {
                console.log(e.target.result);
                document.getElementById('featured-image-result').setAttribute('src', e.target.result);
            }

            reader.readAsDataURL(input.files[0]); // convert into base64 string
        }
    }
</script>
@endpush
