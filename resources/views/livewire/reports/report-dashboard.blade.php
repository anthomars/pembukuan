<div class="stack">
    <div class="toolbar">
        <div>
            <h1 style="margin:0 0 6px;">Laporan Penjualan</h1>
            <div class="muted">Ringkasan harian dan bulanan untuk income, HPP, margin, dan pengeluaran.</div>
        </div>
        <div class="actions">
            <div>
                <label for="reportDate">Tanggal</label>
                <input id="reportDate" class="field" type="date" wire:model.live="reportDate">
            </div>
            <div>
                <label for="reportMonth">Bulan</label>
                <input id="reportMonth" class="field" type="month" wire:model.live="reportMonth">
            </div>
            <div style="display:flex;align-items:end;">
                <a class="button success" style="display:inline-flex; align-items:center; gap:8px; width:auto;" href="/laporan/export?type=daily&reportDate={{ $reportDate }}">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" style="width:14px;height:14px;flex:0 0 auto;">
                        <path d="M7 3h7l5 5v13H7V3Z" fill="currentColor" opacity=".18" />
                        <path d="M14 3v5h5" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
                        <path d="M8.5 8.5h7v11h-7z" fill="#ffffff" opacity=".92" />
                        <path d="M9.5 10l2.1 2.7L9.4 15.5h1.9l1.2-1.7 1.2 1.7h1.9l-2.2-2.8 2.1-2.7h-1.9l-1.1 1.6-1.1-1.6H9.5Z" fill="#16a34a" />
                        <path d="M7 3h7l5 5v13H7V3Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
                    </svg>
                    <span>Export Harian</span>
                </a>
            </div>
            <div style="display:flex;align-items:end;">
                <a class="button success" style="display:inline-flex; align-items:center; gap:8px; width:auto;" href="/laporan/export?type=monthly&reportMonth={{ $reportMonth }}">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" style="width:14px;height:14px;flex:0 0 auto;">
                        <path d="M7 3h7l5 5v13H7V3Z" fill="currentColor" opacity=".18" />
                        <path d="M14 3v5h5" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
                        <path d="M8.5 8.5h7v11h-7z" fill="#ffffff" opacity=".92" />
                        <path d="M9.5 10l2.1 2.7L9.4 15.5h1.9l1.2-1.7 1.2 1.7h1.9l-2.2-2.8 2.1-2.7h-1.9l-1.1 1.6-1.1-1.6H9.5Z" fill="#16a34a" />
                        <path d="M7 3h7l5 5v13H7V3Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
                    </svg>
                    <span>Export Bulanan</span>
                </a>
            </div>
        </div>
    </div>

    <div class="stack">
        <div class="badge">Laporan Harian</div>
        <div class="summary">
            <div class="card"><div class="label">Income</div><div class="value">{{ number_format($dailyIncome, 2, ',', '.') }}</div></div>
            <div class="card"><div class="label">HPP ALL</div><div class="value">{{ number_format($dailyHpp, 2, ',', '.') }}</div></div>
            {{-- <div class="card"><div class="label">Gross Profit</div><div class="value">{{ number_format($dailyGrossProfit, 2, ',', '.') }}</div></div>
            <div class="card"><div class="label">Net Profit</div><div class="value">{{ number_format($dailyNetProfit, 2, ',', '.') }}</div></div> --}}
        </div>
        <div class="summary">
            <div class="card"><div class="label">Pengeluaran</div><div class="value">{{ number_format($dailyExpense, 2, ',', '.') }}</div></div>
            {{-- <div class="card"><div class="label">Margin</div><div class="value">{{ number_format($dailyMargin, 2, ',', '.') }}</div></div> --}}
            <div class="card"><div class="label">Margin</div><div class="value">{{ number_format($dailyNetProfit, 2, ',', '.') }}</div></div>
            {{-- <div class="card"><div class="label">Transaksi</div><div class="value">{{ $dailyTransactions->count() }}</div></div> --}}
            {{-- <div class="card"><div class="label">Produk Laku</div><div class="value">{{ $dailyTopProducts->count() }}</div></div> --}}
        </div>

        {{-- <div class="panel" style="box-shadow:none;">
            <h3 style="margin-top:0;">Transaksi Hari Ini</h3>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Total</th>
                            <th>HPP</th>
                            <th>Gross Profit</th>
                            <th>Margin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dailyTransactions as $transaction)
                            <tr>
                                <td>{{ $transaction['sold_at']->format('H:i') }}</td>
                                <td>{{ number_format($transaction['total'], 2, ',', '.') }}</td>
                                <td>{{ number_format($transaction['hpp'], 2, ',', '.') }}</td>
                                <td>{{ number_format($transaction['gross_profit'], 2, ',', '.') }}</td>
                                <td>{{ number_format($transaction['margin'], 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="muted">Belum ada transaksi di tanggal ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="panel" style="box-shadow:none;">
            <h3 style="margin-top:0;">Top Produk Hari Ini</h3>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Qty Terjual</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dailyTopProducts as $product)
                            <tr>
                                <td>{{ $product->product_name_snapshot }}</td>
                                <td>{{ $product->total_qty }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="muted">Belum ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div> --}}
    </div>

    <div class="stack">
        <div class="badge">Laporan Bulanan</div>
        <div class="summary">
            <div class="card"><div class="label">Income</div><div class="value">{{ number_format($monthlyIncome, 2, ',', '.') }}</div></div>
            <div class="card"><div class="label">HPP</div><div class="value">{{ number_format($monthlyHpp, 2, ',', '.') }}</div></div>
            {{-- <div class="card"><div class="label">Gross Profit</div><div class="value">{{ number_format($monthlyGrossProfit, 2, ',', '.') }}</div></div>
            <div class="card"><div class="label">Net Profit</div><div class="value">{{ number_format($monthlyNetProfit, 2, ',', '.') }}</div></div> --}}
        </div>
        <div class="summary">
            <div class="card"><div class="label">Pengeluaran</div><div class="value">{{ number_format($monthlyExpense, 2, ',', '.') }}</div></div>
            {{-- <div class="card"><div class="label">Margin</div><div class="value">{{ number_format($monthlyMargin, 2, ',', '.') }}</div></div> --}}
            <div class="card"><div class="label">Margin</div><div class="value">{{ number_format($monthlyNetProfit, 2, ',', '.') }}</div></div>
            {{-- <div class="card"><div class="label">Periode</div><div class="value">{{ \Carbon\Carbon::parse($reportMonth . '-01')->format('F Y') }}</div></div> --}}
            {{-- <div class="card"><div class="label">Status</div><div class="value">Aktif</div></div> --}}
        </div>
    </div>
</div>
