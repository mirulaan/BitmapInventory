<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryWorkflowController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\StockCountController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Root Redirect to Dashboard
Route::redirect('/', '/dashboard');

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');

    // Password Reset with Superadmin Code
    Route::get('/reset-password', [AuthController::class, 'showResetPasswordForm'])->name('password.reset_code');
    Route::post('/reset-password', [AuthController::class, 'resetPasswordWithCode'])->name('password.update_with_code');
});

// Authenticated Routes
Route::middleware(['auth', 'role'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard / Command Center (all authenticated roles)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Inventory Asset Management & Workflows
    Route::prefix('inventory')->name('inventory.')->group(function () {
        // Read-only / Overview (all roles)
        Route::get('/', [InventoryWorkflowController::class, 'index'])->name('index');
        Route::get('/product/{product}', [InventoryWorkflowController::class, 'showProduct'])->name('product');

        // Stock In & Item Edit (admin, warehouse)
        Route::middleware('role:admin,warehouse')->group(function () {
            Route::get('/create', [InventoryWorkflowController::class, 'create'])->name('create');
            Route::post('/', [InventoryWorkflowController::class, 'store'])->name('store');
            Route::get('/{item}/edit', [InventoryWorkflowController::class, 'edit'])->name('edit');
            Route::put('/{item}', [InventoryWorkflowController::class, 'update'])->name('update');
            
            // Internal Warehouse Rack-to-Rack Transfer
            Route::get('/{item}/transfer', [InventoryWorkflowController::class, 'transferForm'])->name('transfer');
            Route::post('/{item}/transfer', [InventoryWorkflowController::class, 'processTransfer'])->name('process_transfer');

            // Decommission / Scrap Asset
            Route::get('/{item}/decommission', [InventoryWorkflowController::class, 'decommissionForm'])->name('decommission');
            Route::post('/{item}/decommission', [InventoryWorkflowController::class, 'processDecommission'])->name('process_decommission');
        });

        // Lifecycle & Field Operations (admin, warehouse, engineer)
        Route::middleware('role:admin,warehouse,engineer')->group(function () {
            // Loan Out
            Route::get('/{item}/loan', [InventoryWorkflowController::class, 'loanForm'])->name('loan');
            Route::post('/{item}/loan', [InventoryWorkflowController::class, 'processLoan'])->name('process_loan');

            // Return from Loan
            Route::get('/{item}/return', [InventoryWorkflowController::class, 'returnForm'])->name('return');
            Route::post('/{item}/return', [InventoryWorkflowController::class, 'processReturn'])->name('process_return');

            // Convert Loan to Permanent Sale
            Route::get('/{item}/convert', [InventoryWorkflowController::class, 'convertForm'])->name('convert');
            Route::post('/{item}/convert', [InventoryWorkflowController::class, 'processConvert'])->name('process_convert');

            // Direct Sale
            Route::get('/{item}/sell', [InventoryWorkflowController::class, 'sellForm'])->name('sell');
            Route::post('/{item}/sell', [InventoryWorkflowController::class, 'processSell'])->name('process_sell');

            // Reservation Lifecycle
            Route::get('/{item}/reserve', [InventoryWorkflowController::class, 'reserveForm'])->name('reserve');
            Route::post('/{item}/reserve', [InventoryWorkflowController::class, 'processReserve'])->name('process_reserve');
            Route::post('/{item}/release', [InventoryWorkflowController::class, 'processReleaseReservation'])->name('process_release');

            // RMA & Defective Repair
            Route::get('/{item}/rma', [InventoryWorkflowController::class, 'rmaForm'])->name('rma');
            Route::post('/{item}/rma', [InventoryWorkflowController::class, 'processRma'])->name('process_rma');
            Route::get('/{item}/rma/return', [InventoryWorkflowController::class, 'returnRmaForm'])->name('return_rma');
            Route::post('/{item}/rma/return', [InventoryWorkflowController::class, 'processReturnRma'])->name('process_return_rma');
        });
    });

    // Valuation & Cost Accounting (FIFO, LIFO, Weighted Avg & COGS)
    Route::get('/valuation', [InventoryWorkflowController::class, 'valuation'])->name('inventory.valuation');
    Route::get('/valuation/export', [InventoryWorkflowController::class, 'exportValuationCsv'])->name('inventory.valuation.export');

    // Purchase Orders & Procurement
    Route::get('purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
    Route::middleware('role:admin,warehouse')->group(function () {
        Route::get('purchase-orders/create', [PurchaseOrderController::class, 'create'])->name('purchase-orders.create');
        Route::post('purchase-orders', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
        Route::post('purchase-orders/auto-draft', [PurchaseOrderController::class, 'autoDraftFromLowStock'])->name('purchase-orders.auto_draft');
        Route::post('purchase-orders/{purchaseOrder}/status', [PurchaseOrderController::class, 'updateStatus'])->name('purchase-orders.update_status');
        Route::post('purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
    });
    Route::get('purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('purchase-orders.show');

    // Cycle Counts & Physical Stock Audits
    Route::get('stock-counts', [StockCountController::class, 'index'])->name('stock-counts.index');
    Route::middleware('role:admin,warehouse')->group(function () {
        Route::get('stock-counts/create', [StockCountController::class, 'create'])->name('stock-counts.create');
        Route::post('stock-counts', [StockCountController::class, 'store'])->name('stock-counts.store');
        Route::post('stock-counts/{stockCount}/scan', [StockCountController::class, 'scan'])->name('stock-counts.scan');
        Route::post('stock-counts/{stockCount}/reconcile', [StockCountController::class, 'reconcile'])->name('stock-counts.reconcile');
    });
    Route::get('stock-counts/{stockCount}', [StockCountController::class, 'show'])->name('stock-counts.show');

    // Audit & Ledger Trail (all roles can view & export)
    Route::get('/audit', [InventoryWorkflowController::class, 'audit'])->name('inventory.audit');
    Route::get('/audit/export', [InventoryWorkflowController::class, 'exportAuditCsv'])->name('inventory.audit.export');

    // Product Catalog
    Route::get('products', [ProductController::class, 'index'])->name('products.index');
    Route::middleware('role:admin,warehouse')->group(function () {
        Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('products', [ProductController::class, 'store'])->name('products.store');
        Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    });

    // Settings & System Configurations
    Route::prefix('settings')->group(function () {
        // Categories (View all, mutations admin/warehouse)
        Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::middleware('role:admin,warehouse')->group(function () {
            Route::get('categories/create', [CategoryController::class, 'create'])->name('categories.create');
            Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
            Route::get('categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
            Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
            Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        });

        // Brands (View all, mutations admin/warehouse)
        Route::get('brands', [BrandController::class, 'index'])->name('brands.index');
        Route::middleware('role:admin,warehouse')->group(function () {
            Route::get('brands/create', [BrandController::class, 'create'])->name('brands.create');
            Route::post('brands', [BrandController::class, 'store'])->name('brands.store');
            Route::get('brands/{brand}/edit', [BrandController::class, 'edit'])->name('brands.edit');
            Route::put('brands/{brand}', [BrandController::class, 'update'])->name('brands.update');
            Route::delete('brands/{brand}', [BrandController::class, 'destroy'])->name('brands.destroy');
        });

        // Locations (View all, mutations admin/warehouse)
        Route::get('locations', [LocationController::class, 'index'])->name('locations.index');
        Route::middleware('role:admin,warehouse')->group(function () {
            Route::get('locations/create', [LocationController::class, 'create'])->name('locations.create');
            Route::post('locations', [LocationController::class, 'store'])->name('locations.store');
            Route::get('locations/{location}/edit', [LocationController::class, 'edit'])->name('locations.edit');
            Route::put('locations/{location}', [LocationController::class, 'update'])->name('locations.update');
            Route::delete('locations/{location}', [LocationController::class, 'destroy'])->name('locations.destroy');
        });

        // Clients (View all, mutations admin/warehouse/engineer)
        Route::get('clients', [ClientController::class, 'index'])->name('clients.index');
        Route::middleware('role:admin,warehouse,engineer')->group(function () {
            Route::get('clients/create', [ClientController::class, 'create'])->name('clients.create');
            Route::post('clients', [ClientController::class, 'store'])->name('clients.store');
            Route::get('clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
            Route::put('clients/{client}', [ClientController::class, 'update'])->name('clients.update');
            Route::delete('clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
        });

        // Suppliers (View all, mutations admin/warehouse)
        Route::get('suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
        Route::middleware('role:admin,warehouse')->group(function () {
            Route::get('suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
            Route::post('suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
            Route::get('suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
            Route::put('suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
            Route::delete('suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
        });

        // User Accounts & Password Reset Codes (Super Admin only)
        Route::middleware('role:admin')->group(function () {
            Route::resource('users', UserController::class)->except(['show']);
            Route::post('users/{user}/generate-reset-code', [UserController::class, 'generateResetCode'])->name('users.generate_reset_code');
            Route::post('users/{user}/revoke-reset-code', [UserController::class, 'revokeResetCode'])->name('users.revoke_reset_code');
            Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle_status');
        });
    });

    // Backward-compatible redirects
    Route::redirect('/locations', '/settings/locations');
    Route::redirect('/clients', '/settings/clients');
    Route::redirect('/suppliers', '/settings/suppliers');
    Route::redirect('/users', '/settings/users');
    Route::redirect('/brands', '/settings/brands');
});
