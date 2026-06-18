<?php

namespace App\Livewire\Sales;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SaleEntry extends Component
{
    public ?int $saleId = null;

    public string $barcode = '';
    public string $searchTerm = '';

    /** @var array<int, array<string, mixed>> */
    public array $items = [];

    /** @var array<int, array{id:int, barcode:string, name:string, sell_price:float}> */
    public array $searchResults = [];

    public function mount($sale = null): void
    {
        $record = $sale
            ? Sale::query()->with('items')->findOrFail($sale)
            : Sale::query()
                ->with('items')
                // ->where('user_id', auth()->id())
                ->whereDate('sold_at', now())
                ->latest('sold_at')
                ->latest('id')
                ->first();

        if (! $record) {
            return;
        }

        $this->saleId = $record->id;
        $this->items = $record->items->map(function ($item) {
        // $this->items = $record->items->sortByDesc('id')->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'barcode_snapshot' => $item->barcode_snapshot,
                'product_name_snapshot' => $item->product_name_snapshot,
                'qty' => $item->qty,
                'sell_price' => (float) $item->sell_price,
                'hpp_snapshot' => (float) $item->hpp_snapshot,
            ];
        })->toArray();
    }

    public function addByBarcode(): void
    {
        $barcode = trim($this->barcode);

        if ($barcode === '') {
            return;
        }

        $product = Product::query()
            ->where('barcode', $barcode)
            ->where('is_active', true)
            ->first();

        if (! $product) {
            $this->dispatch('scan-error', message: 'Produk dengan barcode ini tidak ditemukan.');
            $this->barcode = '';
            return;
        }

        if ($product->has_stock) {
            $availableStock = $product->stock === null ? 0 : (int) $product->stock;
            if ($availableStock < 1) {
                $this->dispatch('stock-insufficient', message: "Stok {$product->name} tidak cukup.");
                $this->barcode = '';
                return;
            }
        }

        $existingIndex = collect($this->items)->search(fn (array $item) => (int) $item['product_id'] === (int) $product->id);

        if ($existingIndex !== false) {
            $this->items[$existingIndex]['qty']++;
        } else {
            $this->items[] = [
                'product_id' => $product->id,
                'barcode_snapshot' => $product->barcode,
                'product_name_snapshot' => $product->name,
                'qty' => 1,
                'sell_price' => (float) $product->sell_price,
                'hpp_snapshot' => (float) $product->hpp,
            ];
        }

        $this->barcode = '';
        $this->persistSale();
        $this->dispatch('scan-success');
    }

    public function updatedSearchTerm(): void
    {
        $term = trim($this->searchTerm);

        if ($term === '') {
            $this->searchResults = [];
            return;
        }

        $this->searchResults = Product::query()
            ->where('is_active', true)
            ->where(function ($query) use ($term) {
                $query->where('barcode', 'like', '%' . $term . '%')
                    ->orWhere('name', 'like', '%' . $term . '%');
            })
            ->orderBy('name')
            ->limit(8)
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'barcode' => $product->barcode,
                'name' => $product->name,
                'sell_price' => (float) $product->sell_price,
            ])
            ->all();
    }

    public function addBySearch(int $productId): void
    {
        $product = Product::query()
            ->whereKey($productId)
            ->where('is_active', true)
            ->firstOrFail();

        $this->barcode = $product->barcode;
        $this->addByBarcode();
        $this->searchTerm = '';
        $this->searchResults = [];
    }

    public function incrementItem(int $index): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        $product = Product::query()
            ->whereKey($this->items[$index]['product_id'])
            ->first();

        if ($product && $product->has_stock) {
            $availableStock = $product->stock === null ? 0 : (int) $product->stock;
            if ($availableStock < 1) {
                $this->dispatch('stock-insufficient', message: "Stok {$product->name} tidak cukup.");
                return;
            }
        }

        $this->items[$index]['qty']++;
        $this->persistSale();
    }

    public function decrementItem(int $index): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        $this->items[$index]['qty']--;

        if ($this->items[$index]['qty'] <= 0) {
            $this->removeItem($index);
            return;
        }

        $this->persistSale();
    }

    public function removeItem(int $index): void
    {
        if (isset($this->items[$index])) {
            unset($this->items[$index]);
            $this->items = array_values($this->items);
            $this->persistSale();
        }
    }

    public function getSubtotalProperty(): float
    {
        return collect($this->items)->sum(fn (array $item) => $item['qty'] * $item['sell_price']);
    }

    public function getHppProperty(): float
    {
        return collect($this->items)->sum(fn (array $item) => $item['qty'] * $item['hpp_snapshot']);
    }

    public function getGrossProfitProperty(): float
    {
        return $this->subtotal - $this->hpp;
    }

    public function getMarginPercentProperty(): float
    {
        if ($this->subtotal <= 0) {
            return 0;
        }

        return ($this->grossProfit / $this->subtotal) * 100;
    }

    public function persistSale(): void
    {
        if (empty($this->items)) {
            if ($this->saleId) {
                DB::transaction(function () {
                    $sale = Sale::query()->with('items')->lockForUpdate()->findOrFail($this->saleId);

                    foreach ($sale->items as $existingItem) {
                        $product = Product::query()->lockForUpdate()->find($existingItem->product_id);

                        if ($product && $product->has_stock) {
                            $product->increment('stock', $existingItem->qty);
                        }
                    }

                    StockMovement::query()
                        ->where('reference_type', 'sale')
                        ->where('reference_id', $sale->id)
                        ->delete();

                    $sale->items()->delete();
                    $sale->delete();
                });
            }

            $this->saleId = null;
            $this->searchTerm = '';
            $this->searchResults = [];
            $this->syncDraft();
            $this->dispatch('draft-cleared');
            $this->dispatch('scan-success');
            $this->dispatch('sale-entry-table-updated');

            return;
        }

        DB::transaction(function () {
            $sale = $this->saleId
                ? Sale::query()->with('items')->lockForUpdate()->findOrFail($this->saleId)
                : new Sale();

            // Ambil existing items sebelum operasi apapun
            $existingItems = $sale->items ? $sale->items->keyBy('product_id') : collect();
            $stockRequirements = [];

            foreach ($this->items as $item) {
                $product = Product::query()->lockForUpdate()->findOrFail($item['product_id']);

                if (! $product->has_stock) {
                    continue;
                }

                $qty = (int) $item['qty'];
                $existingQty = isset($existingItems[$item['product_id']]) ? (int) $existingItems[$item['product_id']]->qty : 0;
                $requiredQty = max($qty - $existingQty, 0);

                if ($requiredQty > 0) {
                    $stockRequirements[$product->id] = ($stockRequirements[$product->id] ?? 0) + $requiredQty;
                }
            }

            foreach ($stockRequirements as $productId => $requiredQty) {
                $product = Product::query()->lockForUpdate()->findOrFail($productId);
                $availableStock = $product->stock === null ? 0 : (int) $product->stock;

                if ($availableStock < $requiredQty) {
                    $this->dispatch('stock-insufficient', message: "Stok {$product->name} tidak cukup.");
                    return;
                }
            }

            // Set sale data
            $sale->fill([
                'user_id' => auth()->id(),
                'sold_at' => $sale->exists ? $sale->sold_at : now(),
                'subtotal' => $this->subtotal,
                'total' => $this->subtotal,
            ]);
            $sale->save();

            // Hapus items yang sudah tidak ada di cart
            foreach ($existingItems as $existingItem) {
                if (! in_array($existingItem->product_id, array_column($this->items, 'product_id'))) {
                    $product = Product::query()->lockForUpdate()->find($existingItem->product_id);
                    if ($product && $product->has_stock) {
                        $product->increment('stock', $existingItem->qty);
                    }

                    StockMovement::query()
                        ->where('reference_type', 'sale')
                        ->where('reference_id', $existingItem->sale_id)
                        ->where('product_id', $existingItem->product_id)
                        ->delete();

                    $existingItem->delete();
                }
            }

            // Sync atau create items
            foreach ($this->items as $item) {
                $product = Product::query()->lockForUpdate()->findOrFail($item['product_id']);
                $qty = (int) $item['qty'];
                $stockBefore = $product->stock === null ? 0 : (int) $product->stock;

                if (isset($existingItems[$item['product_id']])) {
                    // Update existing item
                    $saleItem = $existingItems[$item['product_id']];
                    $qtyDiff = $qty - $saleItem->qty;

                    $saleItem->update([
                        'qty' => $qty,
                        'sell_price' => $item['sell_price'],
                        'hpp_snapshot' => $item['hpp_snapshot'],
                        'subtotal' => $qty * $item['sell_price'],
                    ]);

                    if ($product->has_stock && $qtyDiff !== 0) {
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
                } else {
                    // Create new item
                    if ($product->has_stock) {
                        $product->decrement('stock', $qty);
                    }

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'barcode_snapshot' => $item['barcode_snapshot'],
                        'product_name_snapshot' => $item['product_name_snapshot'],
                        'qty' => $qty,
                        'sell_price' => $item['sell_price'],
                        'hpp_snapshot' => $item['hpp_snapshot'],
                        'subtotal' => $qty * $item['sell_price'],
                    ]);

                    if ($product->has_stock) {
                        StockMovement::create([
                            'product_id' => $product->id,
                            'user_id' => auth()->id(),
                            'reference_type' => 'sale',
                            'reference_id' => $sale->id,
                            'movement_type' => 'out',
                            'qty' => $qty,
                            'stock_before' => $stockBefore,
                            'stock_after' => $stockBefore - $qty,
                            'notes' => null,
                        ]);
                    }
                }
            }

            $this->saleId = $sale->id;
        });

        $this->syncDraft();
        $this->dispatch('sale-entry-table-updated');
    }

    private function syncDraft(): void
    {
        $this->dispatch('draft-updated', state: [
            'saleId' => $this->saleId,
            'barcode' => $this->barcode,
            'items' => $this->items,
        ]);
    }

    public function render()
    {
        return view('livewire.sales.sale-entry', [
            'subtotal' => $this->subtotal,
            'hpp' => $this->hpp,
            'grossProfit' => $this->grossProfit,
            'marginPercent' => $this->marginPercent,
        ])->layout('layouts.app');
    }
}
