<?php

use App\Livewire\Forms\PostForm;
use Livewire\Volt\Component;

new class extends Component {
    public PostForm $form;

    function submit()
    {
        $this->form->store();
    }
}; ?>

<div>
    <form wire:submit="submit">
        <flux:field class="mb-4">
            <flux:label>Title</flux:label>
            <flux:input wire:model="form.title" />
            @error('form.title')
                <flux:error name="form.title" message="{{ $message }}" />
            @enderror
        </flux:field>
        <flux:field class="mb-4">
            <flux:label>Content</flux:label>
            <flux:textarea wire:model="form.content" aria-placeholder="What's on your mind?" />
            @error('form.content')
                <flux:error name="form.content" message="{{ $message }}" />
            @enderror
        </flux:field>
        <flux:button type="submit" variant="primary" class="cursor-pointer">Save Draft</flux:button>
    </form>
</div>
