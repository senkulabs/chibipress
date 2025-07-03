<?php

namespace App\Livewire\Forms;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Validate;
use Livewire\Form;

class UserForm extends Form
{
    public ?User $user;

    #[Validate]
    public $name = '';
    public $email = '';
    public $password = '';
    public $role = '';

    public function setUser(User $user)
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->role = $user->roles()->first() ? $user->roles()->first()->name : '';
    }

    protected function rules()
    {
        $isUpdating = $this->user && $this->user->exists();

        return [
            'name' => [
                'required',
                'min:5',
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($this->user)
            ],
            'password' => [
                $isUpdating ? 'nullable' : 'required',
                Password::min(8),
            ],
            'role' => [
                'required'
            ]
        ];
    }

    public function store()
    {
        $this->validate();

        $user = User::create($this->only(['name', 'email', 'password']));

        $user->assignRole($this->role);

        $this->reset();

        $this->user = new User();
    }

    public function update()
    {
        $this->validate();

        $this->user->update(
            $this->only(['name', 'email'])
        );

        if ($this->password) {
            $this->user->password = Hash::make($this->user->password);
            $this->user->save();
        }

        $this->user->assignRole($this->role);
    }
}
