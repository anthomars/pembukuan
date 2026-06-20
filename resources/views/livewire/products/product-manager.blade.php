<div class="stack" wire:keydown.escape.window="closeModal">
    <style>
        .product-modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 60;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(4px);
        }

        .product-modal {
            width: min(720px, 100%);
            max-height: calc(100vh - 48px);
            overflow: auto;
            border-radius: 24px;
            background: var(--panel);
            border: 1px solid rgba(217, 226, 242, 0.85);
            box-shadow: var(--shadow);
            padding: 24px;
        }

        .product-modal-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 20px;
        }

        .product-modal-close {
            width: auto;
            min-width: 40px;
            padding: 8px 10px;
        }

        .product-modal-actions {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .button {
            width: auto;
        }

        @media (max-width: 700px) {
            .product-modal {
                padding: 18px;
                border-radius: 20px;
            }

            .product-modal-actions > * {
                width: 100%;
            }
        }
    </style>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" />
    <div class="toolbar">
        <div>
            <h1 style="margin:0 0 6px;">Master Produ k</h1>
            <div class="muted">Kelola barcode, HPP, harga jual, dan stok opsional.</div>
        </div>
        <div class="actions">
            {{-- <input class="field" style="min-width: 260px;" type="text" wire:model.live="search" placeholder="Cari nama atau barcode"> --}}
            <button class="button" type="button" wire:click="openCreateModal">+ Tambah Produk</button>
        </div>
    </div>

    <div class="panel" style="box-shadow:none;">
        <h3 style="margin-top:0;">Daftar Produk</h3>
        <div class="table-wrap">
            <table id="products-table">
                <thead>
                    <tr>
                        <th>Barcode</th>
                        <th>Nama</th>
                        <th>HPP</th>
                        <th>Harga Jual</th>
                        <th>Stok</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td>{{ $product->barcode }}</td>
                            <td>{{ $product->name }}</td>
                            <td>{{ number_format($product->hpp, 2, ',', '.') }}</td>
                            <td>{{ number_format($product->sell_price, 2, ',', '.') }}</td>
                            <td>{{ $product->has_stock ? ($product->stock ?? 0) : '-' }}</td>
                            <td>
                                <span class="badge">{{ $product->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                            </td>
                            <td>
                                <div class="actions">
                                    <button class="button secondary icon" type="button" wire:click="edit({{ $product->id }})" title="Edit">✎</button>
                                    @if (in_array(auth()->user()?->role, ['owner', 'admin'], true))
                                        <button class="button danger icon delete-product-button" type="button" data-product-id="{{ $product->id }}" title="Hapus">🗑</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="muted">Belum ada produk.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($showModal)
        <div class="product-modal-backdrop" wire:click.self="closeModal">
            <div class="product-modal">
                <div class="product-modal-head">
                    <div>
                        <h3 style="margin:0 0 6px;">{{ $editingId ? 'Edit Produk' : 'Tambah Produk' }}</h3>
                        <div class="muted">Isi detail produk lalu simpan dari sini.</div>
                    </div>
                    <button class="button secondary icon product-modal-close" type="button" wire:click="closeModal" aria-label="Tutup modal">x</button>
                </div>

                <form class="stack" wire:submit.prevent="save">
                    <div>
                        <label for="barcode">Barcode</label>
                        <input id="barcode" class="field" type="text" wire:model.blur="barcode" autofocus>
                        @error('barcode') <div class="muted">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label for="name">Nama</label>
                        <input id="name" class="field" type="text" wire:model.blur="name">
                        @error('name') <div class="muted">{{ $message }}</div> @enderror
                    </div>

                    <div class="grid" style="grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px;">
                        <div>
                            <label for="hpp">HPP</label>
                            <input id="hpp" class="field" type="number" placeholder="0" wire:model.blur="hpp">
                            @error('hpp') <div class="muted">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label for="sellPrice">Harga Jual</label>
                            <input id="sellPrice" class="field" type="number" placeholder="0" wire:model.blur="sellPrice">
                            @error('sellPrice') <div class="muted">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div>
                        <label style="display:flex;align-items:center;gap:10px;">
                            <input type="checkbox" wire:model.live="hasStock">
                            Pakai stok
                        </label>
                    </div>

                    @if ($hasStock)
                        <div>
                            <label for="stock">Stok</label>
                            <input id="stock" class="field" type="number" min="0" step="1" wire:model.blur="stock">
                            @error('stock') <div class="muted">{{ $message }}</div> @enderror
                        </div>
                    @endif

                    <div>
                        <label style="display:flex;align-items:center;gap:10px;">
                            <input type="checkbox" wire:model.live="isActive">
                            Aktif
                        </label>
                    </div>

                    <div class="product-modal-actions">
                        <button class="button secondary" type="button" wire:click="closeModal">close</button>
                        <button class="button" type="submit">
                            {{ $editingId ? 'Update' : 'Save' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest"></script>
    <script>
        const initProductsTable = () => {
            const table = document.querySelector('#products-table');
            if (!table || typeof simpleDatatables === 'undefined') {
                return;
            }

            destroyProductsTable();

            window.productsDataTable = new simpleDatatables.DataTable(table, {
                searchable: true,
                fixedHeight: true,
                perPage: 10,
                perPageSelect: [5, 10, 20, 50, 100, ["ALL", -1]],
            });
        };

        const destroyProductsTable = () => {
            if (!window.productsDataTable) {
                return;
            }

            try {
                window.productsDataTable.destroy();
            } catch (error) {
                console.warn('Failed to destroy existing DataTable:', error);
            }

            window.productsDataTable = null;
        };

        const debounceInitProductsTable = (() => {
            let timeoutId = null;

            return () => {
                if (timeoutId) {
                    clearTimeout(timeoutId);
                }

                timeoutId = window.setTimeout(() => {
                    requestAnimationFrame(initProductsTable);
                    timeoutId = null;
                }, 50);
            };
        })();

        const getProductManagerComponent = () => {
            const root = document.querySelector('[wire\\:id]');
            const componentId = root ? root.getAttribute('wire:id') : null;

            if (!componentId || !window.Livewire) {
                return null;
            }

            return window.Livewire.find(componentId);
        };

        const bindProductManagerLivewire = () => {
            if (window.productManagerLivewireBound) {
                return;
            }

            initProductsTable();
            document.addEventListener('livewire:load', () => debounceInitProductsTable());
            if (window.Livewire && typeof window.Livewire.hook === 'function') {
                window.Livewire.hook('morph.updated', () => debounceInitProductsTable());
                window.Livewire.hook('morphed', () => debounceInitProductsTable());
            }
            window.productManagerLivewireBound = true;
        };

        const bindProductManagerCustomEvents = () => {
            if (window.productManagerCustomEventsBound) {
                return;
            }

            document.addEventListener('click', async (event) => {
                const button = event.target.closest('.delete-product-button');
                if (!button) {
                    return;
                }

                event.preventDefault();
                const productId = button.dataset.productId;
                const component = getProductManagerComponent();

                if (!productId || !component) {
                    return;
                }

                const result = await Swal.fire({
                    title: 'Hapus produk?',
                    text: 'Produk akan dihapus dari sistem.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                });

                if (result.isConfirmed) {
                    component.call('delete', Number(productId));
                }
            });

            window.productManagerCustomEventsBound = true;
        };

        document.addEventListener('DOMContentLoaded', () => {
            initProductsTable();
            bindProductManagerLivewire();
            bindProductManagerCustomEvents();
        });

        document.addEventListener('livewire:init', () => {
            initProductsTable();
            bindProductManagerLivewire();
            bindProductManagerCustomEvents();
        });
    </script>
</div>
