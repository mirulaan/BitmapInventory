<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\Location;
use App\Models\StockCount;
use App\Models\StockCountItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockCountController extends Controller
{
    /**
     * Display a listing of physical stock counts / cycle count sessions.
     */
    public function index(Request $request)
    {
        $query = StockCount::with(['location', 'starter', 'completer', 'items']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        $stockCounts = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $locations = Location::where('type', '!=', 'client_site')->orderBy('name')->get();
        $inProgressCount = StockCount::where('status', 'in_progress')->count();
        $completedCount = StockCount::where('status', 'completed')->count();
        $totalSessionsCount = StockCount::count();

        return view('stock_counts.index', compact(
            'stockCounts',
            'locations',
            'inProgressCount',
            'completedCount',
            'totalSessionsCount'
        ));
    }

    /**
     * Show form to start a new cycle count session.
     */
    public function create()
    {
        $locations = Location::where('type', '!=', 'client_site')
            ->orderBy('branch')
            ->orderBy('floor')
            ->orderBy('name')
            ->get();

        return view('stock_counts.create', compact('locations'));
    }

    /**
     * Initiate a new stock count session.
     */
    public function store(Request $request)
    {
        $request->validate([
            'location_id' => 'nullable|exists:locations,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $yearMonth = now()->format('Ym');
        $seq = StockCount::where('count_number', 'like', "CC-{$yearMonth}-%")->count() + 1;
        $countNumber = sprintf('CC-%s-%04d', $yearMonth, $seq);

        $stockCount = StockCount::create([
            'count_number' => $countNumber,
            'location_id' => $request->location_id,
            'status' => 'in_progress',
            'started_by' => auth()->id() ?? 1,
            'started_at' => now(),
            'notes' => $request->notes,
        ]);

        // Pre-populate expected items from inventory_items
        $itemsQuery = InventoryItem::where('status', 'in_stock');
        if ($request->filled('location_id')) {
            $itemsQuery->where('location_id', $request->location_id);
        }

        $expectedItems = $itemsQuery->get();

        foreach ($expectedItems as $item) {
            StockCountItem::create([
                'stock_count_id' => $stockCount->id,
                'inventory_item_id' => $item->id,
                'expected_location_id' => $item->location_id,
                'counted_location_id' => null,
                'scanned_serial_number' => $item->serial_number,
                'status' => 'missing', // initially missing until scanned!
                'reconciled' => false,
                'scanned_at' => null,
            ]);
        }

        return redirect()->route('stock-counts.show', $stockCount->id)->with('success', "Stock Count session {$stockCount->count_number} initialized with " . $expectedItems->count() . " expected hardware units.");
    }

    /**
     * Show cycle count scanning interface & discrepancy report.
     */
    public function show(StockCount $stockCount)
    {
        $stockCount->load(['location', 'starter', 'completer', 'items.inventoryItem.product', 'items.expectedLocation', 'items.countedLocation']);

        $matchedCount = $stockCount->items->where('status', 'matched')->count();
        $missingCount = $stockCount->items->where('status', 'missing')->count();
        $misplacedCount = $stockCount->items->where('status', 'misplaced')->count();
        $surplusCount = $stockCount->items->where('status', 'surplus_untracked')->count();
        $totalItems = $stockCount->items->count();

        return view('stock_counts.show', compact(
            'stockCount',
            'matchedCount',
            'missingCount',
            'misplacedCount',
            'surplusCount',
            'totalItems'
        ));
    }

    /**
     * Scan a serial number in the active count session.
     */
    public function scan(Request $request, StockCount $stockCount)
    {
        $request->validate([
            'serial_number' => 'required|string',
        ]);

        if ($stockCount->status !== 'in_progress') {
            return redirect()->back()->with('error', 'This stock count session is already closed.');
        }

        $serial = trim($request->serial_number);

        // Check if this item is in the count manifest
        $countItem = StockCountItem::where('stock_count_id', $stockCount->id)
            ->where('scanned_serial_number', $serial)
            ->first();

        if ($countItem) {
            $countItem->update([
                'status' => 'matched',
                'counted_location_id' => $stockCount->location_id ?? $countItem->expected_location_id,
                'scanned_at' => now(),
            ]);

            return redirect()->route('stock-counts.show', $stockCount->id)->with('success', "Serial [{$serial}] verified and matched.");
        }

        // Check if item exists in general inventory but wasn't expected here (misplaced)
        $invItem = InventoryItem::where('serial_number', $serial)->first();

        if ($invItem) {
            StockCountItem::create([
                'stock_count_id' => $stockCount->id,
                'inventory_item_id' => $invItem->id,
                'expected_location_id' => $invItem->location_id,
                'counted_location_id' => $stockCount->location_id,
                'scanned_serial_number' => $serial,
                'status' => 'misplaced',
                'reconciled' => false,
                'scanned_at' => now(),
            ]);

            return redirect()->route('stock-counts.show', $stockCount->id)->with('warning', "Misplaced Unit Detected: [{$serial}] was expected at [{$invItem->location?->name}] but scanned here.");
        }

        // Item does not exist at all in database (surplus / untracked)
        StockCountItem::create([
            'stock_count_id' => $stockCount->id,
            'inventory_item_id' => null,
            'expected_location_id' => null,
            'counted_location_id' => $stockCount->location_id,
            'scanned_serial_number' => $serial,
            'status' => 'surplus_untracked',
            'reconciled' => false,
            'scanned_at' => now(),
        ]);

        return redirect()->route('stock-counts.show', $stockCount->id)->with('error', "Untracked Hardware: [{$serial}] was scanned but does not exist in system database.");
    }

    /**
     * Reconcile discrepancies (auto-write-off missing shrinkage, auto-relocate misplaced).
     */
    public function reconcile(Request $request, StockCount $stockCount)
    {
        if ($stockCount->status !== 'in_progress') {
            return redirect()->back()->with('error', 'Count session is not active.');
        }

        $stockCount->load('items.inventoryItem');

        $missingWrittenOff = 0;
        $misplacedRelocated = 0;

        foreach ($stockCount->items as $item) {
            if ($item->reconciled) continue;

            if ($item->status === 'missing' && $item->inventoryItem) {
                // Write off shrinkage
                $item->inventoryItem->update([
                    'status' => 'decommissioned',
                ]);

                InventoryTransaction::create([
                    'inventory_item_id' => $item->inventory_item_id,
                    'user_id' => auth()->id() ?? 1,
                    'transaction_type' => 'cycle_count_adjustment',
                    'from_location_id' => $item->expected_location_id,
                    'to_location_id' => null,
                    'from_status' => 'in_stock',
                    'to_status' => 'decommissioned',
                    'quantity' => 1,
                    'reference_number' => $stockCount->count_number,
                    'notes' => "Reconciliation Shrinkage: Unit not found during Cycle Count {$stockCount->count_number}.",
                ]);

                $item->update(['reconciled' => true]);
                $missingWrittenOff++;
            } elseif ($item->status === 'misplaced' && $item->inventoryItem && $stockCount->location_id) {
                // Auto-relocate
                $oldLocId = $item->expected_location_id;
                $newLocId = $stockCount->location_id;

                $item->inventoryItem->update([
                    'location_id' => $newLocId,
                    'status' => 'in_stock',
                ]);

                InventoryTransaction::create([
                    'inventory_item_id' => $item->inventory_item_id,
                    'user_id' => auth()->id() ?? 1,
                    'transaction_type' => 'cycle_count_adjustment',
                    'from_location_id' => $oldLocId,
                    'to_location_id' => $newLocId,
                    'from_status' => 'in_stock',
                    'to_status' => 'in_stock',
                    'quantity' => 1,
                    'reference_number' => $stockCount->count_number,
                    'notes' => "Reconciliation Relocation: Found at {$stockCount->location?->name} during Cycle Count {$stockCount->count_number}.",
                ]);

                $item->update(['reconciled' => true]);
                $misplacedRelocated++;
            } elseif ($item->status === 'matched') {
                $item->update(['reconciled' => true]);
            }
        }

        $stockCount->update([
            'status' => 'completed',
            'completed_by' => auth()->id() ?? 1,
            'completed_at' => now(),
        ]);

        return redirect()->route('stock-counts.show', $stockCount->id)->with('success', "Discrepancy reconciliation completed! {$missingWrittenOff} missing items written off as shrinkage, {$misplacedRelocated} misplaced items updated to active rack.");
    }
}
