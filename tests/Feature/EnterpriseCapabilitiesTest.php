<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\Location;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\StockCount;
use App\Models\StockCountItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnterpriseCapabilitiesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $warehouseUser;
    private Product $product;
    private Location $rackA1;
    private Location $rackB2;
    private Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->warehouseUser = User::factory()->warehouse()->create();
        $this->actingAs($this->admin);

        Category::firstOrCreate(['name' => 'Sensors']);

        $this->product = Product::create([
            'sku' => 'SNS-ACC-900',
            'name' => 'High Precision Accelerometer',
            'category' => 'Sensors',
            'brand' => 'Bosch Sensortec',
            'unit_price' => 850.00,
            'min_stock_level' => 5,
        ]);

        $this->rackA1 = Location::create([
            'name' => 'Warehouse Alpha - Rack A1',
            'type' => 'internal',
            'branch' => 'Penang',
            'floor' => 'Level 1',
        ]);

        $this->rackB2 = Location::create([
            'name' => 'Warehouse Alpha - Rack B2',
            'type' => 'internal',
            'branch' => 'Penang',
            'floor' => 'Level 1',
        ]);

        $this->supplier = Supplier::create([
            'name' => 'TechTron Components Sdn Bhd',
            'contact_person' => 'Mr. Tan',
            'email' => 'tan@techtron.com.my',
            'phone' => '+60123456789',
            'address' => 'Penang Tech Park',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Pillar 1: Stock Tracking, Batch Numbers & Internal Transfers
    |--------------------------------------------------------------------------
    */

    public function test_inventory_item_stores_batch_number_and_unit_acquisition_cost(): void
    {
        $response = $this->post('/inventory', [
            'product_id' => $this->product->id,
            'location_id' => $this->rackA1->id,
            'serial_numbers' => ['SNS-LOT1-001', 'SNS-LOT1-002'],
            'batch_no' => 'LOT-2026-X1',
            'unit_cost' => 620.50,
            'notes' => 'Received from manufacturer lot batch',
        ]);

        $response->assertRedirect('/inventory');

        $this->assertDatabaseHas('inventory_items', [
            'serial_number' => 'SNS-LOT1-001',
            'batch_no' => 'LOT-2026-X1',
            'unit_cost' => 620.50,
            'status' => 'in_stock',
        ]);

        $this->assertDatabaseHas('inventory_transactions', [
            'transaction_type' => 'stock_in',
            'batch_no' => 'LOT-2026-X1',
            'unit_cost' => 620.50,
        ]);
    }

    public function test_can_transfer_in_stock_item_between_internal_warehouse_racks(): void
    {
        $item = InventoryItem::create([
            'product_id' => $this->product->id,
            'location_id' => $this->rackA1->id,
            'serial_number' => 'SNS-TRANSFER-01',
            'status' => 'in_stock',
            'batch_no' => 'BATCH-ALPHA',
            'unit_cost' => 700.00,
        ]);

        $response = $this->get('/inventory/' . $item->id . '/transfer');
        $response->assertStatus(200);
        $response->assertSee('Transfer Hardware Location');

        $transferResponse = $this->post('/inventory/' . $item->id . '/transfer', [
            'to_location_id' => $this->rackB2->id,
            'reference_number' => 'TR-2026-081',
            'notes' => 'Relocated to fast-pick bin B2',
        ]);

        $transferResponse->assertRedirect('/inventory/product/' . $this->product->id);

        $this->assertEquals($this->rackB2->id, $item->fresh()->location_id);

        $this->assertDatabaseHas('inventory_transactions', [
            'inventory_item_id' => $item->id,
            'transaction_type' => 'transfer',
            'from_location_id' => $this->rackA1->id,
            'to_location_id' => $this->rackB2->id,
            'reference_number' => 'TR-2026-081',
        ]);
    }

    public function test_cannot_transfer_item_to_the_same_location(): void
    {
        $item = InventoryItem::create([
            'product_id' => $this->product->id,
            'location_id' => $this->rackA1->id,
            'serial_number' => 'SNS-SAMELOC-01',
            'status' => 'in_stock',
        ]);

        $response = $this->post('/inventory/' . $item->id . '/transfer', [
            'to_location_id' => $this->rackA1->id,
        ]);

        $response->assertSessionHasErrors(['to_location_id']);
        $this->assertEquals($this->rackA1->id, $item->fresh()->location_id);
    }

    /*
    |--------------------------------------------------------------------------
    | Pillar 2: Asset Lifecycle & Custody (Reserve, RMA, Decommission)
    |--------------------------------------------------------------------------
    */

    public function test_can_reserve_and_release_inventory_item(): void
    {
        $item = InventoryItem::create([
            'product_id' => $this->product->id,
            'location_id' => $this->rackA1->id,
            'serial_number' => 'SNS-RESERVE-01',
            'status' => 'in_stock',
        ]);

        // Reserve
        $reserveResponse = $this->post('/inventory/' . $item->id . '/reserve', [
            'project_name' => 'Petronas Pilot Staging',
            'person_in_charge' => 'Farid Engineer',
            'notes' => 'Earmarked for upcoming telemetry pilot',
        ]);

        $reserveResponse->assertRedirect('/inventory/product/' . $this->product->id);
        $this->assertEquals('reserved', $item->fresh()->status);

        $this->assertDatabaseHas('inventory_transactions', [
            'inventory_item_id' => $item->id,
            'transaction_type' => 'reserve',
        ]);

        // Release reservation back to stock
        $releaseResponse = $this->post('/inventory/' . $item->id . '/release', [
            'notes' => 'Customer postponed pilot project, releasing to active stock',
        ]);

        $releaseResponse->assertRedirect('/inventory/product/' . $this->product->id);
        $this->assertEquals('in_stock', $item->fresh()->status);

        $this->assertDatabaseHas('inventory_transactions', [
            'inventory_item_id' => $item->id,
            'transaction_type' => 'release_reservation',
        ]);
    }

    public function test_can_send_item_to_rma_and_return_from_repair(): void
    {
        $item = InventoryItem::create([
            'product_id' => $this->product->id,
            'location_id' => $this->rackA1->id,
            'serial_number' => 'SNS-RMA-01',
            'status' => 'in_stock',
        ]);

        // Dispatch to RMA
        $rmaResponse = $this->post('/inventory/' . $item->id . '/rma', [
            'rma_ticket_no' => 'RMA-VENDOR-4491',
            'vendor_name' => 'Bosch Technical Support Center',
            'issue_description' => 'Axis calibration offset beyond tolerance threshold',
            'person_in_charge' => 'Kevin Hardware Tech',
        ]);

        $rmaResponse->assertRedirect('/inventory/product/' . $this->product->id);
        $this->assertEquals('rma_defective', $item->fresh()->status);

        $this->assertDatabaseHas('inventory_transactions', [
            'inventory_item_id' => $item->id,
            'transaction_type' => 'send_rma',
        ]);

        // Return from RMA
        $returnRmaResponse = $this->post('/inventory/' . $item->id . '/rma/return', [
            'location_id' => $this->rackB2->id,
            'resolution_notes' => 'Sensor recalibrated and board firmware flashed to v2.4',
            'person_in_charge' => 'Kevin Hardware Tech',
        ]);

        $returnRmaResponse->assertRedirect('/inventory/product/' . $this->product->id);
        $this->assertEquals('in_stock', $item->fresh()->status);
        $this->assertEquals($this->rackB2->id, $item->fresh()->location_id);

        $this->assertDatabaseHas('inventory_transactions', [
            'inventory_item_id' => $item->id,
            'transaction_type' => 'return_rma',
            'to_location_id' => $this->rackB2->id,
        ]);
    }

    public function test_can_decommission_and_scrap_hardware_asset(): void
    {
        $item = InventoryItem::create([
            'product_id' => $this->product->id,
            'location_id' => $this->rackA1->id,
            'serial_number' => 'SNS-SCRAP-01',
            'status' => 'in_stock',
        ]);

        $response = $this->post('/inventory/' . $item->id . '/decommission', [
            'reason' => 'Total water damage during site flood',
            'approval_notes' => 'Approved by Plant Manager; scrapped for metals recovery',
            'person_in_charge' => 'Kevin Hardware Tech',
        ]);

        $response->assertRedirect('/inventory/product/' . $this->product->id);
        $this->assertEquals('decommissioned', $item->fresh()->status);

        $this->assertDatabaseHas('inventory_transactions', [
            'inventory_item_id' => $item->id,
            'transaction_type' => 'decommission',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Pillar 3: Order & Reorder Management (Suppliers, POs, Auto-Draft)
    |--------------------------------------------------------------------------
    */

    public function test_can_manage_supplier_records(): void
    {
        $response = $this->post('/settings/suppliers', [
            'name' => 'OptoSensor Global',
            'contact_person' => 'Alice Wong',
            'email' => 'alice@optosensor.com',
            'phone' => '+60388992211',
            'address' => 'Bay 4, High-Tech Industrial Zone',
        ]);

        $response->assertRedirect('/settings/suppliers');
        $this->assertDatabaseHas('suppliers', [
            'name' => 'OptoSensor Global',
            'contact_person' => 'Alice Wong',
        ]);
    }

    public function test_can_create_purchase_order_and_receive_goods(): void
    {
        $createResponse = $this->post('/purchase-orders', [
            'supplier_id' => $this->supplier->id,
            'order_date' => now()->toDateString(),
            'expected_delivery_date' => now()->addDays(7)->toDateString(),
            'notes' => 'Q4 sensor replenishment batch',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'unit_cost' => 610.00,
                ],
            ],
        ]);

        $po = PurchaseOrder::latest('id')->firstOrFail();
        $createResponse->assertRedirect('/purchase-orders/' . $po->id);
        $this->assertEquals('draft', $po->status);
        $this->assertEquals(1220.00, (float) $po->total_amount);

        // Receive goods
        $receiveResponse = $this->post('/purchase-orders/' . $po->id . '/receive', [
            'location_id' => $this->rackA1->id,
            'batch_no' => 'LOT-PO-RECEIVED-01',
        ]);

        $receiveResponse->assertRedirect('/purchase-orders/' . $po->id);
        $this->assertEquals('received', $po->fresh()->status);

        // 2 units should have been serialized and added to inventory_items
        $this->assertEquals(2, InventoryItem::where('product_id', $this->product->id)->where('batch_no', 'LOT-PO-RECEIVED-01')->count());

        $this->assertDatabaseHas('inventory_items', [
            'product_id' => $this->product->id,
            'batch_no' => 'LOT-PO-RECEIVED-01',
            'unit_cost' => 610.00,
            'location_id' => $this->rackA1->id,
            'status' => 'in_stock',
        ]);
    }

    public function test_can_auto_draft_replenishment_po_from_low_safety_stock(): void
    {
        // Product has min_stock_level = 5, and currently 0 in stock
        $this->assertEquals(0, $this->product->inventoryItems()->where('status', 'in_stock')->count());

        $response = $this->post('/purchase-orders/auto-draft');
        $response->assertRedirect();

        $latestPo = PurchaseOrder::latest('id')->first();
        $this->assertNotNull($latestPo);
        $this->assertEquals('draft', $latestPo->status);
        $this->assertStringContainsString('Automated replenishment draft', $latestPo->notes);

        $this->assertDatabaseHas('purchase_order_items', [
            'purchase_order_id' => $latestPo->id,
            'product_id' => $this->product->id,
            'quantity_ordered' => 10,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Pillar 4: Valuation & Cost Accounting (FIFO, LIFO, Weighted Avg, COGS)
    |--------------------------------------------------------------------------
    */

    public function test_valuation_dashboard_and_csv_export(): void
    {
        // Create 2 items with distinct costs
        InventoryItem::create([
            'product_id' => $this->product->id,
            'location_id' => $this->rackA1->id,
            'serial_number' => 'SNS-VAL-01',
            'status' => 'in_stock',
            'unit_cost' => 600.00,
            'created_at' => now()->subDays(10),
        ]);

        InventoryItem::create([
            'product_id' => $this->product->id,
            'location_id' => $this->rackA1->id,
            'serial_number' => 'SNS-VAL-02',
            'status' => 'in_stock',
            'unit_cost' => 640.00,
            'created_at' => now()->subDays(2),
        ]);

        // Sold item for COGS
        InventoryItem::create([
            'product_id' => $this->product->id,
            'location_id' => $this->rackA1->id,
            'serial_number' => 'SNS-VAL-SOLD-01',
            'status' => 'sold',
            'unit_cost' => 580.00,
        ]);

        $viewResponse = $this->get('/valuation');
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('FIFO Valuation');
        $viewResponse->assertSee('LIFO Valuation');
        $viewResponse->assertSee('Weighted Avg Value');
        $viewResponse->assertSee('Sold / COGS');

        $exportResponse = $this->get('/valuation/export');
        $exportResponse->assertStatus(200);
        $this->assertStringContainsString('text/csv', $exportResponse->headers->get('Content-Type'));
        $content = $exportResponse->streamedContent();
        $this->assertStringContainsString('SNS-ACC-900', $content);
        $this->assertStringContainsString('High Precision Accelerometer', $content);
    }

    /*
    |--------------------------------------------------------------------------
    | Pillar 5: Cycle Counts, Shelf Scanning & 1-Click Discrepancy Reconciliation
    |--------------------------------------------------------------------------
    */

    public function test_cycle_count_audit_shelf_scanning_and_reconciliation(): void
    {
        // Setup 2 items in rackA1, 1 item in rackB2
        $item1 = InventoryItem::create([
            'product_id' => $this->product->id,
            'location_id' => $this->rackA1->id,
            'serial_number' => 'SNS-AUDIT-MATCHED',
            'status' => 'in_stock',
        ]);

        $item2 = InventoryItem::create([
            'product_id' => $this->product->id,
            'location_id' => $this->rackA1->id,
            'serial_number' => 'SNS-AUDIT-MISSING',
            'status' => 'in_stock',
        ]);

        $item3 = InventoryItem::create([
            'product_id' => $this->product->id,
            'location_id' => $this->rackB2->id, // recorded in rackB2
            'serial_number' => 'SNS-AUDIT-MISPLACED',
            'status' => 'in_stock',
        ]);

        // Start cycle count for rackA1
        $startResponse = $this->post('/stock-counts', [
            'location_id' => $this->rackA1->id,
            'notes' => 'Q4 Routine bin audit of Penang rack A1',
        ]);

        $startResponse->assertRedirect();
        $stockCount = StockCount::where('location_id', $this->rackA1->id)->latest('id')->firstOrFail();
        $this->assertEquals('in_progress', $stockCount->status);

        // Pre-populated manifest should have item1 and item2 marked missing initially
        $this->assertEquals(2, $stockCount->items()->count());

        // Scan item1 -> Should flip to 'matched'
        $scan1Response = $this->post('/stock-counts/' . $stockCount->id . '/scan', [
            'serial_number' => 'SNS-AUDIT-MATCHED',
        ]);
        $scan1Response->assertRedirect('/stock-counts/' . $stockCount->id);
        $this->assertEquals('matched', StockCountItem::where('stock_count_id', $stockCount->id)->where('inventory_item_id', $item1->id)->first()->status);

        // Scan item3 (which was registered in rackB2, found physically in rackA1) -> Should register as 'misplaced'
        $scan3Response = $this->post('/stock-counts/' . $stockCount->id . '/scan', [
            'serial_number' => 'SNS-AUDIT-MISPLACED',
        ]);
        $scan3Response->assertRedirect('/stock-counts/' . $stockCount->id);
        $misplacedItem = StockCountItem::where('stock_count_id', $stockCount->id)->where('inventory_item_id', $item3->id)->first();
        $this->assertNotNull($misplacedItem);
        $this->assertEquals('misplaced', $misplacedItem->status);

        // Discrepancy Reconciliation
        $reconcileResponse = $this->post('/stock-counts/' . $stockCount->id . '/reconcile');
        $reconcileResponse->assertRedirect('/stock-counts/' . $stockCount->id);

        $this->assertEquals('completed', $stockCount->fresh()->status);
        $this->assertNotNull($stockCount->fresh()->completed_at);

        // item1 remains in_stock at rackA1
        $this->assertEquals('in_stock', $item1->fresh()->status);
        $this->assertEquals($this->rackA1->id, $item1->fresh()->location_id);

        // item2 (never scanned) was marked missing -> decommissioned as shrinkage
        $this->assertEquals('decommissioned', $item2->fresh()->status);

        // item3 (misplaced from B2) was physically found in A1 -> auto-relocated to rackA1
        $this->assertEquals($this->rackA1->id, $item3->fresh()->location_id);
        $this->assertEquals('in_stock', $item3->fresh()->status);
    }
}
