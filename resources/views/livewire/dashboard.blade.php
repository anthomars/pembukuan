<div class="stack">
    <div class="toolbar">
        <div>
            <h1 style="margin:0 0 6px;">Dashboard Hari Ini</h1>
            <div class="muted">Ringkasan cepat untuk income, HPP, margin, pengeluaran</div>
        </div>
        <div class="actions">
            <a class="button secondary" href="/penjualan" style="display:inline-flex;align-items:center;">+ Penjualan</a>
            <a class="button secondary" href="/produk" style="display:inline-flex;align-items:center;">Produk</a>
            <a class="button secondary" href="/pengeluaran" style="display:inline-flex;align-items:center;">Pengeluaran</a>
            <a class="button secondary" href="/laporan" style="display:inline-flex;align-items:center;">Laporan</a>
        </div>
    </div>

    <div class="summary">
        <div class="card">
            <div class="label">Income Hari Ini</div>
            <div class="value">{{ number_format($todayIncome, 2, ',', '.') }}</div>
        </div>
        <div class="card">
            <div class="label">HPP Hari Ini</div>
            <div class="value">{{ number_format($todayHpp, 2, ',', '.') }}</div>
        </div>
        {{-- <div class="card">
            <div class="label">Gross Profit</div>
            <div class="value">{{ number_format($todayGrossProfit, 2, ',', '.') }}</div>
        </div>
        <div class="card">
            <div class="label">Net Profit</div>
            <div class="value">{{ number_format($todayNetProfit, 2, ',', '.') }}</div>
        </div> --}}
    </div>

    <div class="summary">
        <div class="card">
            <div class="label">Pengeluaran</div>
            <div class="value">{{ number_format($todayExpense, 2, ',', '.') }}</div>
        </div>
        <div class="card">
            <div class="label">Margin</div>
            {{-- <div class="value">{{ number_format($todayMargin, 2, ',', '.') }}</div> --}}
            <div class="value">{{ number_format($todayNetProfit, 2, ',', '.') }}</div>
        </div>
        {{-- <div class="card">
            <div class="label">Transaksi</div>
            <div class="value">{{ $todaySalesCount }}</div>
        </div>
        <div class="card">
            <div class="label">Tanggal</div>
            <div class="value">{{ now()->format('d/m/Y') }}</div>
        </div> --}}
    </div>

    <div class="grid">
        {{-- <div class="panel" style="box-shadow:none;">
            <h3 style="margin-top:0;">Transaksi Terbaru</h3>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Total</th>
                            <th>HPP</th>
                            <th>Gross Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($todaySales as $sale)
                            @php
                                $saleHpp = $sale->items->sum(fn ($item) => $item->qty * $item->hpp_snapshot);
                            @endphp
                            <tr>
                                <td>{{ $sale->sold_at->format('H:i') }}</td>
                                <td>{{ number_format($sale->total, 2, ',', '.') }}</td>
                                <td>{{ number_format($saleHpp, 2, ',', '.') }}</td>
                                <td>{{ number_format($sale->total - $saleHpp, 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="muted">Belum ada transaksi hari ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div> --}}

        <div class="panel" style="box-shadow:none;">
            <h3 style="margin-top:0;">Produk Terlaris</h3>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bestSellingProducts as $product)
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
        </div>
    </div>
</div>
