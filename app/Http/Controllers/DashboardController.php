<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the Command Center dashboard.
     */
    public function index()
    {
        // 1. Core Fleet Numbers
        $totalHardware = InventoryItem::where('status', '!=', 'sold')->count();
        $inStock = InventoryItem::where('status', 'in_stock')->count();
        $onLoan = InventoryItem::where('status', 'on_loan')->count();
        $soldCount = InventoryItem::where('status', 'sold')->count();

        // 2. Total Valuation of Active Physical Fleet
        $totalFleetValue = (float) InventoryItem::where('status', '!=', 'sold')
            ->join('products', 'inventory_items.product_id', '=', 'products.id')
            ->sum('products.unit_price');

        // 3. Fleet Allocation Percentages
        $statusDistribution = [
            'stock' => $totalHardware > 0 ? round(($inStock / $totalHardware) * 100) : 0,
            'loan'  => $totalHardware > 0 ? round(($onLoan / $totalHardware) * 100) : 0,
        ];

        // 4. Active Field Loans & Overdue Tracking
        $activeLoans = InventoryItem::with(['product', 'client', 'location', 'currentLoan'])
            ->where('status', 'on_loan')
            ->get()
            ->map(function ($item) {
                $endDate = $item->currentLoan?->loan_end_date 
                    ? Carbon::parse($item->currentLoan->loan_end_date)->endOfDay() 
                    : null;

                $isOverdue = $endDate && $endDate->isPast();
                $isExpiringSoon = $endDate && $endDate->isFuture() && now()->diffInDays($endDate) <= 14;

                $item->is_overdue = $isOverdue;
                $item->is_expiring_soon = $isExpiringSoon;
                $item->end_date_formatted = $endDate ? $endDate->format('d M Y') : 'Open Term';
                $item->days_overdue = ($isOverdue && $endDate) ? (int) abs(now()->diffInDays($endDate, false)) : 0;
                $item->days_remaining = ($endDate && !$isOverdue) ? (int) now()->diffInDays($endDate, false) : 0;

                return $item;
            })
            ->sortBy(function ($item) {
                // Sort overdue first, then soonest expiration
                if ($item->is_overdue) {
                    return -1000 + $item->days_overdue;
                }
                return $item->days_remaining;
            })
            ->values();

        $overdueCount = $activeLoans->where('is_overdue', true)->count();
        $dueSoonCount = $activeLoans->where('is_expiring_soon', true)->count();

        // 5. Recent Activity Stream (Last 7 transactions)
        $recentActivity = InventoryTransaction::with([
            'inventoryItem.product', 
            'user', 
            'toLocation', 
            'fromLocation', 
            'client'
        ])
            ->orderBy('created_at', 'desc')
            ->limit(7)
            ->get();

        // 6. Low Stock Inventory Warnings (breached min_stock_level threshold or <= 2 in stock)
        $lowStockProducts = Product::withCount(['inventoryItems as in_stock_qty' => function ($query) {
            $query->where('status', 'in_stock');
        }])
            ->get()
            ->filter(function ($product) {
                $threshold = $product->min_stock_level > 0 ? $product->min_stock_level : 2;
                return $product->in_stock_qty <= $threshold;
            })
            ->sortBy('in_stock_qty')
            ->take(6)
            ->values();

        return view('dashboard', compact(
            'totalHardware',
            'inStock',
            'onLoan',
            'soldCount',
            'totalFleetValue',
            'statusDistribution',
            'activeLoans',
            'overdueCount',
            'dueSoonCount',
            'recentActivity',
            'lowStockProducts'
        ));
    }
}
