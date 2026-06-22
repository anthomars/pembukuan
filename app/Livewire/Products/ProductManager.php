<?php

namespace App\Livewire\Products;

use App\Models\Product;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ProductManager extends Component
{
    public string $search = '';

    public ?int $editingId = null;
    public bool $showModal = false;

    public string $barcode = '';
    public string $name = '';
    public float|string $hpp = '';
    public float|string $sellPrice = '';
    public bool $hasStock = false;
    public ?int $stock = null;
    public bool $isActive = true;

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
        $this->barcode = '';
        $this->name = '';
        $this->hpp = '';
        $this->sellPrice = '';
        $this->hasStock = false;
        $this->stock = null;
        $this->isActive = true;
    }

    public function save(): void
    {
        if (! in_array(auth()->user()?->role, ['owner', 'admin'], true)) {
            return;
        }

        $validated = $this->validate([
            'barcode' => ['required', 'string', 'max:255', Rule::unique('products', 'barcode')->ignore($this->editingId)],
            'name' => ['required', 'string', 'max:255'],
            'hpp' => ['required', 'numeric', 'min:0'],
            'sellPrice' => ['required', 'numeric', 'min:0'],
            'hasStock' => ['boolean'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'isActive' => ['boolean'],
        ]);

        Product::updateOrCreate(
            ['id' => $this->editingId],
            [
                'barcode' => $validated['barcode'],
                'name' => $validated['name'],
                'hpp' => $validated['hpp'],
                'sell_price' => $validated['sellPrice'],
                'has_stock' => $validated['hasStock'],
                'stock' => $validated['hasStock'] ? ($validated['stock'] ?? 0) : null,
                'is_active' => $validated['isActive'],
            ]
        );

        $this->closeModal();
    }

    public function edit(int $id): void
    {
        if (! in_array(auth()->user()?->role, ['owner', 'admin'], true)) {
            return;
        }

        $this->resetValidation();
        $product = Product::query()->findOrFail($id);

        $this->editingId = $product->id;
        $this->barcode = $product->barcode;
        $this->name = $product->name;
        $this->hpp = (float) $product->hpp;
        $this->sellPrice = (float) $product->sell_price;
        $this->hasStock = (bool) $product->has_stock;
        $this->stock = $product->stock;
        $this->isActive = (bool) $product->is_active;
        $this->showModal = true;
    }

    public function delete(int $id): void
    {
        if (! in_array(auth()->user()?->role, ['owner', 'admin'], true)) {
            return;
        }

        Product::query()->findOrFail($id)->delete();
    }

    public function render()
    {
        $products = Product::query()
            ->when($this->search !== '', function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('barcode', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->get();

        return view('livewire.products.product-manager', [
            'products' => $products,
        ])->layout('layouts.app');
    }
}
