<?php

namespace App\Livewire\Sales;

use App\Models\Sale;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SaleHistory extends Component
{
    public string $reportDate;
    public ?int $editingSaleId = null;
    public ?int $editingItemId = null;
    public array $editingSaleItems = [];

    public function mount(): void
    {
        $this->reportDate = now()->toDateString();
    }

    public function editSale(int $saleId, int $itemId): void
    {
        $sale = Sale::query()->with('items')->findOrFail($saleId);
        $item = $sale->items->firstWhere('id', $itemId);

        if (!$item) {
            return;
        }

        $this->editingSaleId = $saleId;
        $this->editingItemId = $itemId;
        $this->editingSaleItems = [
            [
                'id' => $item->id,
                'product_name_snapshot' => $item->product_name_snapshot,
                'barcode_snapshot' => $item->barcode_snapshot,
                'qty' => $item->qty,
                'sell_price' => (float) $item->sell_price,
                'hpp_snapshot' => (float) $item->hpp_snapshot,
            ]
        ];
    }

    public function cancelEdit(): void
    {
        $this->editingSaleId = null;
        $this->editingItemId = null;
        $this->editingSaleItems = [];
    }

    public function saveSale(): void
    {
        if ($this->editingSaleId === null || $this->editingItemId === null) {
            return;
        }

        $this->validate([
            'editingSaleItems' => 'required|array|min:1',
            'editingSaleItems.0.id' => 'required|integer|exists:sale_items,id',
            'editingSaleItems.0.qty' => 'required|integer|min:1',
            'editingSaleItems.0.sell_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () {
            $sale = Sale::query()->with('items')->lockForUpdate()->findOrFail($this->editingSaleId);
            $editedItem = $this->editingSaleItems[0];
            $item = $sale->items->firstWhere('id', $editedItem['id']);

            if (!$item) {
                return;
            }

            $qty = (int) $editedItem['qty'];
            $sellPrice = round((float) $editedItem['sell_price'], 2);
            $qtyDiff = $qty - $item->qty;
            $product = $item->product()->lockForUpdate()->first();

            if ($product && $product->has_stock && $qtyDiff > 0) {
                $availableStock = $product->stock === null ? 0 : (int) $product->stock;

                if ($availableStock < $qtyDiff) {
                    $this->dispatch('stock-insufficient', message: "Stok {$product->name} tidak cukup.");
                    return;
                }
            }

            $item->update([
                'qty' => $qty,
                'sell_price' => $sellPrice,
                'subtotal' => $qty * $sellPrice,
            ]);

            // Update stok jika ada perubahan qty
            if ($qtyDiff !== 0) {
                if ($product && $product->has_stock) {
                    $stockBefore = $product->stock === null ? 0 : (int) $product->stock;

                    if ($qtyDiff > 0) {
                        $product->decrement('stock', $qtyDiff);

                        StockMovement::create([
                            'product_id' => $product->id,
                            'user_id' => auth()->id(),
                            'reference_type' => 'sale',
                            'reference_id' => $sale->id,
                            'movement_type' => 'out',
                            'qty' => $qtyDiff,
                            'stock_before' => $stockBefore,
                            'stock_after' => $stockBefore - $qtyDiff,
                            'notes' => 'Adjustment',
                        ]);
                    } else {
                        $restockQty = abs($qtyDiff);
                        $product->increment('stock', $restockQty);

                        StockMovement::create([
                            'product_id' => $product->id,
                            'user_id' => auth()->id(),
                            'reference_type' => 'sale',
                            'reference_id' => $sale->id,
                            'movement_type' => 'in',
                            'qty' => $restockQty,
                            'stock_before' => $stockBefore,
                            'stock_after' => $stockBefore + $restockQty,
                            'notes' => 'Adjustment',
                        ]);
                    }
                }
            }

            // Update sale subtotal
            $subtotal = collect($sale->items)->sum(fn ($i) => $i->qty * $i->sell_price);
            $sale->update(['subtotal' => $subtotal, 'total' => $subtotal]);
        });

        $this->cancelEdit();
    }

    public function deleteItem(int $saleId, int $itemId): void
    {
        DB::transaction(function () use ($saleId, $itemId) {
            $sale = Sale::query()->with('items')->lockForUpdate()->findOrFail($saleId);
            $item = $sale->items->firstWhere('id', $itemId);

            if (!$item) {
                return;
            }

            $product = $item->product()->lockForUpdate()->first();

            if ($product && $product->has_stock) {
                $product->increment('stock', $item->qty);
            }

            StockMovement::query()
                ->where('reference_type', 'sale')
                ->where('reference_id', $sale->id)
                ->where('product_id', $item->product_id)
                ->delete();

            $item->delete();

            // Hapus sale jika tidak ada items lagi
            if ($sale->items()->count() === 0) {
                $sale->delete();
            } else {
                // Update sale subtotal
                $subtotal = collect($sale->items)->sum(fn ($i) => $i->qty * $i->sell_price);
                $sale->update(['subtotal' => $subtotal, 'total' => $subtotal]);
            }
        });
    }

    public function render()
    {
        $sales = Sale::query()
            ->with('items')
            ->whereDate('sold_at', $this->reportDate)
            ->latest('sold_at')
            ->latest('id')
            ->get();

        return view('livewire.sales.sale-history', [
            'sales' => $sales,
        ])->layout('layouts.app');
    }
}
