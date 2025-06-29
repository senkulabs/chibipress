<?php

use App\Livewire\Forms\PageForm;
use App\Models\Page;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Livewire\Volt\Component;

new class extends Component {
    use WithFileUploads;

    public PageForm $form;

    function mount(Page $page)
    {
        $this->form->setPage($page);
    }

    public function submit()
    {
        $this->form->update();
    }
}; ?>

<div>
    <div class="title flex items-center gap-2 mb-4">
        <h1 class="inline-block text-3xl">{{ __('Edit Page') }}</h1>
        <flux:button href="{{ route('pages.create') }}">{{ __('Add Page') }}</flux:button>
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
                <flux:textarea wire:model="form.content" id="content" />
                @error('form.content')
                    <flux:error name="form.content" message="{{ $message }}" />
                @enderror
            </flux:field>
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
            <flux:button type="submit" variant="primary" class="cursor-pointer">Submit</flux:button>
            <flux:button href="{{ route('posts.index') }}">Cancel</flux:button>
        </form>
    </div>
</div>

