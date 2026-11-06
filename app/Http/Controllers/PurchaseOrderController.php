<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\Location;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of purchase orders.
     */
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'creator', 'items.product']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $like = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $query->where(function ($q) use ($search, $like) {
                $q->where('po_number', $like, "%{$search}%")
                    ->orWhere('notes', $like, "%{$search}%")
                    ->orWhereHas('supplier', function ($sq) use ($search, $like) {
                        $sq->where('name', $like, "%{$search}%");
                    });
            });
        }

        $purchaseOrders = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $suppliers = Supplier::orderBy('name')->get();
        $draftCount = PurchaseOrder::where('status', 'draft')->count();
        $orderedCount = PurchaseOrder::where('status', 'ordered')->count();
        $receivedCount = PurchaseOrder::where('status', 'received')->count();
        $totalOrdersCount = PurchaseOrder::count();

        return view('purchase_orders.index', compact(
            'purchaseOrders',
            'suppliers',
            'draftCount',
            'orderedCount',
            'receivedCount',
            'totalOrdersCount'
        ));
    }

    /**
     * Show form to create a new purchase order.
     */
    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        return view('purchase_orders.create', compact('suppliers', 'products'));
    }

    /**
     * Store a newly created purchase order.
     */
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        $yearMonth = now()->format('Ym');
        $seq = PurchaseOrder::where('po_number', 'like', "PO-{$yearMonth}-%")->count() + 1;
        $poNumber = sprintf('PO-%s-%04d', $yearMonth, $seq);

        $totalAmount = 0;
        foreach ($request->items as $itemData) {
            $totalAmount += (int) $itemData['quantity'] * (float) $itemData['unit_cost'];
        }

        $po = PurchaseOrder::create([
            'po_number' => $poNumber,
            'supplier_id' => $request->supplier_id,
            'status' => 'draft',
            'order_date' => $request->order_date,
            'expected_delivery_date' => $request->expected_delivery_date,
            'total_amount' => $totalAmount,
            'notes' => $request->notes,
            'created_by' => auth()->id() ?? 1,
        ]);

        foreach ($request->items as $itemData) {
            $qty = (int) $itemData['quantity'];
            $cost = (float) $itemData['unit_cost'];
            PurchaseOrderItem::create([
                'purchase_order_id' => $po->id,
                'product_id' => $itemData['product_id'],
                'quantity_ordered' => $qty,
                'quantity_received' => 0,
                'unit_cost' => $cost,
                'total_cost' => $qty * $cost,
            ]);
        }

        return redirect()->route('purchase-orders.show', $po->id)->with('success', "Purchase Order {$po->po_number} successfully created.");
    }

    /**
     * Display a specific purchase order.
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'creator', 'items.product']);
        $locations = Location::where('type', '!=', 'client_site')->orderBy('name')->get();

        return view('purchase_orders.show', compact('purchaseOrder', 'locations'));
    }

    /**
     * Update PO status (e.g. from draft to ordered, or cancelled).
     */
    public function updateStatus(Request $request, PurchaseOrder $purchaseOrder)
    {
        $request->validate([
            'status' => 'required|in:draft,ordered,cancelled',
        ]);

        $purchaseOrder->update([
            'status' => $request->status,
        ]);

        return redirect()->route('purchase-orders.show', $purchaseOrder->id)->with('success', "PO status updated to " . ucfirst($request->status) . ".");
    }

    /**
     * Receive goods from PO directly into warehouse rack.
     */
    public function receive(Request $request, PurchaseOrder $purchaseOrder)
    {
        $request->validate([
            'location_id' => 'required|exists:locations,id',
            'batch_no' => 'nullable|string|max:100',
        ]);

        if ($purchaseOrder->status === 'received') {
            return redirect()->back()->with('error', 'This purchase order has already been marked as received.');
        }

        $locationId = $request->location_id;
        $batchNo = $request->filled('batch_no') ? trim($request->batch_no) : "LOT-{$purchaseOrder->po_number}";

        $totalUnitsCreated = 0;

        foreach ($purchaseOrder->items as $poItem) {
            $remaining = $poItem->quantity_ordered - $poItem->quantity_received;
            if ($remaining <= 0) continue;

            $product = $poItem->product;

            for ($i = 1; $i <= $remaining; $i++) {
                // Generate a clean serialized ID
                $serialNumber = sprintf('%s-%s-%03d', $product->sku, now()->format('ymdHi'), rand(100, 999));

                $item = InventoryItem::create([
                    'product_id' => $product->id,
                    'location_id' => $locationId,
                    'serial_number' => $serialNumber,
                    'batch_no' => $batchNo,
                    'unit_cost' => $poItem->unit_cost,
                    'quantity' => 1,
                    'status' => 'in_stock',
                ]);

                InventoryTransaction::create([
                    'inventory_item_id' => $item->id,
                    'user_id' => auth()->id() ?? 1,
                    'transaction_type' => 'stock_in',
                    'from_location_id' => null,
                    'to_location_id' => $locationId,
                    'from_status' => null,
                    'to_status' => 'in_stock',
                    'quantity' => 1,
                    'batch_no' => $batchNo,
                    'unit_cost' => $poItem->unit_cost,
                    'po_supplier' => $purchaseOrder->po_number,
                    'notes' => "Received from Supplier: {$purchaseOrder->supplier?->name} under {$purchaseOrder->po_number}",
                ]);

                $totalUnitsCreated++;
            }

            $poItem->update([
                'quantity_received' => $poItem->quantity_ordered,
            ]);
        }

        $purchaseOrder->update([
            'status' => 'received',
        ]);

        return redirect()->route('purchase-orders.show', $purchaseOrder->id)->with('success', "PO received! {$totalUnitsCreated} hardware units were serialized and placed into warehouse stock.");
    }

    /**
     * 1-Click Auto-Draft Purchase Order from low stock safety thresholds.
     */
    public function autoDraftFromLowStock()
    {
        $lowStockProducts = Product::withCount(['inventoryItems as in_stock_qty' => function ($q) {
            $q->where('status', 'in_stock');
        }])->get()->filter(function ($p) {
            $threshold = $p->min_stock_level > 0 ? $p->min_stock_level : 2;
            return $p->in_stock_qty <= $threshold;
        });

        if ($lowStockProducts->isEmpty()) {
            return redirect()->route('purchase-orders.index')->with('info', 'No products are currently at or below minimum safety stock levels.');
        }

        $defaultSupplier = Supplier::first();
        if (!$defaultSupplier) {
            return redirect()->route('suppliers.index')->with('error', 'Please create at least one supplier before generating Purchase Orders.');
        }

        $yearMonth = now()->format('Ym');
        $seq = PurchaseOrder::where('po_number', 'like', "PO-{$yearMonth}-%")->count() + 1;
        $poNumber = sprintf('PO-%s-%04d', $yearMonth, $seq);

        $po = PurchaseOrder::create([
            'po_number' => $poNumber,
            'supplier_id' => $defaultSupplier->id,
            'status' => 'draft',
            'order_date' => now()->toDateString(),
            'expected_delivery_date' => now()->addDays(7)->toDateString(),
            'total_amount' => 0,
            'notes' => 'Automated replenishment draft generated from Low Stock Safety Level alerts.',
            'created_by' => auth()->id() ?? 1,
        ]);

        $totalAmount = 0;
        foreach ($lowStockProducts as $prod) {
            $threshold = $prod->min_stock_level > 0 ? $prod->min_stock_level : 5;
            // Target reorder to reach 2x safety threshold
            $reorderQty = max(5, ($threshold * 2) - $prod->in_stock_qty);
            $unitCost = (float) ($prod->unit_price > 0 ? $prod->unit_price * 0.7 : 500.00); // estimated 70% of catalog price
            $lineTotal = $reorderQty * $unitCost;

            PurchaseOrderItem::create([
                'purchase_order_id' => $po->id,
                'product_id' => $prod->id,
                'quantity_ordered' => $reorderQty,
                'quantity_received' => 0,
                'unit_cost' => $unitCost,
                'total_cost' => $lineTotal,
            ]);

            $totalAmount += $lineTotal;
        }

        $po->update(['total_amount' => $totalAmount]);

        return redirect()->route('purchase-orders.show', $po->id)->with('success', "Draft PO {$po->po_number} automatically generated for " . $lowStockProducts->count() . " low-stock models.");
    }
}
