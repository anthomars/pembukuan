<?php

namespace App\Livewire\Expenses;

use App\Models\Expense;
use Livewire\Component;

class ExpenseManager extends Component
{
    public string $expenseDate;

    public ?int $editingId = null;
    public bool $showModal = false;

    public string $description = '';
    public float|string $amount = '';
    public string $notes = '';

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
        $this->expenseDate = now()->toDateString();
        $this->description = '';
        $this->amount = '';
        $this->notes = '';
    }

    public function save(): void
    {
        $validated = $this->validate([
            'expenseDate' => ['required', 'date'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        Expense::updateOrCreate(
            ['id' => $this->editingId],
            [
                'user_id' => auth()->id(),
                'expense_date' => $validated['expenseDate'],
                'description' => $validated['description'],
                'amount' => $validated['amount'],
                'notes' => $validated['notes'] ?: null,
            ]
        );

        $this->closeModal();
    }

    public function edit(int $id): void
    {
        $this->resetValidation();
        $expense = Expense::query()->findOrFail($id);

        $this->editingId = $expense->id;
        $this->expenseDate = $expense->expense_date->toDateString();
        $this->description = $expense->description;
        $this->amount = (float) $expense->amount;
        $this->notes = $expense->notes ?? '';
        $this->showModal = true;
    }

    public function delete(int $id): void
    {
        Expense::query()->findOrFail($id)->delete();
    }

    public function render()
    {
        return view('livewire.expenses.expense-manager', [
            'expenses' => Expense::query()
                ->latest('expense_date')
                ->latest('id')
                ->get(),
        ])->layout('layouts.app');
    }
}
