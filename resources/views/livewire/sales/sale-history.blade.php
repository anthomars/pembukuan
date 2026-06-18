<div class="stack">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" />
    <div class="toolbar">
        <div>
            <h1 style="margin:0 0 6px;">Transaksi</h1>
            <div class="muted">Lihat, edit, atau hapus penjualan per tanggal.</div>
        </div>
            <div class="actions">
            <div>
                <label for="reportDate">Tanggal</label>
                <input id="reportDate" class="field" type="date" wire:model.live="reportDate">
            </div>
            <a class="button secondary" href="/penjualan" style="display:inline-flex;align-items:center;">+ Penjualan Baru</a>
        </div>
    </div>

    <div class="panel" style="box-shadow:none;">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" />
        <div class="table-wrap">
            <table id="sales-table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Barcode</th>
                        <th>Qty</th>
                        <th>HPP</th>
                        <th>Price</th>
                        <th>Income</th>
                        <th>HPP All</th>
                        <th>Margin</th>
                        <th>%</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sales as $sale)
                        @foreach ($sale->items as $item)
                            @php
                                $income = $item->qty * $item->sell_price;
                                $hppAll = $item->qty * $item->hpp_snapshot;
                                $margin = $income - $hppAll;
                                $marginPercent = $hppAll > 0 ? ($margin / $hppAll) * 100 : 0;
                            @endphp
                            <tr>
                                <td>{{ $item->product_name_snapshot }}</td>
                                <td>{{ $item->barcode_snapshot }}</td>
                                @if ($editingSaleId === $sale->id && $editingItemId === $item->id)
                                    <td style="min-width: 90px;">
                                        <input
                                            type="number"
                                            min="1"
                                            class="field"
                                            style="width:80px;"
                                            wire:model.live="editingSaleItems.0.qty"
                                        >
                                    </td>
                                    <td>{{ number_format($item->hpp_snapshot, 2, ',', '.') }}</td>
                                    <td style="min-width: 120px;">
                                        <input
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            class="field"
                                            style="width:100px;"
                                            wire:model.live="editingSaleItems.0.sell_price"
                                        >
                                    </td>
                                @else
                                    <td>{{ $item->qty }}</td>
                                    <td>{{ number_format($item->hpp_snapshot, 2, ',', '.') }}</td>
                                    <td>{{ number_format($item->sell_price, 2, ',', '.') }}</td>
                                @endif
                                <td>{{ number_format($income, 2, ',', '.') }}</td>
                                <td>{{ number_format($hppAll, 2, ',', '.') }}</td>
                                <td>{{ number_format($margin, 2, ',', '.') }}</td>
                                <td>{{ number_format($marginPercent, 2, ',', '.') }}%</td>
                                <td style="vertical-align:middle;">
                                    <div style="display:flex;gap:8px;justify-content:center;">
                                        @if ($editingSaleId === $sale->id && $editingItemId === $item->id)
                                            <button type="button" class="button small" wire:click="saveSale()" style="width:auto;">✔</button>
                                            <button type="button" class="button secondary small" wire:click="cancelEdit()" style="width:auto;">✕</button>
                                        @else
                                            <button type="button" class="button secondary icon" wire:click="editSale({{ $sale->id }}, {{ $item->id }})" title="Edit">✎</button>
                                            <button type="button" class="button danger icon delete-sale-item-button" data-sale-id="{{ $sale->id }}" data-item-id="{{ $item->id }}" title="Hapus">🗑</button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="10" class="muted">Belum ada transaksi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest"></script>
    <script>
        const initSalesTable = () => {
            const table = document.querySelector('#sales-table');
            if (!table || typeof simpleDatatables === 'undefined') {
                return;
            }

            destroySalesTable();

            window.salesDataTable = new simpleDatatables.DataTable(table, {
                searchable: true,
                fixedHeight: true,
                perPage: 10,
                perPageSelect: [5, 10, 20, 50, 100, ["ALL", -1]],
            });
        };

        const destroySalesTable = () => {
            if (!window.salesDataTable) {
                return;
            }

            try {
                window.salesDataTable.destroy();
            } catch (error) {
                console.warn('Failed to destroy existing DataTable:', error);
            }

            window.salesDataTable = null;
        };

        const debounceInitSalesTable = (() => {
            let timeoutId = null;

            return () => {
                if (timeoutId) {
                    clearTimeout(timeoutId);
                }

                timeoutId = window.setTimeout(() => {
                    requestAnimationFrame(initSalesTable);
                    timeoutId = null;
                }, 50);
            };
        })();

        const getSaleHistoryComponent = () => {
            const root = document.querySelector('[wire\\:id]');
            const componentId = root ? root.getAttribute('wire:id') : null;

            if (!componentId || !window.Livewire) {
                return null;
            }

            return window.Livewire.find(componentId);
        };

        const bindSaleHistoryLivewire = () => {
            if (window.saleHistoryLivewireBound) {
                return;
            }

            initSalesTable();
            document.addEventListener('livewire:load', () => debounceInitSalesTable());
            if (window.Livewire && typeof window.Livewire.hook === 'function') {
                window.Livewire.hook('morph.updated', () => debounceInitSalesTable());
                window.Livewire.hook('morphed', () => debounceInitSalesTable());
            }
            window.saleHistoryLivewireBound = true;
        };

        const bindSaleHistoryCustomEvents = () => {
            if (window.saleHistoryCustomEventsBound) {
                return;
            }

            document.addEventListener('click', async (event) => {
                const button = event.target.closest('.delete-sale-item-button');
                if (!button) {
                    return;
                }

                event.preventDefault();
                const saleId = button.dataset.saleId;
                const itemId = button.dataset.itemId;
                const component = getSaleHistoryComponent();

                if (!saleId || !itemId || !component) {
                    return;
                }

                const result = await Swal.fire({
                    title: 'Hapus item?',
                    text: 'Item akan dihapus dari transaksi.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                });

                if (result.isConfirmed) {
                    component.call('deleteItem', Number(saleId), Number(itemId));
                }
            });

            window.addEventListener('stock-insufficient', async (event) => {
                const message = event.detail?.message || 'Stok tidak cukup.';
                await Swal.fire({
                    title: 'Stok tidak cukup',
                    text: message,
                    icon: 'warning',
                    confirmButtonText: 'OK',
                });
            });

            window.saleHistoryCustomEventsBound = true;
        };

        document.addEventListener('DOMContentLoaded', () => {
            initSalesTable();
            bindSaleHistoryLivewire();
            bindSaleHistoryCustomEvents();
        });

        document.addEventListener('livewire:init', () => {
            initSalesTable();
            bindSaleHistoryLivewire();
            bindSaleHistoryCustomEvents();
        });
    </script>
</div>
