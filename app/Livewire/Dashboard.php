<?php

namespace App\Livewire;

use App\Models\Expense;
use App\Models\Sale;
use App\Models\SaleItem;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $today = now();

        $todaySales = Sale::query()
            ->with('items')
            ->whereDate('sold_at', $today)
            ->latest('sold_at')
            ->get();

        $todayIncome = $todaySales->sum('total');
        $todayHpp = $todaySales->sum(function (Sale $sale) {
            return $sale->items->sum(fn ($item) => $item->qty * $item->hpp_snapshot);
        });
        $todayExpense = Expense::query()
            ->whereDate('expense_date', $today)
            ->sum('amount');

        $todayGrossProfit = $todayIncome - $todayHpp;
        $todayNetProfit = $todayGrossProfit - $todayExpense;
        $todayMargin = $todayGrossProfit;

        $bestSellingProducts = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->whereDate('sales.sold_at', $today)
            ->select('product_name_snapshot')
            ->selectRaw('SUM(qty) as total_qty')
            ->groupBy('product_name_snapshot')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        return view('livewire.dashboard', [
            'todayIncome' => $todayIncome,
            'todayHpp' => $todayHpp,
            'todayGrossProfit' => $todayGrossProfit,
            'todayExpense' => $todayExpense,
            'todayNetProfit' => $todayNetProfit,
            'todayMargin' => $todayMargin,
            'todaySalesCount' => $todaySales->count(),
            'bestSellingProducts' => $bestSellingProducts,
            'todaySales' => $todaySales,
        ])->layout('layouts.app');
    }
}
