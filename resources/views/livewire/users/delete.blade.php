<?php

use App\Models\User;
use Livewire\Volt\Component;

new class extends Component {
    const DELETE = 'delete';
    const TRANSFER = 'transfer';
    const USER_ID_ADMIN = 1;

    public $user;
    public $selectedUsers;
    public int $selectedUser = self::USER_ID_ADMIN;
    public $action;
    public $buttonDisabled = true;

    public function mount(User $user)
    {
        $this->user = $user;
        $this->selectedUsers = User::whereNot('id', '=', $user->id)->get();
    }

    public function updatedAction()
    {
        if (!empty($this->action)) {
            $this->buttonDisabled = false;
        }
    }

    public function submit()
    {
        if ($this->action === self::DELETE) {
            $transferToUser = User::find($this->selectedUser);
            $this->user->deleteWithTransfer($transferToUser);
        } else {
            $this->user->deleteWithPosts();
        }

        $this->redirect('/users');
    }
}; ?>

<div>
    <div class="title flex items-center gap-2">
        <h1 class="inline-block text-3xl mb-4">{{ __('Delete User') }}</h1>
    </div>
    <div class="form flex flex-col">
        <div class="mb-4">
            <p>You have specified this user for deletion:</p>
            <p>User: {{ $user->name }}</p>
        </div>

        <div class="mb-4">
            <form wire:submit="submit">
                <fieldset class="flex flex-col space-y-4">
                    <legend>What should be done with content owned by this user?</legend>
                    <label class="flex items-center gap-2">
                        <input type="radio" value="delete" wire:model.change="action"> Delete all content.
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="radio" value="transfer" wire:model.change="action"> Attribute all content to:
                        <select wire:model="selectedUser" class="block rounded">
                            @foreach($selectedUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->roles->first()->name }})</option>
                            @endforeach
                        </select>
                    </label>
                    <div class="flex items-center gap-4">
                        <div class="flex items-center justify-end">
                            <flux:button variant="primary" type="submit" class="w-full" :disabled="$buttonDisabled">{{ __('Confirm Deletion') }}</flux:button>
                        </div>
                    </div>
                </fieldset>
            </form>
        </div>
    </div>
</div>
