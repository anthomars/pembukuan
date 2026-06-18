<?php

namespace App\Livewire\Reports;

use App\Models\Expense;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ReportDashboard extends Component
{
    public string $reportDate;

    public string $reportMonth;

    public function mount(): void
    {
        $today = now();

        $this->reportDate = $today->toDateString();
        $this->reportMonth = $today->format('Y-m');
    }

    public function render()
    {
        $date = Carbon::parse($this->reportDate);
        $month = Carbon::parse($this->reportMonth . '-01');

        $dailySales = Sale::query()
            ->with('items')
            ->whereDate('sold_at', $date)
            ->get();

        $dailyExpense = Expense::query()
            ->whereDate('expense_date', $date)
            ->sum('amount');

        $dailyIncome = SaleItem::query()
            ->whereHas('sale', fn ($query) => $query->whereDate('sold_at', $date))
            ->selectRaw('COALESCE(SUM(qty * sell_price), 0) as total_income')
            ->value('total_income');
        $dailyHpp = SaleItem::query()
            ->whereHas('sale', fn ($query) => $query->whereDate('sold_at', $date))
            ->selectRaw('COALESCE(SUM(qty * hpp_snapshot), 0) as total_hpp')
            ->value('total_hpp');

        $dailyGrossProfit = $dailyIncome - $dailyHpp;
        $dailyNetProfit = $dailyGrossProfit - $dailyExpense;
        $dailyMargin = $dailyGrossProfit;

        $dailyTopProducts = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->select('product_name_snapshot')
            ->selectRaw('SUM(qty) as total_qty')
            ->whereDate('sales.sold_at', $date)
            ->groupBy('product_name_snapshot')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        $monthlySales = Sale::query()
            ->with('items')
            ->whereYear('sold_at', $month->year)
            ->whereMonth('sold_at', $month->month)
            ->get();

        $monthlyExpense = Expense::query()
            ->whereYear('expense_date', $month->year)
            ->whereMonth('expense_date', $month->month)
            ->sum('amount');

        $monthlyIncome = SaleItem::query()
            ->whereHas('sale', fn ($query) => $query->whereYear('sold_at', $month->year)->whereMonth('sold_at', $month->month))
            ->selectRaw('COALESCE(SUM(qty * sell_price), 0) as total_income')
            ->value('total_income');
        $monthlyHpp = SaleItem::query()
            ->whereHas('sale', fn ($query) => $query->whereYear('sold_at', $month->year)->whereMonth('sold_at', $month->month))
            ->selectRaw('COALESCE(SUM(qty * hpp_snapshot), 0) as total_hpp')
            ->value('total_hpp');

        $monthlyGrossProfit = $monthlyIncome - $monthlyHpp;
        $monthlyNetProfit = $monthlyGrossProfit - $monthlyExpense;
        $monthlyMargin = $monthlyGrossProfit;

        $dailyTransactions = $dailySales
            ->map(function (Sale $sale) {
                $hpp = $sale->items->sum(fn ($item) => $item->qty * $item->hpp_snapshot);
                $grossProfit = $sale->total - $hpp;

                return [
                    'id' => $sale->id,
                    'sold_at' => $sale->sold_at,
                    'total' => $sale->total,
                    'hpp' => $hpp,
                    'gross_profit' => $grossProfit,
                    'margin' => $grossProfit,
                ];
            });

        return view('livewire.reports.report-dashboard', [
            'dailyIncome' => $dailyIncome,
            'dailyHpp' => $dailyHpp,
            'dailyGrossProfit' => $dailyGrossProfit,
            'dailyExpense' => $dailyExpense,
            'dailyNetProfit' => $dailyNetProfit,
            'dailyMargin' => $dailyMargin,
            'dailyTopProducts' => $dailyTopProducts,
            'monthlyIncome' => $monthlyIncome,
            'monthlyHpp' => $monthlyHpp,
            'monthlyGrossProfit' => $monthlyGrossProfit,
            'monthlyExpense' => $monthlyExpense,
            'monthlyNetProfit' => $monthlyNetProfit,
            'monthlyMargin' => $monthlyMargin,
            'dailyTransactions' => $dailyTransactions,
        ])->layout('layouts.app');
    }
}
