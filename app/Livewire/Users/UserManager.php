<?php

namespace App\Livewire\Users;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class UserManager extends Component
{
    public ?int $editingId = null;
    public bool $showModal = false;

    public string $name = '';
    public string $email = '';
    public string $role = 'cashier';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount(): void
    {
        $this->resetForm();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->resetValidation();
        $this->editingId = null;
        $this->name = '';
        $this->email = '';
        $this->role = 'cashier';
        $this->password = '';
        $this->password_confirmation = '';
    }

    public function save(): void
    {
        $currentUser = auth()->user();
        $targetUser = $this->editingId ? User::query()->find($this->editingId) : null;

        if ($currentUser?->role === 'admin' && $targetUser?->role === 'owner') {
            return;
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->editingId)],
            'role' => ['required', 'in:owner,admin,cashier'],
        ];

        if ($this->editingId) {
            $rules['password'] = ['nullable', 'string', 'min:8', 'confirmed'];
        } else {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }

        $validated = $this->validate($rules);

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ];

        if (! empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }

        User::updateOrCreate(
            ['id' => $this->editingId],
            $payload
        );

        $this->closeModal();
    }

    public function edit(int $id): void
    {
        $this->resetValidation();
        $user = User::query()->findOrFail($id);

        if (auth()->user()?->role === 'admin' && $user->role === 'owner') {
            return;
        }

        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role ?? 'cashier';
        $this->password = '';
        $this->password_confirmation = '';
        $this->showModal = true;
    }

    public function delete(int $id): void
    {
        $currentUser = auth()->user();
        $targetUser = User::query()->findOrFail($id);

        if (auth()->id() === $id) {
            return;
        }

        if ($currentUser?->role === 'admin' && $targetUser->role === 'owner') {
            return;
        }

        $targetUser->delete();
    }

    public function render()
    {
        return view('livewire.users.user-manager', [
            'users' => User::query()->latest()->get(),
        ])->layout('layouts.app');
    }
}
