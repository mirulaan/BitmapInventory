<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Client;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\Location;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InventoryWorkflowController extends Controller
{
    /**
     * Display the inventory overview grouped by product.
     */
    public function index(Request $request)
    {
        $query = Product::withCount([
            'inventoryItems as total_qty',
            'inventoryItems as in_stock_qty' => function ($q) {
                $q->where('status', 'in_stock');
            },
            'inventoryItems as on_loan_qty' => function ($q) {
                $q->where('status', 'on_loan');
            },
            'inventoryItems as sold_qty' => function ($q) {
                $q->where('status', 'sold');
            },
        ]);

        // Search by Product Name, SKU, or Brand
        if ($request->filled('search')) {
            $search = $request->search;
            $like = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $query->where(function ($q) use ($search, $like) {
                $q->where('name', $like, "%{$search}%")
                    ->orWhere('sku', $like, "%{$search}%")
                    ->orWhere('brand', $like, "%{$search}%");
            });
        }

        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }

        // Sort by Product Name, Category, or Brand
        $sort = $request->get('sort', 'name_asc');
        if ($sort === 'name_desc') {
            $query->orderBy('name', 'desc');
        } elseif ($sort === 'brand_asc') {
            $query->orderBy('brand', 'asc')->orderBy('name', 'asc');
        } elseif ($sort === 'brand_desc') {
            $query->orderBy('brand', 'desc')->orderBy('name', 'asc');
        } elseif ($sort === 'category_asc') {
            $query->orderBy('category', 'asc')->orderBy('name', 'asc');
        } elseif ($sort === 'category_desc') {
            $query->orderBy('category', 'desc')->orderBy('name', 'asc');
        } else {
            $query->orderBy('name', 'asc');
        }

        $products = $query->get();
        $brands = Product::whereNotNull('brand')
            ->where('brand', '!=', '')
            ->distinct()
            ->orderBy('brand')
            ->pluck('brand');

        return view('inventory.index', compact('products', 'brands'));
    }

    /**
     * Show individual serialized items for a specific product.
     */
    public function showProduct(Request $request, Product $product)
    {
        $query = InventoryItem::with(['location', 'client', 'currentLoan'])
            ->where('product_id', $product->id)
            ->where('status', '!=', 'sold');

        // Search by Serial Number
        if ($request->filled('search')) {
            $query->where('serial_number', 'ilike', "%{$request->search}%");
        }

        // Filter by Location
        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $items = $query->orderBy('created_at', 'desc')->get();

        // Fetch only locations that currently contain this product
        $locationIds = InventoryItem::where('product_id', $product->id)
            ->where('status', '!=', 'sold')
            ->pluck('location_id')
            ->unique();
        $locations = Location::whereIn('id', $locationIds)->orderBy('name')->get();

        return view('inventory.product', compact('product', 'items', 'locations'));
    }

    /**
     * Build the filtered query for the audit ledger.
     */
    protected function buildAuditQuery(Request $request)
    {
        $like = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $query = InventoryTransaction::with([
            'item.product',
            'user',
            'fromLocation',
            'toLocation',
            'client',
        ]);

        // Filter by Transaction Type
        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        // Filter by Location
        if ($request->filled('location_id')) {
            $locId = $request->location_id;
            $query->where(function ($q) use ($locId) {
                $q->where('from_location_id', $locId)
                    ->orWhere('to_location_id', $locId);
            });
        }

        // Filter by Date Range
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', Carbon::parse($request->date_from)->startOfDay());
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', Carbon::parse($request->date_to)->endOfDay());
        }

        // Multi-field search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search, $like) {
                $q->where('loan_form_no', $like, "%{$search}%")
                    ->orWhere('po_customer', $like, "%{$search}%")
                    ->orWhere('po_supplier', $like, "%{$search}%")
                    ->orWhere('reference_number', $like, "%{$search}%")
                    ->orWhere('person_in_charge', $like, "%{$search}%")
                    ->orWhere('notes', $like, "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search, $like) {
                        $userQuery->where('name', $like, "%{$search}%");
                    })
                    ->orWhereHas('item', function ($itemQuery) use ($search, $like) {
                        $itemQuery->where('serial_number', $like, "%{$search}%")
                            ->orWhereHas('product', function ($prodQuery) use ($search, $like) {
                                $prodQuery->where('name', $like, "%{$search}%")
                                    ->orWhere('brand', $like, "%{$search}%")
                                    ->orWhere('sku', $like, "%{$search}%");
                            });
                    })
                    ->orWhereHas('toLocation', function ($locQuery) use ($search, $like) {
                        $locQuery->where('name', $like, "%{$search}%");
                    })
                    ->orWhereHas('fromLocation', function ($locQuery) use ($search, $like) {
                        $locQuery->where('name', $like, "%{$search}%");
                    });
            });
        }

        return $query;
    }

    /**
     * Display the immutable audit ledger.
     */
    public function audit(Request $request)
    {
        $query = $this->buildAuditQuery($request);

        $transactions = $query->orderBy('created_at', 'desc')->paginate(30)->withQueryString();

        // High-level Ledger Metrics
        $totalTransactions = InventoryTransaction::count();
        $stockInCount = InventoryTransaction::where('transaction_type', 'stock_in')->count();
        $loanOutCount = InventoryTransaction::where('transaction_type', 'dispatch_loan')->count();
        $returnsCount = InventoryTransaction::where('transaction_type', 'return_loan')->count();
        $salesCount = InventoryTransaction::whereIn('transaction_type', ['convert_to_sale', 'direct_sale'])->count();

        $locations = Location::orderBy('name')->get();

        return view('inventory.audit', compact(
            'transactions',
            'totalTransactions',
            'stockInCount',
            'loanOutCount',
            'returnsCount',
            'salesCount',
            'locations'
        ));
    }

    /**
     * Stream CSV export of filtered audit ledger records.
     */
    public function exportAuditCsv(Request $request)
    {
        $query = $this->buildAuditQuery($request);
        $transactions = $query->orderBy('created_at', 'desc')->get();

        $filename = 'audit_ledger_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($transactions) {
            $handle = fopen('php://output', 'w');

            // Add UTF-8 BOM for clean Excel rendering
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Log ID',
                'Timestamp',
                'Event Type',
                'Product Model',
                'Brand',
                'SKU',
                'Serial Number',
                'Quantity',
                'From Location',
                'To Location',
                'Status Transition',
                'Client Account',
                'Loan Form No',
                'Customer PO',
                'Supplier PO',
                'Reference No',
                'Person in Charge',
                'Operator / User',
                'Loan Start Date',
                'Loan End Date',
                'Notes / Remarks',
            ]);

            foreach ($transactions as $log) {
                $statusChange = ($log->from_status || $log->to_status)
                    ? "{$log->from_status} -> {$log->to_status}"
                    : 'N/A';

                fputcsv($handle, [
                    $log->id,
                    $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : '',
                    ucwords(str_replace('_', ' ', $log->transaction_type)),
                    $log->item?->product?->name ?? 'N/A',
                    $log->item?->product?->brand ?? 'N/A',
                    $log->item?->product?->sku ?? 'N/A',
                    $log->item?->serial_number ?? 'Bulk',
                    $log->quantity ?? 1,
                    $log->fromLocation?->full_path ?? $log->fromLocation?->name ?? 'N/A',
                    $log->toLocation?->full_path ?? $log->toLocation?->name ?? 'N/A',
                    $statusChange,
                    $log->client?->company_name ?? 'N/A',
                    $log->loan_form_no ?? '',
                    $log->po_customer ?? '',
                    $log->po_supplier ?? '',
                    $log->reference_number ?? '',
                    $log->person_in_charge ?? '',
                    $log->user?->name ?? 'System',
                    $log->loan_start_date ? $log->loan_start_date->format('Y-m-d') : '',
                    $log->loan_end_date ? $log->loan_end_date->format('Y-m-d') : '',
                    $log->notes ?? '',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Show the Stock In form.
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        $locations = Location::where('type', '!=', 'client_site')
            ->orderBy('branch')
            ->orderBy('floor')
            ->orderBy('name')
            ->get();

        return view('inventory.create', compact('categories', 'products', 'locations'));
    }

    /**
     * Store bulk serialized hardware units into inventory.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'location_id' => 'required|exists:locations,id',
            'batch_no' => 'nullable|string|max:100',
            'unit_cost' => 'nullable|numeric|min:0',
            'serial_numbers' => 'required|array|min:1',
            'serial_numbers.*' => 'required|string|distinct|unique:inventory_items,serial_number',
        ], [
            'serial_numbers.required' => 'You must add at least one serial number to the table.',
            'serial_numbers.*.unique' => 'One of the serial numbers you entered is already in the database.',
            'serial_numbers.*.distinct' => 'You entered the same serial number twice in the table.',
        ]);

        $batchNo = $request->filled('batch_no') ? trim($request->batch_no) : null;
        $unitCost = $request->filled('unit_cost') ? (float) $request->unit_cost : 0.00;

        foreach ($request->serial_numbers as $sn) {
            $item = InventoryItem::create([
                'product_id' => $request->product_id,
                'location_id' => $request->location_id,
                'serial_number' => trim($sn),
                'batch_no' => $batchNo,
                'unit_cost' => $unitCost,
                'quantity' => 1,
                'status' => 'in_stock',
            ]);

            InventoryTransaction::create([
                'inventory_item_id' => $item->id,
                'user_id' => auth()->id() ?? 1,
                'transaction_type' => 'stock_in',
                'from_location_id' => null,
                'to_location_id' => $request->location_id,
                'from_status' => null,
                'to_status' => 'in_stock',
                'batch_no' => $batchNo,
                'unit_cost' => $unitCost,
                'quantity' => 1,
                'notes' => 'Received into stock' . ($batchNo ? " (Batch: {$batchNo})" : ''),
            ]);
        }

        return redirect()->route('inventory.index')->with('success', count($request->serial_numbers) . ' new hardware items successfully added to stock.');
    }

    /**
     * Show the hardware edit form and loan extension interface.
     */
    public function edit(InventoryItem $item)
    {
        $categories = Category::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        $locations = Location::orderBy('type')->orderBy('name')->get();

        if ($item->status === 'on_loan') {
            $item->load('currentLoan');
        }

        return view('inventory.edit', compact('item', 'categories', 'products', 'locations'));
    }

    /**
     * Update hardware details and optionally extend active loan period.
     */
    public function update(Request $request, InventoryItem $item)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'location_id' => 'required|exists:locations,id',
            'serial_number' => 'required|string|max:255',
            'extend_days' => 'nullable|integer|min:1',
        ]);

        $item->update([
            'product_id' => $request->product_id,
            'location_id' => $request->location_id,
            'serial_number' => $request->serial_number,
        ]);

        if ($item->status === 'on_loan' && $request->filled('extend_days')) {
            $lastLoan = InventoryTransaction::where('inventory_item_id', $item->id)
                ->where('transaction_type', 'dispatch_loan')
                ->latest()
                ->first();

            if ($lastLoan && $lastLoan->loan_end_date) {
                $newEndDate = Carbon::parse($lastLoan->loan_end_date)->addDays((int) $request->extend_days);
                $lastLoan->update([
                    'loan_end_date' => $newEndDate->toDateString(),
                ]);
            }
        }

        return redirect()->route('inventory.index')->with('success', 'Hardware details updated successfully.');
    }

    /**
     * Show dispatch form to loan hardware to a client.
     */
    public function loanForm(InventoryItem $item)
    {
        if ($item->status !== 'in_stock') {
            return redirect()->route('inventory.index')->with('error', 'Only items currently in stock can be loaned out.');
        }

        $clients = Client::orderBy('company_name')->get();
        $locations = Location::where('type', 'client_site')->orderBy('name')->get();

        return view('inventory.loan', compact('item', 'clients', 'locations'));
    }

    /**
     * Process dispatching an item on loan to a client site.
     */
    public function processLoan(Request $request, InventoryItem $item)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'location_id' => 'required|exists:locations,id',
            'person_in_charge' => 'required|string|max:255',
            'loan_form_no' => 'required|string|max:255',
            'loan_start_date' => 'required|date',
            'loan_end_date' => 'required|date|after_or_equal:loan_start_date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $oldLocationId = $item->location_id;

        $item->update([
            'status' => 'on_loan',
            'client_id' => $request->client_id,
            'location_id' => $request->location_id,
        ]);

        InventoryTransaction::create([
            'inventory_item_id' => $item->id,
            'user_id' => auth()->id() ?? 1,
            'transaction_type' => 'dispatch_loan',
            'from_location_id' => $oldLocationId,
            'to_location_id' => $request->location_id,
            'from_status' => 'in_stock',
            'to_status' => 'on_loan',
            'quantity' => $item->quantity,
            'person_in_charge' => $request->person_in_charge,
            'loan_form_no' => $request->loan_form_no,
            'loan_start_date' => $request->loan_start_date,
            'loan_end_date' => $request->loan_end_date,
            'notes' => $request->notes,
        ]);

        return redirect()->route('inventory.index')->with('success', 'Hardware successfully dispatched on loan.');
    }

    /**
     * Show return form to return an on-loan item back to warehouse stock.
     */
    public function returnForm(InventoryItem $item)
    {
        if ($item->status !== 'on_loan') {
            return redirect()->route('inventory.index')->with('error', 'Only items currently on loan can be returned.');
        }

        $locations = Location::where('type', '!=', 'client_site')
            ->orderBy('branch')
            ->orderBy('floor')
            ->orderBy('name')
            ->get();

        return view('inventory.return', compact('item', 'locations'));
    }

    /**
     * Process return of hardware from client site back to internal rack.
     */
    public function processReturn(Request $request, InventoryItem $item)
    {
        $request->validate([
            'location_id' => 'required|exists:locations,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $oldLocationId = $item->location_id;

        $item->update([
            'status' => 'in_stock',
            'location_id' => $request->location_id,
            'client_id' => null,
        ]);

        InventoryTransaction::create([
            'inventory_item_id' => $item->id,
            'user_id' => auth()->id() ?? 1,
            'transaction_type' => 'loan_return',
            'from_location_id' => $oldLocationId,
            'to_location_id' => $request->location_id,
            'from_status' => 'on_loan',
            'to_status' => 'in_stock',
            'quantity' => $item->quantity,
            'notes' => $request->notes,
        ]);

        return redirect()->route('inventory.index')->with('success', 'Hardware successfully returned and placed back into stock.');
    }

    /**
     * Show form to convert an existing loan into a permanent sale.
     */
    public function convertForm(InventoryItem $item)
    {
        if ($item->status !== 'on_loan') {
            return redirect()->route('inventory.index')->with('error', 'Only items currently on loan can be converted to a sale.');
        }

        $lastLoan = InventoryTransaction::where('inventory_item_id', $item->id)
            ->where('transaction_type', 'dispatch_loan')
            ->latest()
            ->first();

        $loanFormNo = $lastLoan ? $lastLoan->loan_form_no : 'Unknown Document';

        return view('inventory.convert', compact('item', 'loanFormNo'));
    }

    /**
     * Process converting an on-loan item into a sale.
     */
    public function processConvert(Request $request, InventoryItem $item)
    {
        $request->validate([
            'person_in_charge' => 'required|string|max:255',
            'po_customer' => 'required|string|max:255',
            'po_supplier' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $currentLocationId = $item->location_id;

        $lastLoan = InventoryTransaction::where('inventory_item_id', $item->id)
            ->where('transaction_type', 'dispatch_loan')
            ->latest()
            ->first();

        $originalLoanFormNo = $lastLoan ? $lastLoan->loan_form_no : null;

        $item->update([
            'status' => 'sold',
        ]);

        InventoryTransaction::create([
            'inventory_item_id' => $item->id,
            'user_id' => auth()->id() ?? 1,
            'transaction_type' => 'convert_to_sale',
            'from_location_id' => $currentLocationId,
            'to_location_id' => $currentLocationId,
            'from_status' => 'on_loan',
            'to_status' => 'sold',
            'quantity' => $item->quantity,
            'person_in_charge' => $request->person_in_charge,
            'loan_form_no' => $originalLoanFormNo,
            'po_customer' => $request->po_customer,
            'po_supplier' => $request->po_supplier,
            'notes' => $request->notes,
        ]);

        return redirect()->route('inventory.index')->with('success', 'Hardware successfully converted to a permanent sale.');
    }

    /**
     * Show form for direct sale from warehouse to client.
     */
    public function sellForm(InventoryItem $item)
    {
        if ($item->status !== 'in_stock') {
            return redirect()->route('inventory.index')->with('error', 'Only items currently in stock can be sold directly.');
        }

        $clients = Client::orderBy('company_name')->get();
        $locations = Location::where('type', 'client_site')->orderBy('name')->get();

        return view('inventory.sell', compact('item', 'clients', 'locations'));
    }

    /**
     * Process direct sale of an in-stock item to a client.
     */
    public function processSell(Request $request, InventoryItem $item)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'location_id' => 'required|exists:locations,id',
            'person_in_charge' => 'required|string|max:255',
            'po_customer' => 'required|string|max:255',
            'po_supplier' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $oldLocationId = $item->location_id;

        $item->update([
            'status' => 'sold',
            'client_id' => $request->client_id,
            'location_id' => $request->location_id,
        ]);

        InventoryTransaction::create([
            'inventory_item_id' => $item->id,
            'user_id' => auth()->id() ?? 1,
            'transaction_type' => 'direct_sale',
            'from_location_id' => $oldLocationId,
            'to_location_id' => $request->location_id,
            'from_status' => 'in_stock',
            'to_status' => 'sold',
            'quantity' => $item->quantity,
            'person_in_charge' => $request->person_in_charge,
            'po_customer' => $request->po_customer,
            'po_supplier' => $request->po_supplier,
            'notes' => $request->notes,
        ]);

        return redirect()->route('inventory.index')->with('success', 'Hardware successfully sold and dispatched to client.');
    }

    /**
     * Show form to transfer internal rack / warehouse location.
     */
    public function transferForm(InventoryItem $item)
    {
        if ($item->status !== 'in_stock') {
            return redirect()->back()->with('error', 'Only in-stock hardware can be transferred between warehouse racks.');
        }

        $locations = Location::where('type', '!=', 'client_site')
            ->where('id', '!=', $item->location_id)
            ->orderBy('branch')
            ->orderBy('floor')
            ->orderBy('name')
            ->get();

        return view('inventory.transfer', compact('item', 'locations'));
    }

    /**
     * Process internal location transfer.
     */
    public function processTransfer(Request $request, InventoryItem $item)
    {
        $request->validate([
            'to_location_id' => 'required|exists:locations,id',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($request->to_location_id == $item->location_id) {
            return back()->withErrors(['to_location_id' => 'Destination rack must be different from current rack.'])->withInput();
        }

        $oldLocationId = $item->location_id;
        $newLocationId = $request->to_location_id;

        $item->update([
            'location_id' => $newLocationId,
        ]);

        InventoryTransaction::create([
            'inventory_item_id' => $item->id,
            'user_id' => auth()->id() ?? 1,
            'transaction_type' => 'transfer',
            'from_location_id' => $oldLocationId,
            'to_location_id' => $newLocationId,
            'from_status' => $item->status,
            'to_status' => $item->status,
            'quantity' => $item->quantity,
            'batch_no' => $item->batch_no,
            'unit_cost' => $item->unit_cost,
            'reference_number' => $request->reference_number,
            'notes' => $request->notes ?? 'Relocated internally between warehouse racks.',
        ]);

        return redirect()->route('inventory.product', $item->product_id)->with('success', 'Hardware unit successfully transferred to new location.');
    }

    /**
     * Show form to reserve item for client or upcoming project.
     */
    public function reserveForm(InventoryItem $item)
    {
        if ($item->status !== 'in_stock') {
            return redirect()->back()->with('error', 'Only in-stock hardware can be placed on reservation.');
        }

        $clients = Client::orderBy('company_name')->get();
        return view('inventory.reserve', compact('item', 'clients'));
    }

    /**
     * Process reservation.
     */
    public function processReserve(Request $request, InventoryItem $item)
    {
        $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'project_name' => 'nullable|string|max:255',
            'person_in_charge' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $item->update([
            'status' => 'reserved',
            'client_id' => $request->client_id,
        ]);

        InventoryTransaction::create([
            'inventory_item_id' => $item->id,
            'user_id' => auth()->id() ?? 1,
            'transaction_type' => 'reserve',
            'from_location_id' => $item->location_id,
            'to_location_id' => $item->location_id,
            'from_status' => 'in_stock',
            'to_status' => 'reserved',
            'quantity' => $item->quantity,
            'batch_no' => $item->batch_no,
            'unit_cost' => $item->unit_cost,
            'person_in_charge' => $request->person_in_charge,
            'reference_number' => $request->project_name,
            'notes' => $request->notes ?? 'Reserved for client staging or project.',
        ]);

        return redirect()->route('inventory.product', $item->product_id)->with('success', 'Hardware successfully marked as Reserved.');
    }

    /**
     * Release active reservation back to in-stock.
     */
    public function processReleaseReservation(Request $request, InventoryItem $item)
    {
        if ($item->status !== 'reserved') {
            return redirect()->back()->with('error', 'Item is not currently reserved.');
        }

        $item->update([
            'status' => 'in_stock',
            'client_id' => null,
        ]);

        InventoryTransaction::create([
            'inventory_item_id' => $item->id,
            'user_id' => auth()->id() ?? 1,
            'transaction_type' => 'release_reservation',
            'from_location_id' => $item->location_id,
            'to_location_id' => $item->location_id,
            'from_status' => 'reserved',
            'to_status' => 'in_stock',
            'quantity' => $item->quantity,
            'batch_no' => $item->batch_no,
            'unit_cost' => $item->unit_cost,
            'notes' => $request->notes ?? 'Reservation cancelled; returned to active stock.',
        ]);

        return redirect()->route('inventory.product', $item->product_id)->with('success', 'Reservation released. Hardware is back in stock.');
    }

    /**
     * Show form to log RMA defect / send for repair.
     */
    public function rmaForm(InventoryItem $item)
    {
        return view('inventory.rma', compact('item'));
    }

    /**
     * Process RMA dispatch / repair ticket.
     */
    public function processRma(Request $request, InventoryItem $item)
    {
        $request->validate([
            'rma_ticket_no' => 'required|string|max:255',
            'vendor_name' => 'required|string|max:255',
            'issue_description' => 'required|string|max:1000',
            'person_in_charge' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $oldStatus = $item->status;
        $item->update([
            'status' => 'rma_defective',
        ]);

        InventoryTransaction::create([
            'inventory_item_id' => $item->id,
            'user_id' => auth()->id() ?? 1,
            'transaction_type' => 'send_rma',
            'from_location_id' => $item->location_id,
            'to_location_id' => null,
            'from_status' => $oldStatus,
            'to_status' => 'rma_defective',
            'quantity' => $item->quantity,
            'batch_no' => $item->batch_no,
            'unit_cost' => $item->unit_cost,
            'person_in_charge' => $request->person_in_charge,
            'reference_number' => $request->rma_ticket_no,
            'notes' => "RMA Ticket: {$request->rma_ticket_no} | Vendor: {$request->vendor_name} | Defect: {$request->issue_description}" . ($request->notes ? " | Notes: {$request->notes}" : ''),
        ]);

        return redirect()->route('inventory.product', $item->product_id)->with('success', 'Hardware unit logged under RMA / Defective repair.');
    }

    /**
     * Show form to return repaired item to warehouse stock.
     */
    public function returnRmaForm(InventoryItem $item)
    {
        if ($item->status !== 'rma_defective') {
            return redirect()->back()->with('error', 'Item is not currently under RMA.');
        }

        $locations = Location::where('type', '!=', 'client_site')->orderBy('name')->get();
        return view('inventory.return_rma', compact('item', 'locations'));
    }

    /**
     * Process return from RMA repair.
     */
    public function processReturnRma(Request $request, InventoryItem $item)
    {
        $request->validate([
            'location_id' => 'required|exists:locations,id',
            'resolution_notes' => 'required|string|max:1000',
            'person_in_charge' => 'required|string|max:255',
        ]);

        $item->update([
            'status' => 'in_stock',
            'location_id' => $request->location_id,
        ]);

        InventoryTransaction::create([
            'inventory_item_id' => $item->id,
            'user_id' => auth()->id() ?? 1,
            'transaction_type' => 'return_rma',
            'from_location_id' => null,
            'to_location_id' => $request->location_id,
            'from_status' => 'rma_defective',
            'to_status' => 'in_stock',
            'quantity' => $item->quantity,
            'batch_no' => $item->batch_no,
            'unit_cost' => $item->unit_cost,
            'person_in_charge' => $request->person_in_charge,
            'notes' => 'Returned from repair: ' . $request->resolution_notes,
        ]);

        return redirect()->route('inventory.product', $item->product_id)->with('success', 'Repaired hardware received back into warehouse stock.');
    }

    /**
     * Show form to decommission / scrap hardware.
     */
    public function decommissionForm(InventoryItem $item)
    {
        return view('inventory.decommission', compact('item'));
    }

    /**
     * Process asset decommission.
     */
    public function processDecommission(Request $request, InventoryItem $item)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
            'approval_notes' => 'required|string|max:1000',
            'person_in_charge' => 'required|string|max:255',
        ]);

        $oldStatus = $item->status;
        $item->update([
            'status' => 'decommissioned',
        ]);

        InventoryTransaction::create([
            'inventory_item_id' => $item->id,
            'user_id' => auth()->id() ?? 1,
            'transaction_type' => 'decommission',
            'from_location_id' => $item->location_id,
            'to_location_id' => null,
            'from_status' => $oldStatus,
            'to_status' => 'decommissioned',
            'quantity' => $item->quantity,
            'batch_no' => $item->batch_no,
            'unit_cost' => $item->unit_cost,
            'person_in_charge' => $request->person_in_charge,
            'reference_number' => $request->reason,
            'notes' => 'Decommissioned/Scrapped: ' . $request->approval_notes,
        ]);

        return redirect()->route('inventory.product', $item->product_id)->with('success', 'Hardware unit decommissioned and permanently removed from active stock.');
    }

    /**
     * Valuation & Cost Accounting (FIFO, LIFO, Weighted Average & COGS)
     */
    public function valuation(Request $request)
    {
        $products = Product::with(['inventoryItems' => function ($q) {
            $q->orderBy('created_at', 'asc');
        }])->get();

        $rows = [];
        $totalFifo = 0;
        $totalLifo = 0;
        $totalWeightedAvg = 0;
        $totalCogs = 0;
        $totalFleetCatalogValue = 0;

        foreach ($products as $product) {
            $allItems = $product->inventoryItems;
            $inStockItems = $allItems->where('status', 'in_stock');
            $soldItems = $allItems->where('status', 'sold');

            $inStockCount = $inStockItems->count();
            $soldCount = $soldItems->count();

            // Total acquisition cost of in-stock units
            $sumCost = (float) $inStockItems->sum('unit_cost');
            $weightedAvgUnitCost = $inStockCount > 0 ? ($sumCost / $inStockCount) : (float) ($product->unit_price ?? 0);
            $weightedAvgValuation = $sumCost > 0 ? $sumCost : ($inStockCount * (float) ($product->unit_price ?? 0));

            // FIFO: in-stock units are valued at the latest batches received
            $fifoValuation = (float) $inStockItems->sortByDesc('created_at')->sum('unit_cost');
            if ($fifoValuation == 0 && $inStockCount > 0) {
                $fifoValuation = $inStockCount * (float) ($product->unit_price ?? 0);
            }

            // LIFO: in-stock units are valued at the earliest batches received
            $lifoValuation = (float) $inStockItems->sortBy('created_at')->sum('unit_cost');
            if ($lifoValuation == 0 && $inStockCount > 0) {
                $lifoValuation = $inStockCount * (float) ($product->unit_price ?? 0);
            }

            // COGS: acquisition cost of sold units
            $productCogs = (float) $soldItems->sum('unit_cost');
            $catalogValuation = $inStockCount * (float) ($product->unit_price ?? 0);

            $rows[] = [
                'product' => $product,
                'in_stock_count' => $inStockCount,
                'sold_count' => $soldCount,
                'weighted_avg_cost' => $weightedAvgUnitCost,
                'catalog_unit_price' => (float) ($product->unit_price ?? 0),
                'fifo_valuation' => $fifoValuation,
                'lifo_valuation' => $lifoValuation,
                'weighted_avg_valuation' => $weightedAvgValuation,
                'cogs' => $productCogs,
            ];

            $totalFifo += $fifoValuation;
            $totalLifo += $lifoValuation;
            $totalWeightedAvg += $weightedAvgValuation;
            $totalCogs += $productCogs;
            $totalFleetCatalogValue += $catalogValuation;
        }

        // Annual holding / carrying cost estimated at 18% of active warehouse inventory
        $estimatedHoldingCost = $totalWeightedAvg * 0.18;

        return view('inventory.valuation', compact(
            'rows',
            'totalFifo',
            'totalLifo',
            'totalWeightedAvg',
            'totalCogs',
            'totalFleetCatalogValue',
            'estimatedHoldingCost'
        ));
    }

    /**
     * Export valuation report as CSV.
     */
    public function exportValuationCsv()
    {
        $products = Product::with(['inventoryItems'])->get();
        $filename = 'inventory_valuation_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($products) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM

            fputcsv($handle, [
                'SKU',
                'Product Name',
                'Category',
                'Brand',
                'In Stock Qty',
                'Catalog Unit Price',
                'Avg Acquisition Unit Cost',
                'FIFO Valuation',
                'LIFO Valuation',
                'Weighted Avg Valuation',
                'Units Sold',
                'COGS (Sold Inventory Cost)',
            ]);

            foreach ($products as $prod) {
                $inStock = $prod->inventoryItems->where('status', 'in_stock');
                $sold = $prod->inventoryItems->where('status', 'sold');

                $count = $inStock->count();
                $sumCost = (float) $inStock->sum('unit_cost');
                $avgCost = $count > 0 ? ($sumCost / $count) : 0;
                $fifo = $sumCost > 0 ? $sumCost : ($count * (float) $prod->unit_price);
                $lifo = $fifo;
                $cogs = (float) $sold->sum('unit_cost');

                fputcsv($handle, [
                    $prod->sku,
                    $prod->name,
                    $prod->category ?? '',
                    $prod->brand ?? '',
                    $count,
                    number_format((float) $prod->unit_price, 2, '.', ''),
                    number_format($avgCost, 2, '.', ''),
                    number_format($fifo, 2, '.', ''),
                    number_format($lifo, 2, '.', ''),
                    number_format($fifo, 2, '.', ''),
                    $sold->count(),
                    number_format($cogs, 2, '.', ''),
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
