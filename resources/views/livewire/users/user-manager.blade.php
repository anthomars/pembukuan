<div class="stack" wire:keydown.escape.window="closeModal">
    <style>
        .user-modal-backdrop {
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

        .user-modal {
            width: min(640px, 100%);
            max-height: calc(100vh - 48px);
            overflow: auto;
            border-radius: 24px;
            background: var(--panel);
            border: 1px solid rgba(217, 226, 242, 0.85);
            box-shadow: var(--shadow);
            padding: 24px;
        }

        .user-modal-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 20px;
        }

        .user-modal-close {
            width: auto;
            min-width: 40px;
            padding: 8px 10px;
        }

        .user-modal-actions {
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
            .user-modal {
                padding: 18px;
                border-radius: 20px;
            }

            .user-modal-actions > * {
                width: 100%;
            }
        }
    </style>

    <div class="toolbar">
        <div>
            <h1 style="margin:0 0 6px;">Manajemen User</h1>
            <div class="muted">Kelola akun owner, admin, dan kasir.</div>
        </div>
        <div class="actions">
            <button class="button" type="button" wire:click="openCreateModal">+ Tambah User</button>
        </div>
    </div>

    <div class="panel" style="box-shadow:none;">
        <h3 style="margin-top:0;">Daftar User</h3>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->role ?? 'cashier' }}</td>
                            <td>
                                <div class="actions">
                                    @if (! (auth()->user()?->role === 'admin' && $user->role === 'owner'))
                                        <button class="button secondary icon" type="button" wire:click="edit({{ $user->id }})" title="Edit" aria-label="Edit user">
                                            ✎
                                        </button>
                                    @endif
                                    @if (auth()->id() !== $user->id && ! (auth()->user()?->role === 'admin' && $user->role === 'owner'))
                                        <button class="button danger icon delete-user-button" type="button" data-user-id="{{ $user->id }}" title="Hapus" aria-label="Hapus user">
                                            🗑
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="muted">Belum ada user.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($showModal)
        <div class="user-modal-backdrop" wire:click.self="closeModal">
            <div class="user-modal">
                <div class="user-modal-head">
                    <div>
                        <h3 style="margin:0 0 6px;">{{ $editingId ? 'Edit User' : 'Tambah User' }}</h3>
                        <div class="muted">Isi data akun lalu simpan dari sini.</div>
                    </div>
                    <button class="button secondary icon user-modal-close" type="button" wire:click="closeModal" aria-label="Tutup modal">x</button>
                </div>

                <form class="stack" wire:submit.prevent="save">
                    <div>
                        <label for="name">Nama</label>
                        <input id="name" class="field" type="text" wire:model.blur="name">
                        @error('name') <div class="muted">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label for="email">Email</label>
                        <input id="email" class="field" type="email" wire:model.blur="email">
                        @error('email') <div class="muted">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label for="role">Role</label>
                        <select id="role" class="field" wire:model.live="role">
                            <option value="owner">Owner</option>
                            <option value="admin">Admin</option>
                            <option value="cashier">Cashier</option>
                        </select>
                        @error('role') <div class="muted">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label for="password">Password</label>
                        <input id="password" class="field" type="password" wire:model.blur="password">
                        @error('password') <div class="muted">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label for="passwordConfirmation">Konfirmasi Password</label>
                        <input id="passwordConfirmation" class="field" type="password" wire:model.blur="password_confirmation">
                    </div>

                    <div class="user-modal-actions">
                        <button class="button secondary" type="button" wire:click="closeModal">Close</button>
                        <button class="button" type="submit">
                            {{ $editingId ? 'Update' : 'Save' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <script>
        const getUserManagerComponent = () => {
            const root = document.querySelector('[wire\\:id]');
            const componentId = root ? root.getAttribute('wire:id') : null;

            if (!componentId || !window.Livewire) {
                return null;
            }

            return window.Livewire.find(componentId);
        };

        const bindUserManagerDeleteConfirmation = () => {
            if (window.userManagerDeleteConfirmationBound) {
                return;
            }

            document.addEventListener('click', async (event) => {
                const button = event.target.closest('.delete-user-button');
                if (!button) {
                    return;
                }

                event.preventDefault();

                const userId = button.dataset.userId;
                const component = getUserManagerComponent();

                if (!userId || !component) {
                    return;
                }

                const result = await Swal.fire({
                    title: 'Hapus user?',
                    text: 'User akan dihapus dari sistem.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                });

                if (result.isConfirmed) {
                    component.call('delete', Number(userId));
                }
            });

            window.userManagerDeleteConfirmationBound = true;
        };

        document.addEventListener('DOMContentLoaded', bindUserManagerDeleteConfirmation);
        document.addEventListener('livewire:init', bindUserManagerDeleteConfirmation);
    </script>
</div>
