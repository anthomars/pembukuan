<div class="stack">
    <style>
        .calc-modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 70;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(4px);
        }
        .calc-modal-backdrop[hidden] {
            display: none !important;
        }
        .calc-modal {
            width: min(760px, 100%);
            max-height: calc(100vh - 48px);
            overflow: auto;
            border-radius: 24px;
            background: var(--panel);
            border: 1px solid rgba(217, 226, 242, 0.85);
            box-shadow: var(--shadow);
            padding: 24px;
        }
        .calc-modal-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 20px;
        }
        .calc-modal-close {
            width: auto;
            min-width: 40px;
            padding: 8px 10px;
        }
        .calc-tabs {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }
        .calc-tab {
            width: auto;
            padding: 10px 14px;
        }
        .calc-tab.active {
            background: var(--accent);
            color: #fff;
            border-color: var(--accent);
        }
        .calc-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(280px, 0.8fr);
            gap: 16px;
        }
        .calc-card {
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 16px;
            background: linear-gradient(180deg, #fff 0%, #f8fbff 100%);
        }
        .calc-display {
            width: 100%;
            border-radius: 14px;
            border: 1px solid var(--line);
            background: #fff;
            padding: 14px 16px;
            font-size: 22px;
            font-weight: 800;
            text-align: right;
            margin-bottom: 12px;
        }
        .calc-subdisplay {
            font-size: 12px;
            color: var(--muted);
            text-align: right;
            margin-bottom: 12px;
            min-height: 18px;
        }
        .calc-keys {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
        }
        .calc-key {
            width: 100%;
            min-height: 48px;
            border-radius: 14px;
            border: 1px solid var(--line);
            background: #fff;
            font-weight: 700;
            cursor: pointer;
        }
        .calc-key.operator {
            background: var(--accent-weak);
            border-color: rgba(29, 78, 216, 0.16);
            color: var(--accent);
        }
        .calc-key.equal {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff;
        }
        .calc-field {
            margin-bottom: 14px;
        }
        .calc-result {
            border-radius: 16px;
            border: 1px solid var(--line);
            background: #fff;
            padding: 16px;
            min-height: 104px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 6px;
        }
        .calc-result .label {
            font-size: 13px;
            color: var(--muted);
        }
        .calc-result .value {
            font-size: 26px;
            font-weight: 800;
        }
        .scan-row {
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }
        .scan-row .field-wrap {
            flex: 1 1 auto;
        }
        .scan-camera-button {
            width: auto;
            min-width: 54px;
            padding: 12px 14px;
            display: none;
        }
        .scan-camera-button svg {
            width: 18px;
            height: 18px;
        }
        .barcode-scanner-backdrop {
            position: fixed;
            inset: 0;
            z-index: 80;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 18px;
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(4px);
        }
        .barcode-scanner-backdrop[hidden] {
            display: none !important;
        }
        .barcode-scanner-modal {
            width: min(560px, 100%);
            border-radius: 24px;
            background: var(--panel);
            border: 1px solid rgba(217, 226, 242, 0.85);
            box-shadow: var(--shadow);
            padding: 18px;
        }
        .barcode-scanner-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 14px;
        }
        .barcode-scanner-close {
            width: auto;
            min-width: 40px;
            padding: 8px 10px;
        }
        .barcode-scanner-video-wrap {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            background: #0f172a;
            aspect-ratio: 3 / 4;
            margin-bottom: 12px;
        }
        .barcode-scanner-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .barcode-scanner-target {
            position: absolute;
            inset: 14% 12%;
            border: 2px solid rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 0 0 9999px rgba(15, 23, 42, 0.22);
            pointer-events: none;
        }
        .barcode-scanner-status {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
            padding: 12px 14px;
            border-radius: 16px;
            background: #f8fbff;
            border: 1px solid var(--line);
            color: var(--muted);
            font-size: 14px;
        }
        form>div {
            margin-bottom: 0;
        }
        form .muted {
            color: var(--muted);
        }

        @media (max-width: 800px) {
            .calc-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 700px) {
            .calc-modal {
                padding: 18px;
                border-radius: 20px;
            }
        }

        @media (max-width: 768px) {
            .scan-row {
                align-items: stretch;
            }
            .scan-camera-button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                flex: 0 0 auto;
            }
        }
    </style>

    <div class="toolbar">
        <div>
            <h1 style="margin:0 0 6px;">Penjualan Kasir</h1>
            <div class="muted">Scan barcode. Transaksi akan tersimpan otomatis setiap scan.</div>
        </div>
        <div class="actions">
            <button class="button secondary" type="button" data-open-calculator="standard">📟 Kalkulator Biasa</button>
            <button class="button secondary" type="button" data-open-calculator="admin">📟 Kalkulator Admin</button>
        </div>
    </div>

    <div class="panel" style="box-shadow:none;">
        <form class="stack" wire:submit.prevent="addByBarcode">
            <div class="scan-row">
                <div class="field-wrap">
                <label for="barcode">Scan Barcode</label>
                <input
                    id="barcode"
                    class="field"
                    type="text"
                    wire:model.live="barcode"
                    autocomplete="off"
                    inputmode="text"
                    enterkeyhint="go"
                    placeholder="Scan atau ketik barcode lalu tekan Enter"
                    autofocus
                >
                @error('barcode')
                    <div class="muted">{{ $message }}</div>
                @enderror
                </div>

                <button
                    type="button"
                    class="button secondary scan-camera-button"
                    id="open-barcode-scanner"
                    aria-label="Scan barcode dengan kamera"
                    title="Scan barcode dengan kamera"
                >
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M4 7a2 2 0 0 1 2-2h2l1.2-1.8A2 2 0 0 1 10.86 2h2.28a2 2 0 0 1 1.66 1.2L16 5h2a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M9 13.5A3 3 0 1 1 15 13.5a3 3 0 0 1-6 0Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>

            <div class="muted" style="margin-top:8px;">Scan 1x = qty 1, scan lagi produk yang sama = qty bertambah.</div>
        </form>
    </div>

    <div class="panel" style="box-shadow:none;">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" />
        <h3 style="margin-top:0;"></h3>
        <div class="table-wrap">
            <table id="sale-entry-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Qty</th>
                        <th>HPP</th>
                        <th>Price</th>
                        <th>Income</th>
                        <th>HPP All</th>
                        <th>Margin</th>
                        <th>%</th>
                        <th>+</th>
                        <th>-</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $index => $item)
                        @php
                            $itemIncome = $item['qty'] * $item['sell_price'];
                            $itemHppAll = $item['qty'] * $item['hpp_snapshot'];
                            $itemMargin = $itemIncome - $itemHppAll;
                            $itemMarginPercent = $itemHppAll > 0 ? ($itemMargin / $itemHppAll) * 100 : 0;
                        @endphp
                        <tr>
                            <td>{{ $item['product_name_snapshot'] }}</td>
                            <td style="min-width: 72px; font-weight: 700;">{{ $item['qty'] }}</td>
                            <td>{{ number_format($item['hpp_snapshot'], 2, ',', '.') }}</td>
                            <td>{{ number_format($item['sell_price'], 2, ',', '.') }}</td>
                            <td>{{ number_format($itemIncome, 2, ',', '.') }}</td>
                            <td>{{ number_format($itemHppAll, 2, ',', '.') }}</td>
                            <td>{{ number_format($itemMargin, 2, ',', '.') }}</td>
                            <td>{{ number_format($itemMarginPercent, 2, ',', '.') }}%</td>
                            <td>
                                @if (in_array(auth()->user()?->role, ['owner', 'admin'], true))
                                    <button type="button" class="button small" wire:click="incrementItem({{ $index }})">+</button>
                                @endif
                            </td>
                            <td>
                                @if (in_array(auth()->user()?->role, ['owner', 'admin'], true))
                                    <button type="button" class="button small" wire:click="decrementItem({{ $index }})">-</button>
                                @endif
                            </td>
                            <td>
                                @if (in_array(auth()->user()?->role, ['owner', 'admin'], true))
                                    <button type="button" class="button danger icon delete-item-button" data-item-index="{{ $index }}" title="Hapus">🗑</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="muted">Keranjang masih kosong.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div wire:ignore>
        <div id="sale-calculator-modal" class="calc-modal-backdrop" hidden>
            <div class="calc-modal">
                <div class="calc-modal-head">
                    <div>
                        <h3 id="sale-calculator-title" style="margin:0 0 6px;">Kalkulator Biasa</h3>
                        <div class="muted" id="sale-calculator-subtitle">Hitung cepat tanpa meninggalkan halaman penjualan.</div>
                    </div>
                    <button class="button secondary icon calc-modal-close" type="button" data-close-calculator aria-label="Tutup kalkulator">x</button>
                </div>

                <div class="calc-tabs">
                    <button class="button secondary calc-tab active" type="button" data-calculator-tab="standard">Biasa</button>
                    <button class="button secondary calc-tab" type="button" data-calculator-tab="admin">Harga - Admin</button>
                </div>

                <div class="calc-grid">
                    <div class="calc-card" data-calculator-panel="standard">
                        <div class="calc-subdisplay" id="standard-calc-expression"></div>
                        <input class="calc-display" id="standard-calc-display" type="text" value="0" readonly>
                        <div class="calc-keys">
                            <button class="calc-key operator" type="button" data-calc-action="clear">C</button>
                            <button class="calc-key operator" type="button" data-calc-action="backspace">⌫</button>
                            <button class="calc-key operator" type="button" data-calc-action="operator" data-calc-value="%">%</button>
                            <button class="calc-key operator" type="button" data-calc-action="operator" data-calc-value="/">÷</button>
                            <button class="calc-key" type="button" data-calc-action="digit" data-calc-value="7">7</button>
                            <button class="calc-key" type="button" data-calc-action="digit" data-calc-value="8">8</button>
                            <button class="calc-key" type="button" data-calc-action="digit" data-calc-value="9">9</button>
                            <button class="calc-key operator" type="button" data-calc-action="operator" data-calc-value="*">×</button>
                            <button class="calc-key" type="button" data-calc-action="digit" data-calc-value="4">4</button>
                            <button class="calc-key" type="button" data-calc-action="digit" data-calc-value="5">5</button>
                            <button class="calc-key" type="button" data-calc-action="digit" data-calc-value="6">6</button>
                            <button class="calc-key operator" type="button" data-calc-action="operator" data-calc-value="-">−</button>
                            <button class="calc-key" type="button" data-calc-action="digit" data-calc-value="1">1</button>
                            <button class="calc-key" type="button" data-calc-action="digit" data-calc-value="2">2</button>
                            <button class="calc-key" type="button" data-calc-action="digit" data-calc-value="3">3</button>
                            <button class="calc-key operator" type="button" data-calc-action="operator" data-calc-value="+">+</button>
                            <button class="calc-key" type="button" data-calc-action="digit" data-calc-value="0">0</button>
                            <button class="calc-key" type="button" data-calc-action="dot">.</button>
                            <button class="calc-key operator" type="button" data-calc-action="operator" data-calc-value="(">(</button>
                            <button class="calc-key operator" type="button" data-calc-action="operator" data-calc-value=")">)</button>
                            <button class="calc-key equal" type="button" data-calc-action="equal" style="grid-column: span 4;">=</button>
                        </div>
                    </div>

                    <div class="calc-card" data-calculator-panel="admin" hidden>
                        <div class="calc-field">
                            <label for="adminCalcPrice">Harga</label>
                            <input id="adminCalcPrice" class="field" type="text" inputmode="decimal" autocomplete="off">
                        </div>
                        <div class="calc-field">
                            <label for="adminCalcPercent">Admin (%)</label>
                            <input id="adminCalcPercent" class="field" type="number" min="0" step="0.01" inputmode="decimal">
                        </div>
                        <div class="calc-result">
                            <div class="label">Hasil</div>
                            <div class="value" id="adminCalcResult">0</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div wire:ignore>
        <div id="barcode-scanner-modal" class="barcode-scanner-backdrop" hidden>
            <div class="barcode-scanner-modal">
                <div class="barcode-scanner-head">
                    <div>
                        <h3 style="margin:0 0 6px;">Scan Barcode Kamera</h3>
                        <div class="muted">Arahkan kamera ke barcode produk.</div>
                    </div>
                    <button class="button secondary icon barcode-scanner-close" type="button" id="close-barcode-scanner" aria-label="Tutup scanner">x</button>
                </div>

                <div class="barcode-scanner-video-wrap">
                    <video id="barcode-scanner-video" class="barcode-scanner-video" playsinline muted></video>
                    <div class="barcode-scanner-target"></div>
                </div>

                <div class="barcode-scanner-status">
                    <span id="barcode-scanner-status-text">Menunggu kamera...</span>
                    <button class="button secondary" type="button" id="barcode-scanner-toggle-flash" hidden>Flash</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest"></script>
    <script>
        const initSaleEntryTable = () => {
            const table = document.querySelector('#sale-entry-table');
            if (!table || typeof simpleDatatables === 'undefined') {
                return;
            }

            destroySaleEntryTable();
            window.saleEntryDataTable = new simpleDatatables.DataTable(table, {
                searchable: true,
                fixedHeight: true,
                perPage: 10,
                perPageSelect: [5, 10, 20, 50, 100, ["ALL", -1]],
                // labels: {
                //     placeholder: 'Cari...',
                //     perPage: '{select} baris per halaman',
                //     noRows: 'Tidak ada data yang cocok',
                //     info: 'Menampilkan {start} sampai {end} dari {rows} baris',
                // },
            });
        };

        const destroySaleEntryTable = () => {
            if (!window.saleEntryDataTable) {
                return;
            }

            try {
                window.saleEntryDataTable.destroy();
            } catch (error) {
                console.warn('Failed to destroy existing DataTable:', error);
            }

            window.saleEntryDataTable = null;
        };

        const debounceInitSaleEntryTable = (() => {
            let timeoutId = null;

            return () => {
                if (timeoutId) {
                    clearTimeout(timeoutId);
                }

                timeoutId = window.setTimeout(() => {
                    requestAnimationFrame(initSaleEntryTable);
                    timeoutId = null;
                }, 50);
            };
        })();

        const getSaleEntryComponent = () => {
            const root = document.querySelector('[wire\\:id]');
            const componentId = root ? root.getAttribute('wire:id') : null;

            if (!componentId || !window.Livewire) {
                return null;
            }

            return window.Livewire.find(componentId);
        };

        const bindSaleCalculator = () => {
            if (window.saleCalculatorBound) {
                return;
            }

            const modal = document.getElementById('sale-calculator-modal');
            const title = document.getElementById('sale-calculator-title');
            const subtitle = document.getElementById('sale-calculator-subtitle');
            const standardPanel = document.querySelector('[data-calculator-panel="standard"]');
            const adminPanel = document.querySelector('[data-calculator-panel="admin"]');
            const standardDisplay = document.getElementById('standard-calc-display');
            const standardExpression = document.getElementById('standard-calc-expression');
            const adminPriceInput = document.getElementById('adminCalcPrice');
            const adminPercentInput = document.getElementById('adminCalcPercent');
            const adminResult = document.getElementById('adminCalcResult');

            if (!modal || !title || !subtitle || !standardPanel || !adminPanel || !standardDisplay || !standardExpression || !adminPriceInput || !adminPercentInput || !adminResult) {
                return;
            }

            const state = {
                activeTab: 'standard',
                expression: '',
            };

            const formatStandard = (value) => {
                const number = Number(value || 0);
                return new Intl.NumberFormat('en-US', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 2,
                }).format(Number.isFinite(number) ? number : 0);
            };

            const formatAdmin = (value) => {
                const number = Number(value || 0);
                return new Intl.NumberFormat('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                }).format(Number.isFinite(number) ? number : 0);
            };

            const parseFormattedNumber = (value) => {
                const normalized = String(value || '').replace(/,/g, '');
                const parsed = Number(normalized);
                return Number.isFinite(parsed) ? parsed : 0;
            };

            const formatAdminInput = (value) => {
                const normalized = String(value || '').replace(/,/g, '');
                if (normalized === '' || normalized === '.') {
                    return normalized;
                }

                const parts = normalized.split('.');
                const integerPart = parts[0].replace(/\D/g, '');
                const decimalPart = parts[1] !== undefined ? parts[1].replace(/\D/g, '') : '';
                const formattedInteger = integerPart === '' ? '0' : integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');

                return decimalPart === '' ? formattedInteger : `${formattedInteger}.${decimalPart}`;
            };

            const renderStandard = () => {
                standardExpression.textContent = state.expression || '0';

                if (!state.expression) {
                    standardDisplay.value = '0';
                    return;
                }

                const preview = evaluateExpression(state.expression);
                standardDisplay.value = preview === null ? '0' : formatStandard(preview);
            };

            const renderAdmin = () => {
                const price = parseFormattedNumber(adminPriceInput.value);
                const percent = Number(adminPercentInput.value || 0);
                const result = price - (price * percent / 100);
                adminResult.textContent = formatAdmin(result);
            };

            const evaluateExpression = (expression) => {
                if (!/^[0-9+\-*/().\s%]+$/.test(expression)) {
                    return null;
                }

                try {
                    const normalized = expression.replace(/%/g, '/100');
                    const result = Function(`"use strict"; return (${normalized});`)();
                    return Number.isFinite(result) ? result : null;
                } catch (error) {
                    return null;
                }
            };

            const setTab = (tab) => {
                state.activeTab = tab;
                title.textContent = tab === 'admin' ? 'Kalkulator Harga - Admin' : 'Kalkulator Biasa';
                subtitle.textContent = tab === 'admin'
                    ? 'Hitung harga jual dengan pengurangan persen admin.'
                    : 'Hitung cepat tanpa meninggalkan halaman penjualan.';

                standardPanel.hidden = tab !== 'standard';
                adminPanel.hidden = tab !== 'admin';

                document.querySelectorAll('[data-calculator-tab]').forEach((button) => {
                    button.classList.toggle('active', button.dataset.calculatorTab === tab);
                });
            };

            const openModal = (tab) => {
                modal.hidden = false;
                setTab(tab || 'standard');
                if (tab === 'admin') {
                    adminPriceInput.focus();
                } else {
                    standardDisplay.focus();
                }
            };

            const closeModal = () => {
                modal.hidden = true;
            };

            const pushExpression = (value) => {
                state.expression += value;
                renderStandard();
            };

            document.addEventListener('click', (event) => {
                const openButton = event.target.closest('[data-open-calculator]');
                if (openButton) {
                    event.preventDefault();
                    openModal(openButton.dataset.openCalculator);
                    return;
                }

                const closeButton = event.target.closest('[data-close-calculator]');
                if (closeButton) {
                    event.preventDefault();
                    closeModal();
                    return;
                }

                const tabButton = event.target.closest('[data-calculator-tab]');
                if (tabButton) {
                    event.preventDefault();
                    setTab(tabButton.dataset.calculatorTab);
                    return;
                }

                const keyButton = event.target.closest('[data-calc-action]');
                if (!keyButton || modal.hidden || state.activeTab !== 'standard') {
                    return;
                }

                event.preventDefault();
                const action = keyButton.dataset.calcAction;
                const value = keyButton.dataset.calcValue || '';

                if (action === 'digit' || action === 'operator') {
                    pushExpression(value);
                    return;
                }

                if (action === 'dot') {
                    pushExpression('.');
                    return;
                }

                if (action === 'clear') {
                    state.expression = '';
                    renderStandard();
                    return;
                }

                if (action === 'backspace') {
                    state.expression = state.expression.slice(0, -1);
                    renderStandard();
                    return;
                }

                if (action === 'equal') {
                    const result = evaluateExpression(state.expression);
                    if (result !== null) {
                        state.expression = String(result);
                    }
                    renderStandard();
                }
            });

            adminPriceInput.addEventListener('input', () => {
                adminPriceInput.value = formatAdminInput(adminPriceInput.value);
                renderAdmin();
                adminPriceInput.setSelectionRange(adminPriceInput.value.length, adminPriceInput.value.length);
            });
            adminPercentInput.addEventListener('input', renderAdmin);
            window.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !modal.hidden) {
                    closeModal();
                }
            });

            renderStandard();
            renderAdmin();
            window.saleCalculatorBound = true;
        };

        const bindBarcodeScanner = () => {
            if (window.barcodeScannerBound) {
                return;
            }

            const openButton = document.getElementById('open-barcode-scanner');
            const modal = document.getElementById('barcode-scanner-modal');
            const closeButton = document.getElementById('close-barcode-scanner');
            const video = document.getElementById('barcode-scanner-video');
            const statusText = document.getElementById('barcode-scanner-status-text');
            const toggleFlashButton = document.getElementById('barcode-scanner-toggle-flash');
            const barcodeInput = document.getElementById('barcode');

            if (!openButton || !modal || !closeButton || !video || !statusText || !toggleFlashButton || !barcodeInput) {
                return;
            }

            const state = {
                stream: null,
                detector: null,
                active: false,
                rafId: null,
                lastValue: '',
                torchEnabled: false,
                hasTorch: false,
            };

            const setStatus = (message) => {
                statusText.textContent = message;
            };

            const stopScanner = () => {
                state.active = false;

                if (state.rafId) {
                    cancelAnimationFrame(state.rafId);
                    state.rafId = null;
                }

                if (video.srcObject) {
                    video.srcObject.getTracks().forEach((track) => track.stop());
                    video.srcObject = null;
                }

                state.stream = null;
                state.detector = null;
                modal.hidden = true;
            };

            const applyTorch = async () => {
                if (!state.stream || !state.hasTorch) {
                    return;
                }

                const track = state.stream.getVideoTracks()[0];
                const capabilities = track.getCapabilities ? track.getCapabilities() : {};

                if (!capabilities.torch) {
                    state.hasTorch = false;
                    toggleFlashButton.hidden = true;
                    return;
                }

                state.torchEnabled = !state.torchEnabled;
                await track.applyConstraints({
                    advanced: [{ torch: state.torchEnabled }],
                });
                toggleFlashButton.textContent = state.torchEnabled ? 'Flash On' : 'Flash';
            };

            const handleBarcode = async (value) => {
                if (!value || value === state.lastValue) {
                    return;
                }

                state.lastValue = value;
                barcodeInput.value = value;
                barcodeInput.dispatchEvent(new Event('input', { bubbles: true }));

                try {
                    const component = getSaleEntryComponent();
                    if (component) {
                        await component.set('barcode', value);
                        await component.call('addByBarcode');
                    } else {
                        barcodeInput.form?.requestSubmit();
                    }
                } finally {
                    stopScanner();
                }
            };

            const scanFrame = async () => {
                if (!state.active || !state.detector) {
                    return;
                }

                try {
                    const barcodes = await state.detector.detect(video);
                    if (barcodes.length > 0) {
                        await handleBarcode(barcodes[0].rawValue);
                        return;
                    }
                } catch (error) {
                    console.warn('Barcode detection failed:', error);
                }

                state.rafId = requestAnimationFrame(scanFrame);
            };

            const openScanner = async () => {
                modal.hidden = false;
                state.lastValue = '';
                state.torchEnabled = false;
                toggleFlashButton.hidden = true;
                toggleFlashButton.textContent = 'Flash';
                setStatus('Meminta akses kamera...');

                if (!navigator.mediaDevices?.getUserMedia) {
                    setStatus('Browser tidak mendukung kamera.');
                    return;
                }

                try {
                    state.stream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: { ideal: 'environment' },
                        },
                        audio: false,
                    });
                } catch (error) {
                    setStatus('Kamera tidak bisa dibuka. Pastikan izin kamera aktif.');
                    return;
                }

                video.srcObject = state.stream;

                try {
                    await video.play();
                } catch (error) {
                    setStatus('Video kamera gagal diputar.');
                    return;
                }

                if ('BarcodeDetector' in window) {
                    try {
                        state.detector = new BarcodeDetector({
                            formats: ['ean_13', 'ean_8', 'code_128', 'code_39', 'code_93', 'codabar', 'upc_a', 'upc_e', 'qr_code'],
                        });
                        setStatus('Arahkan barcode ke dalam frame.');
                    } catch (error) {
                        state.detector = null;
                        setStatus('Barcode detector tidak tersedia di browser ini.');
                    }
                } else {
                    state.detector = null;
                    setStatus('Browser ini belum mendukung Barcode Detector.');
                }

                const track = state.stream.getVideoTracks()[0];
                const capabilities = track.getCapabilities ? track.getCapabilities() : {};
                if (capabilities.torch) {
                    state.hasTorch = true;
                    toggleFlashButton.hidden = false;
                }

                state.active = true;
                scanFrame();
            };

            openButton.addEventListener('click', openScanner);
            closeButton.addEventListener('click', stopScanner);
            toggleFlashButton.addEventListener('click', applyTorch);
            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    stopScanner();
                }
            });

            window.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !modal.hidden) {
                    stopScanner();
                }
            });

            window.addEventListener('beforeunload', stopScanner);
            window.barcodeScannerBound = true;
        };

        const bindSaleEntryLivewire = () => {
            if (window.saleEntryLivewireBound) {
                return;
            }

            initSaleEntryTable();
            document.addEventListener('livewire:load', () => debounceInitSaleEntryTable());
            if (window.Livewire && typeof window.Livewire.hook === 'function') {
                window.Livewire.hook('morph.updated', () => debounceInitSaleEntryTable());
                window.Livewire.hook('morphed', () => debounceInitSaleEntryTable());
            }
            window.saleEntryLivewireBound = true;
        };

        const bindSaleEntryCustomEvents = () => {
            if (window.saleEntryCustomEventsBound) {
                return;
            }

            window.addEventListener('draft-updated', (event) => {
                const state = event.detail?.state || {};
                localStorage.setItem('sale-entry-draft', JSON.stringify(state));
            });

            window.addEventListener('draft-cleared', () => {
                localStorage.removeItem('sale-entry-draft');
            });

            window.addEventListener('sale-entry-table-updated', () => {
                debounceInitSaleEntryTable();
            });

            document.addEventListener('click', async (event) => {
                const button = event.target.closest('.delete-item-button');
                if (!button) {
                    return;
                }

                event.preventDefault();
                const itemIndex = button.dataset.itemIndex;
                const component = getSaleEntryComponent();

                if (itemIndex === undefined || itemIndex === null || !component) {
                    return;
                }

                const result = await Swal.fire({
                    title: 'Hapus item?',
                    text: 'Item akan dihapus dari keranjang.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                });

                if (result.isConfirmed) {
                    component.call('removeItem', Number(itemIndex));
                }
            });

            window.addEventListener('scan-error', async (event) => {
                const message = event.detail?.message || 'Barcode tidak ditemukan.';
                // alert(message);
                await Swal.fire({
                    title: 'Barcode tidak ditemukan.',
                    text: message,
                    icon: 'warning',
                    confirmButtonText: 'OK',
                });

                const barcodeInput = document.getElementById('barcode');
                if (barcodeInput) {
                    barcodeInput.focus();
                    barcodeInput.select();
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

                const barcodeInput = document.getElementById('barcode');
                if (barcodeInput) {
                    barcodeInput.focus();
                    barcodeInput.select();
                }
            });

            window.addEventListener('scan-success', () => {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Scan berhasil',
                    showConfirmButton: false,
                    timer: 1400,
                    timerProgressBar: true,
                    background: '#ffffff',
                });

                const barcodeInput = document.getElementById('barcode');
                if (barcodeInput) {
                    barcodeInput.focus();
                    barcodeInput.select();
                }

                const audioContext = window.__scanAudioContext || new (window.AudioContext || window.webkitAudioContext)();
                window.__scanAudioContext = audioContext;

                if (audioContext.state === 'suspended') {
                    audioContext.resume();
                }

                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();
                oscillator.type = 'sine';
                oscillator.frequency.value = 880;
                gainNode.gain.value = 0.03;
                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);
                oscillator.start();
                oscillator.stop(audioContext.currentTime + 0.08);
            });

            window.saleEntryCustomEventsBound = true;
        };

        document.addEventListener('DOMContentLoaded', () => {
            initSaleEntryTable();
            bindSaleCalculator();
            bindBarcodeScanner();
            bindSaleEntryLivewire();
            bindSaleEntryCustomEvents();
        });

        document.addEventListener('livewire:init', () => {
            initSaleEntryTable();
            bindSaleCalculator();
            bindBarcodeScanner();
            bindSaleEntryLivewire();
            const root = document.querySelector('[wire\\:id]');
            const componentId = root ? root.getAttribute('wire:id') : null;
            const draftKey = 'sale-entry-draft';

            if (componentId && window.Livewire) {
                const component = window.Livewire.find(componentId);

                if (component) {
                    const draft = localStorage.getItem(draftKey);

                    if (draft) {
                        try {
                            const state = JSON.parse(draft);

                            if (state.saleId !== undefined) {
                                component.set('saleId', state.saleId);
                            }

                            if (state.barcode !== undefined) {
                                component.set('barcode', state.barcode);
                            }

                            if (Array.isArray(state.items)) {
                                component.set('items', state.items);
                            }
                        } catch (error) {
                            localStorage.removeItem(draftKey);
                        }
                    }
                }
            }

            bindSaleEntryCustomEvents();
        });
    </script>
</div>
