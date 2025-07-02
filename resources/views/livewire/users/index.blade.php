<?php

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    #[Url(as: 'q')]
    public $search = '';

    #[Url(as: 'role')]
    public $role = '';

    #[Computed()]
    public function users()
    {
        $users = User::query();

        if ($this->search) {
            $users->where('name', 'like', "%{$this->search}%");
        }

        if ($this->role) {
            $users->role($this->role);
        }

        return $users->paginate(10);
    }

    public function with(): array
    {
        return [
            'totalCount' => User::count(),
            'adminCount' => User::role('admin')->count(),
            'editorCount' => User::role('editor')->count(),
            'authorCount' => User::role('author')->count(),
        ];
    }
}; ?>

<div>
    <div class="title flex items-center gap-2 mb-4">
        <h1 class="inline-block text-3xl">{{ __('Users') }}</h1>
        <flux:button href="{{ route('users.create') }}">{{ __('Add User') }}</flux:button>
    </div>
    <div class="control flex justify-between items-center mb-4">
        <div class="filter">
            <ul class="flex gap-2">
                <li>
                    <a href="{{ route('users.index') }}"
                    class="{{ request()->fullUrlIs(route('users.index')) ? 'font-bold' : 'text-blue-400' }}">All</a> <span>({{ $totalCount }})</span> |
                </li>
                <li>
                    <a href="{{ route('users.index', ['role' => 'admin']) }}"
                    class="{{ request()->fullUrlIs(route('users.index', ['role' => 'admin'])) ? 'font-bold' : 'text-blue-400' }}">Administrator</a> <span>({{ $adminCount }})</span> |
                </li>
                <li>
                    <a href="{{ route('users.index', ['role' => 'editor']) }}"
                    class="{{ request()->fullUrlIs(route('users.index', ['role' => 'editor'])) ? 'font-bold' : 'text-blue-400' }}">Editor</a> <span>({{ $editorCount }})</span> |
                </li>
                <li>
                    <a href="{{ route('users.index', ['role' => 'author']) }}"
                    class="{{ request()->fullUrlIs(route('users.index', ['role' => 'author'])) ? 'font-bold' : 'text-blue-400' }}">Author</a> <span>({{ $authorCount }})</span>
                </li>
            </ul>
        </div>
        <div class="search">
            <flux:input.group>
                <flux:input wire:model.change="search" />
                <flux:button>Search Users</flux:button>
            </flux:input.group>
        </div>
    </div>
    <div class="table-control">
        <div class="grid overflow-x-auto">
            <table class="w-full table-auto divide-y">
                <thead>
                    <tr>
                        <th class="py-3 px-3 text-left">Name</th>
                        <th class="py-3 px-3 text-left">Email</th>
                        <th class="py-3 px-3 text-left">Role</th>
                        <th class="py-3 px-3 text-left">Posts</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach ($this->users as $user)
                    <tr wire:key="{{ $user->id }}">
                        <td class="py-3 px-3">
                            {{ $user->name }}
                            <div>
                                <a href="#" class="text-blue-400 hover:underline">Edit</a> |
                                <a href="#" wire:click="#" class="text-red-500 hover:underline">Delete</a>
                            </div>
                        </td>
                        <td class="py-3 px-3" style="width: 25%;">
                            {{ $user->email }}
                        </td>
                        <td class="py-3 px-3" style="width: 14%;">
                            {{ ucfirst($user->roles()->first()->name) }}
                        </td>
                        <td class="py-3 px-3" style="width: 14%;">
                            {{ $user->posts->count() }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="table-pagination border-t">
            {{ $this->users->links() }}
        </div>
    </div>
</div>
