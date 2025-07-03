<?php

use App\Livewire\Forms\UserForm;
use App\Models\User;
use Livewire\Volt\Component;
use Spatie\Permission\Models\Role;

new class extends Component {

    public UserForm $form;

    public function mount(User $user)
    {
        $this->form->setUser($user);
    }

    public function with(): array
    {
        return [
            'roles' => Role::all(),
        ];
    }

    function submit()
    {
        $this->form->update();

        return $this->redirect('/users');
    }
}; ?>

<div>
    <div class="title flex items-center gap-2">
        <h1 class="inline-block text-3xl mb-4">{{ __('Edit User') }}</h1>
    </div>
    <div class="form flex flex-col">
        <form wire:submit="submit" class="my-6 w-full space-y-6">
            <flux:input wire:model="form.name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

            <div>
                <flux:input wire:model="form.email" :label="__('Email')" type="email" required autocomplete="email" />
            </div>

            <flux:field>
                <flux:input
                    wire:model="form.password"
                    :label="__('New password')"
                    type="password"
                    autocomplete="new-password"
                />
            </flux:field>

            <flux:field>
                <flux:select wire:model="form.role" placeholder="Role">
                @foreach ($roles as $role)
                    <flux:select.option wire:key="{{ $role->id }}" value="{{ $role->name }}">{{ ucfirst($role->name) }}</flux:select.option>
                @endforeach
                </flux:select>
                @error('form.role')
                    <flux:error name="form.role" message="{{ $message }}" />
                @enderror
            </flux:field>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('Save') }}</flux:button>
                </div>
            </div>
        </form>
    </div>
</div>
