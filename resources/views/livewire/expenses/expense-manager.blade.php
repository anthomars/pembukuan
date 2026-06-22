<div class="stack" wire:keydown.escape.window="closeModal">
    <style>
        .expense-modal-backdrop {
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

        .expense-modal {
            width: min(640px, 100%);
            max-height: calc(100vh - 48px);
            overflow: auto;
            border-radius: 24px;
            background: var(--panel);
            border: 1px solid rgba(217, 226, 242, 0.85);
            box-shadow: var(--shadow);
            padding: 24px;
        }

        .expense-modal-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 20px;
        }

        .expense-modal-close {
            width: auto;
            min-width: 40px;
            padding: 8px 10px;
        }

        .expense-modal-actions {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 6px;
        }

        .button {
            width: auto;
        }

        @media (max-width: 700px) {
            .expense-modal {
                padding: 18px;
                border-radius: 20px;
            }

            .expense-modal-actions > * {
                width: 100%;
            }
        }
    </style>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" />
    <div class="toolbar">
        <div>
            <h1 style="margin:0 0 6px;">Pengeluaran</h1>
            <div class="muted">Catat uang keluar harian untuk kebutuhan operasional.</div>
        </div>
        <div class="actions">
            @if (in_array(auth()->user()?->role, ['owner', 'admin'], true))
                <button class="button" type="button" wire:click="openCreateModal">+ Tambah Pengeluaran</button>
            @endif
        </div>
    </div>

    <div class="panel" style="box-shadow:none;">
        <h3 style="margin-top:0;">Daftar Pengeluaran</h3>
        <div class="table-wrap">
            <table id="expenses-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Deskripsi</th>
                        <th>Nominal</th>
                        <th>Catatan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($expenses as $expense)
                        <tr>
                            <td>{{ $expense->expense_date->format('d/m/Y') }}</td>
                            <td>{{ $expense->description }}</td>
                            <td>{{ number_format($expense->amount, 2, ',', '.') }}</td>
                            <td>{{ $expense->notes }}</td>
                            <td>
                                <div class="actions">
                                    @if (in_array(auth()->user()?->role, ['owner', 'admin'], true))
                                    <button class="button secondary icon" type="button" wire:click="edit({{ $expense->id }})" title="Edit" aria-label="Edit pengeluaran">
                                        ✎
                                    </button>
                                    <button class="button danger icon delete-expense-button" type="button" data-expense-id="{{ $expense->id }}" title="Hapus" aria-label="Hapus pengeluaran">
                                        🗑
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="muted">Belum ada pengeluaran.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($showModal)
        <div class="expense-modal-backdrop" wire:click.self="closeModal">
            <div class="expense-modal">
                <div class="expense-modal-head">
                    <div>
                        <h3 style="margin:0 0 6px;">{{ $editingId ? 'Edit Pengeluaran' : 'Tambah Pengeluaran' }}</h3>
                        <div class="muted">Isi data pengeluaran lalu simpan dari sini.</div>
                    </div>
                    <button class="button secondary icon expense-modal-close" type="button" wire:click="closeModal" aria-label="Tutup modal">x</button>
                </div>

                <form class="stack" wire:submit.prevent="save">
                    <div>
                        <label for="expenseDate">Tanggal</label>
                        <input id="expenseDate" class="field" type="date" wire:model.blur="expenseDate">
                        @error('expenseDate') <div class="muted">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label for="description">Deskripsi</label>
                        <input id="description" class="field" type="text" wire:model.blur="description">
                        @error('description') <div class="muted">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label for="amount">Nominal</label>
                        <input id="amount" class="field" type="number" placeholder="0" wire:model.blur="amount">
                        @error('amount') <div class="muted">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label for="notes">Catatan</label>
                        <textarea id="notes" class="textarea" wire:model.blur="notes"></textarea>
                        @error('notes') <div class="muted">{{ $message }}</div> @enderror
                    </div>

                    <div class="expense-modal-actions">
                        <button class="button secondary" type="button" wire:click="closeModal">Close</button>
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
        const initExpensesTable = () => {
            const table = document.querySelector('#expenses-table');
            if (!table || typeof simpleDatatables === 'undefined') {
                return;
            }

            destroyExpensesTable();

            window.expensesDataTable = new simpleDatatables.DataTable(table, {
                searchable: true,
                fixedHeight: true,
                perPage: 10,
                perPageSelect: [5, 10, 20, 50, 100, ["ALL", -1]],
            });
        };

        const destroyExpensesTable = () => {
            if (!window.expensesDataTable) {
                return;
            }

            try {
                window.expensesDataTable.destroy();
            } catch (error) {
                console.warn('Failed to destroy existing DataTable:', error);
            }

            window.expensesDataTable = null;
        };

        const debounceInitExpensesTable = (() => {
            let timeoutId = null;

            return () => {
                if (timeoutId) {
                    clearTimeout(timeoutId);
                }

                timeoutId = window.setTimeout(() => {
                    requestAnimationFrame(initExpensesTable);
                    timeoutId = null;
                }, 50);
            };
        })();

        const getExpenseManagerComponent = () => {
            const root = document.querySelector('[wire\\:id]');
            const componentId = root ? root.getAttribute('wire:id') : null;

            if (!componentId || !window.Livewire) {
                return null;
            }

            return window.Livewire.find(componentId);
        };

        const bindExpenseManagerLivewire = () => {
            if (window.expenseManagerLivewireBound) {
                return;
            }

            initExpensesTable();
            document.addEventListener('livewire:load', () => debounceInitExpensesTable());
            if (window.Livewire && typeof window.Livewire.hook === 'function') {
                window.Livewire.hook('morph.updated', () => debounceInitExpensesTable());
                window.Livewire.hook('morphed', () => debounceInitExpensesTable());
            }
            window.expenseManagerLivewireBound = true;
        };

        const bindExpenseManagerCustomEvents = () => {
            if (window.expenseManagerCustomEventsBound) {
                return;
            }

            document.addEventListener('click', async (event) => {
                const button = event.target.closest('.delete-expense-button');
                if (!button) {
                    return;
                }

                event.preventDefault();
                const expenseId = button.dataset.expenseId;
                const component = getExpenseManagerComponent();

                if (!expenseId || !component) {
                    return;
                }

                const result = await Swal.fire({
                    title: 'Hapus pengeluaran?',
                    text: 'Data pengeluaran akan dihapus dari sistem.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                });

                if (result.isConfirmed) {
                    component.call('delete', Number(expenseId));
                }
            });

            window.expenseManagerCustomEventsBound = true;
        };

        document.addEventListener('DOMContentLoaded', () => {
            initExpensesTable();
            bindExpenseManagerLivewire();
            bindExpenseManagerCustomEvents();
        });

        document.addEventListener('livewire:init', () => {
            initExpensesTable();
            bindExpenseManagerLivewire();
            bindExpenseManagerCustomEvents();
        });
    </script>
</div>
