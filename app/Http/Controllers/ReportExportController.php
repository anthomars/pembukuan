<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Product;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReportExportController extends Controller
{
    /**
     * Download Excel-compatible report.
     * Supports query params:
     * - type: daily|monthly (default daily)
     * - reportDate: YYYY-MM-DD (for daily)
     * - reportMonth: YYYY-MM (for monthly)
     */
    public function download(Request $request): Response
    {
        $type = $request->query('type', 'daily');
        $reportDate = $request->query('reportDate', now()->toDateString());
        $reportMonth = $request->query('reportMonth', now()->format('Y-m'));

        if ($type === 'monthly') {
            $month = Carbon::parse($reportMonth . '-01');

            return $this->excelResponse(
                $this->monthlyHtml($month),
                'laporan-bulanan-' . $reportMonth . '.xls'
            );
        }

        $date = Carbon::parse($reportDate);

        return $this->excelResponse(
            $this->dailyHtml($date),
            'laporan-harian-' . $reportDate . '.xls'
        );
    }

    private function dailyHtml(Carbon $date): string
    {
        $products = Product::query()
            ->orderBy('name')
            ->get();

        $salesByProductId = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->whereDate('sales.sold_at', $date)
            ->groupBy('sale_items.product_id')
            ->selectRaw('sale_items.product_id, COALESCE(SUM(sale_items.qty), 0) as total_qty')
            ->pluck('total_qty', 'product_id');

        $expense = Expense::query()->whereDate('expense_date', $date)->sum('amount');
        $summaryTotals = SaleItem::query()
            ->whereHas('sale', fn ($query) => $query->whereDate('sold_at', $date))
            ->selectRaw('COALESCE(SUM(qty * sell_price), 0) as total_income')
            ->selectRaw('COALESCE(SUM(qty * hpp_snapshot), 0) as total_hpp')
            ->first();

        return $this->buildHtmlReport(
            heading: 'LAPORAN HARIAN',
            title: 'DATE: ' . $date->format('n/j/Y'),
            products: $products,
            salesByProductId: $salesByProductId,
            expense: (float) $expense,
            summaryIncome: (float) ($summaryTotals->total_income ?? 0),
            summaryHpp: (float) ($summaryTotals->total_hpp ?? 0)
        );
    }

    private function monthlyHtml(Carbon $month): string
    {
        $products = Product::query()
            ->orderBy('name')
            ->get();

        $salesByProductId = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->whereYear('sales.sold_at', $month->year)
            ->whereMonth('sales.sold_at', $month->month)
            ->groupBy('sale_items.product_id')
            ->selectRaw('sale_items.product_id, COALESCE(SUM(sale_items.qty), 0) as total_qty')
            ->pluck('total_qty', 'product_id');

        $expense = Expense::query()
            ->whereYear('expense_date', $month->year)
            ->whereMonth('expense_date', $month->month)
            ->sum('amount');
        $summaryTotals = SaleItem::query()
            ->whereHas('sale', fn ($query) => $query->whereYear('sold_at', $month->year)->whereMonth('sold_at', $month->month))
            ->selectRaw('COALESCE(SUM(qty * sell_price), 0) as total_income')
            ->selectRaw('COALESCE(SUM(qty * hpp_snapshot), 0) as total_hpp')
            ->first();

        return $this->buildHtmlReport(
            heading: 'LAPORAN BULANAN',
            title: 'MONTH: ' . $month->format('n/Y'),
            products: $products,
            salesByProductId: $salesByProductId,
            expense: (float) $expense,
            summaryIncome: (float) ($summaryTotals->total_income ?? 0),
            summaryHpp: (float) ($summaryTotals->total_hpp ?? 0)
        );
    }

    private function buildHtmlReport(iterable $products, $salesByProductId, float $expense, string $heading, string $title, ?float $summaryIncome = null, ?float $summaryHpp = null): string
    {
        $rows = '';
        $totalIncome = 0.0;
        $totalHpp = 0.0;

        foreach ($products as $product) {
            $qty = (int) ($salesByProductId[$product->id] ?? 0);
            $hpp = (float) $product->hpp;
            $price = (float) $product->sell_price;
            $income = $qty * $price;
            $hppAll = $qty * $hpp;
            $margin = $income - $hppAll;
            $marginPercent = $hppAll > 0 ? ($margin / $hppAll) * 100 : null;

            $totalIncome += $income;
            $totalHpp += $hppAll;

            $rows .= '<tr>';
            $rows .= '<td class="item">' . e($product->name) . '</td>';
            $rows .= '<td class="center">' . $qty . '</td>';
            $rows .= '<td class="currency">' . $this->formatNumber($hpp) . '</td>';
            $rows .= '<td class="currency">' . $this->formatNumber($price) . '</td>';
            $rows .= '<td class="currency">' . $this->formatAmount($income) . '</td>';
            $rows .= '<td class="currency">' . $this->formatAmount($hppAll) . '</td>';
            $rows .= '<td class="currency margin-cell">' . $this->formatAmount($margin) . '</td>';
            $rows .= '<td class="center">' . $this->formatPercent($marginPercent) . '</td>';
            $rows .= '</tr>';
        }

        $summaryIncome = $summaryIncome ?? $totalIncome;
        $summaryHpp = $summaryHpp ?? $totalHpp;
        $grossProfit = $summaryIncome - $summaryHpp;
        $netProfit = $grossProfit - $expense;
        $formattedIncome = $this->formatNumber($summaryIncome);
        $formattedGrossProfit = $this->formatNumber($grossProfit);
        $formattedExpense = $this->formatNumber($expense);
        $formattedNetProfit = $this->formatNumber($netProfit);

        return <<<HTML
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            color: #000;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        td, th {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: middle;
            line-height: 1.1;
        }

        .report-title {
            border: none;
            padding: 0 0 6px 0;
            font-size: 13px;
        }

        .report-heading {
            border: none;
            padding: 0 0 2px 0;
            font-size: 16px;
            font-weight: bold;
        }

        .header-black {
            background: #111;
            color: #fff;
            font-weight: bold;
            text-align: center;
        }

        .header-red {
            background: #ff1a1a;
            color: #fff;
            font-weight: bold;
            text-align: center;
        }

        .header-yellow {
            background: #fff200;
            color: #000;
            font-weight: bold;
            text-align: center;
        }

        .item {
            font-weight: bold;
            background: #f3f3f3;
        }

        .center {
            text-align: center;
        }

        .currency {
            text-align: right;
            white-space: nowrap;
            background: #fde9d9;
        }

        .margin-cell {
            background: #fff200;
        }

        .summary-label {
            background: #111;
            color: #fff;
            font-weight: bold;
            text-align: right;
        }

        .summary-value {
            background: #111;
            color: #fff;
            font-weight: bold;
            text-align: right;
        }

        .summary-yellow-label {
            background: #fff200;
            color: #000;
            font-weight: bold;
            text-align: right;
        }

        .summary-yellow-value {
            background: #fff200;
            color: #000;
            font-weight: bold;
            text-align: right;
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <td class="report-heading" colspan="8">{$heading} - {$title}</td>
        </tr>
        <tr>
            <td class="report-title" colspan="8"></td>
        </tr>
        <tr>
            <th class="header-black">ITEM</th>
            <th class="header-black">QTY</th>
            <th class="header-red">HPP</th>
            <th class="header-black">PRICE</th>
            <th class="header-black">INCOME</th>
            <th class="header-red">HPP ALL</th>
            <th class="header-yellow">MARGIN</th>
            <th class="header-black">%</th>
        </tr>
        {$rows}
        <tr>
            <td colspan="3" style="border:none;background:#fff;"></td>
            <td colspan="1" class="summary-label">INCOME:</td>
            <td colspan="1" class="summary-value">{$formattedIncome}</td>
            <td class="summary-yellow-label">MARGIN:</td>
            <td class="summary-yellow-value">{$formattedGrossProfit}</td>
        </tr>
        <tr>
            <td colspan="3" style="border:none;background:#fff;"></td>
            <td colspan="1" class="summary-label">Pengeluaran:</td>
            <td colspan="1" class="summary-value">{$formattedExpense}</td>
            <td class="summary-yellow-label">MARGIN:</td>
            <td class="summary-yellow-value">{$formattedNetProfit}</td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function excelResponse(string $html, string $filename): Response
    {
        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }

    private function formatAmount(?float $value): string
    {
        if ($value === null || abs($value) < 0.00001) {
            return '-';
        }

        return $this->formatNumber($value);
    }

    private function formatPercent(?float $value): string
    {
        if ($value === null) {
            return '-';
        }

        return number_format($value, 0, ',', '.') . '%';
    }

    private function formatNumber(float $value): string
    {
        return number_format($value, 2, ',', '.');
    }
}
