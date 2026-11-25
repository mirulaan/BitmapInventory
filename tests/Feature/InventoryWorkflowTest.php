<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Client;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\Location;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Product $product;

    private Location $warehouse;

    private Location $clientSite;

    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        Category::firstOrCreate(['name' => 'Camera']);

        $this->product = Product::create([
            'sku' => 'CAM-TEST-001',
            'name' => 'Basler Ace Test',
            'category' => 'Camera',
            'unit_price' => 1500.00,
        ]);

        $this->warehouse = Location::create([
            'name' => 'Rack A1',
            'type' => 'internal',
            'branch' => 'KL',
            'floor' => 'Floor 1',
        ]);

        $this->clientSite = Location::create([
            'name' => 'Factory Line 1',
            'type' => 'client_site',
        ]);

        $this->client = Client::create([
            'company_name' => 'Acme Robotics',
            'contact_person' => 'Alice Wonderland',
            'contact_no' => '+60123456789',
            'email' => 'alice@acme.com',
        ]);
    }

    public function test_user_can_view_inventory_overview(): void
    {
        $response = $this->get(route('inventory.index'));

        $response->assertStatus(200);
        $response->assertSee('Basler Ace Test');
    }

    public function test_inventory_create_page_renders_warehouse_rack_options(): void
    {
        $response = $this->get(route('inventory.create'));

        $response->assertStatus(200);
        $response->assertSee($this->warehouse->name);
        $response->assertSee('Receiving Warehouse Rack');
    }

    public function test_user_can_bulk_receive_serialized_hardware(): void
    {
        $payload = [
            'product_id' => $this->product->id,
            'location_id' => $this->warehouse->id,
            'serial_numbers' => ['SN-AUTO-001', 'SN-AUTO-002', 'SN-AUTO-003'],
        ];

        $response = $this->post(route('inventory.store'), $payload);

        $response->assertRedirect(route('inventory.index'));
        $this->assertDatabaseCount('inventory_items', 3);
        $this->assertDatabaseHas('inventory_items', [
            'serial_number' => 'SN-AUTO-001',
            'status' => 'in_stock',
            'location_id' => $this->warehouse->id,
        ]);
        $this->assertDatabaseCount('inventory_transactions', 3);
        $this->assertDatabaseHas('inventory_transactions', [
            'transaction_type' => 'stock_in',
            'to_location_id' => $this->warehouse->id,
            'to_status' => 'in_stock',
        ]);
    }

    public function test_user_can_dispatch_item_on_loan(): void
    {
        $item = InventoryItem::create([
            'product_id' => $this->product->id,
            'serial_number' => 'SN-LOAN-100',
            'quantity' => 1,
            'location_id' => $this->warehouse->id,
            'status' => 'in_stock',
        ]);

        $payload = [
            'client_id' => $this->client->id,
            'location_id' => $this->clientSite->id,
            'person_in_charge' => 'Bob Engineer',
            'loan_form_no' => 'LF-2026-999',
            'loan_start_date' => Carbon::now()->toDateString(),
            'loan_end_date' => Carbon::now()->addDays(30)->toDateString(),
            'notes' => 'Dispatched for PoC testing',
        ];

        $response = $this->post(route('inventory.process_loan', $item->id), $payload);

        $response->assertRedirect(route('inventory.index'));

        $item->refresh();
        $this->assertEquals('on_loan', $item->status);
        $this->assertEquals($this->client->id, $item->client_id);
        $this->assertEquals($this->clientSite->id, $item->location_id);

        $this->assertDatabaseHas('inventory_transactions', [
            'inventory_item_id' => $item->id,
            'transaction_type' => 'dispatch_loan',
            'from_location_id' => $this->warehouse->id,
            'to_location_id' => $this->clientSite->id,
            'from_status' => 'in_stock',
            'to_status' => 'on_loan',
            'loan_form_no' => 'LF-2026-999',
            'person_in_charge' => 'Bob Engineer',
        ]);
    }

    public function test_user_can_return_item_from_loan_back_to_warehouse(): void
    {
        $item = InventoryItem::create([
            'product_id' => $this->product->id,
            'serial_number' => 'SN-RET-200',
            'quantity' => 1,
            'location_id' => $this->clientSite->id,
            'client_id' => $this->client->id,
            'status' => 'on_loan',
        ]);

        $payload = [
            'location_id' => $this->warehouse->id,
            'notes' => 'Returned safely from client site',
        ];

        $response = $this->post(route('inventory.process_return', $item->id), $payload);

        $response->assertRedirect(route('inventory.index'));

        $item->refresh();
        $this->assertEquals('in_stock', $item->status);
        $this->assertNull($item->client_id);
        $this->assertEquals($this->warehouse->id, $item->location_id);

        $this->assertDatabaseHas('inventory_transactions', [
            'inventory_item_id' => $item->id,
            'transaction_type' => 'loan_return',
            'from_location_id' => $this->clientSite->id,
            'to_location_id' => $this->warehouse->id,
            'from_status' => 'on_loan',
            'to_status' => 'in_stock',
        ]);
    }

    public function test_user_can_convert_loan_to_permanent_sale(): void
    {
        $item = InventoryItem::create([
            'product_id' => $this->product->id,
            'serial_number' => 'SN-CONV-300',
            'quantity' => 1,
            'location_id' => $this->clientSite->id,
            'client_id' => $this->client->id,
            'status' => 'on_loan',
        ]);

        // Prior dispatch transaction
        InventoryTransaction::create([
            'inventory_item_id' => $item->id,
            'user_id' => $this->user->id,
            'transaction_type' => 'dispatch_loan',
            'from_location_id' => $this->warehouse->id,
            'to_location_id' => $this->clientSite->id,
            'from_status' => 'in_stock',
            'to_status' => 'on_loan',
            'quantity' => 1,
            'person_in_charge' => 'Bob Engineer',
            'loan_form_no' => 'LF-2026-CONV',
        ]);

        $payload = [
            'person_in_charge' => 'Sales Lead',
            'po_customer' => 'PO-CUST-888',
            'po_supplier' => 'PO-SUPP-888',
            'notes' => 'Client approved purchase after successful PoC',
        ];

        $response = $this->post(route('inventory.process_convert', $item->id), $payload);

        $response->assertRedirect(route('inventory.index'));

        $item->refresh();
        $this->assertEquals('sold', $item->status);

        $this->assertDatabaseHas('inventory_transactions', [
            'inventory_item_id' => $item->id,
            'transaction_type' => 'convert_to_sale',
            'from_status' => 'on_loan',
            'to_status' => 'sold',
            'loan_form_no' => 'LF-2026-CONV',
            'po_customer' => 'PO-CUST-888',
            'po_supplier' => 'PO-SUPP-888',
        ]);
    }

    public function test_user_can_sell_item_directly(): void
    {
        $item = InventoryItem::create([
            'product_id' => $this->product->id,
            'serial_number' => 'SN-SELL-400',
            'quantity' => 1,
            'location_id' => $this->warehouse->id,
            'status' => 'in_stock',
        ]);

        $payload = [
            'client_id' => $this->client->id,
            'location_id' => $this->clientSite->id,
            'person_in_charge' => 'Direct Sales Rep',
            'po_customer' => 'PO-CUST-999',
            'po_supplier' => 'PO-SUPP-999',
            'notes' => 'Direct warehouse sale',
        ];

        $response = $this->post(route('inventory.process_sell', $item->id), $payload);

        $response->assertRedirect(route('inventory.index'));

        $item->refresh();
        $this->assertEquals('sold', $item->status);
        $this->assertEquals($this->client->id, $item->client_id);
        $this->assertEquals($this->clientSite->id, $item->location_id);

        $this->assertDatabaseHas('inventory_transactions', [
            'inventory_item_id' => $item->id,
            'transaction_type' => 'direct_sale',
            'from_location_id' => $this->warehouse->id,
            'to_location_id' => $this->clientSite->id,
            'from_status' => 'in_stock',
            'to_status' => 'sold',
            'po_customer' => 'PO-CUST-999',
            'po_supplier' => 'PO-SUPP-999',
        ]);
    }

    public function test_user_can_view_audit_ledger(): void
    {
        $item = InventoryItem::create([
            'product_id' => $this->product->id,
            'serial_number' => 'SN-AUDIT-500',
            'quantity' => 1,
            'location_id' => $this->warehouse->id,
            'status' => 'in_stock',
        ]);

        InventoryTransaction::create([
            'inventory_item_id' => $item->id,
            'user_id' => $this->user->id,
            'transaction_type' => 'stock_in',
            'to_location_id' => $this->warehouse->id,
            'to_status' => 'in_stock',
            'quantity' => 1,
            'notes' => 'Initial stock receiving',
        ]);

        $response = $this->get(route('inventory.audit'));

        $response->assertStatus(200);
        $response->assertSee('SN-AUDIT-500');
        $response->assertSee('Stock In');
    }

    public function test_inventory_create_retains_category_product_location_and_serials_on_validation_failure(): void
    {
        $response = $this->from(route('inventory.create'))->post(route('inventory.store'), [
            'category' => 'Camera',
            'product_id' => $this->product->id,
            'location_id' => '', // missing location triggers validation error
            'serial_numbers' => ['SN-RETAIN-001', 'SN-RETAIN-002'],
        ]);

        $response->assertRedirect(route('inventory.create'));
        $response->assertSessionHasErrors('location_id');

        $page = $this->get(route('inventory.create'));
        $page->assertStatus(200);

        // Verify Category remains selected
        $page->assertSee('<option value="Camera" selected>', false);

        // Verify Product remains selected
        $page->assertSee('<option value="'.$this->product->id.'" selected', false);

        // Verify Serial Numbers and quantity are preserved
        $page->assertSee('SN-RETAIN-001');
        $page->assertSee('SN-RETAIN-002');
        $page->assertSee('value="2"', false);
    }

    public function test_inventory_create_infers_category_from_product_when_category_was_not_explicitly_sent(): void
    {
        $response = $this->from(route('inventory.create'))->post(route('inventory.store'), [
            'category' => '',
            'product_id' => $this->product->id,
            'location_id' => $this->warehouse->id,
            'serial_numbers' => ['SN-DUP-01', 'SN-DUP-01'], // duplicate serial triggers validation error
        ]);

        $response->assertRedirect(route('inventory.create'));
        $response->assertSessionHasErrors();

        $page = $this->get(route('inventory.create'));
        $page->assertStatus(200);

        // Category inferred from product and selected
        $page->assertSee('<option value="Camera" selected>', false);

        // Product remains selected
        $page->assertSee('<option value="'.$this->product->id.'" selected', false);

        // Location remains selected
        $page->assertSee('<option value="'.$this->warehouse->id.'" selected', false);

        // Duplicate serial preserved in table for user to fix
        $page->assertSee('SN-DUP-01');
    }

    public function test_dashboard_displays_clickable_loan_status_and_extend_button(): void
    {
        $item = InventoryItem::create([
            'product_id' => $this->product->id,
            'serial_number' => 'SN-DASH-LOAN-01',
            'quantity' => 1,
            'location_id' => $this->clientSite->id,
            'client_id' => $this->client->id,
            'status' => 'on_loan',
        ]);

        InventoryTransaction::create([
            'inventory_item_id' => $item->id,
            'user_id' => $this->user->id,
            'transaction_type' => 'dispatch_loan',
            'from_location_id' => $this->warehouse->id,
            'to_location_id' => $this->clientSite->id,
            'from_status' => 'in_stock',
            'to_status' => 'on_loan',
            'quantity' => 1,
            'person_in_charge' => 'Alice',
            'loan_form_no' => 'LF-DASH-01',
            'loan_start_date' => Carbon::now()->subDays(5)->toDateString(),
            'loan_end_date' => Carbon::now()->addDays(10)->toDateString(),
        ]);

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('SN-DASH-LOAN-01');
        $response->assertSee('openLoanModal(this, \'status\')', false);
        $response->assertSee('openLoanModal(this, \'extend\')', false);
        $response->assertSee('openLoanModal(this, \'return\')', false);
        $response->assertSee('openLoanModal(this, \'convert\')', false);
        $response->assertSee('loan-manage-modal');
    }

    public function test_user_can_extend_active_loan_from_dashboard(): void
    {
        $item = InventoryItem::create([
            'product_id' => $this->product->id,
            'serial_number' => 'SN-EXT-01',
            'quantity' => 1,
            'location_id' => $this->clientSite->id,
            'client_id' => $this->client->id,
            'status' => 'on_loan',
        ]);

        $loanTx = InventoryTransaction::create([
            'inventory_item_id' => $item->id,
            'user_id' => $this->user->id,
            'transaction_type' => 'dispatch_loan',
            'from_location_id' => $this->warehouse->id,
            'to_location_id' => $this->clientSite->id,
            'from_status' => 'in_stock',
            'to_status' => 'on_loan',
            'quantity' => 1,
            'person_in_charge' => 'Bob',
            'loan_form_no' => 'LF-EXT-01',
            'loan_start_date' => Carbon::now()->subDays(10)->toDateString(),
            'loan_end_date' => Carbon::now()->subDay()->toDateString(),
        ]);

        $newEndDate = Carbon::now()->addDays(21)->toDateString();

        $response = $this->from(route('dashboard'))
            ->post(route('inventory.process_extend_loan', $item->id), [
                'new_end_date' => $newEndDate,
                'notes' => 'Client requested 3-week PoC extension',
            ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        $loanTx->refresh();
        $this->assertEquals($newEndDate, Carbon::parse($loanTx->loan_end_date)->toDateString());

        $this->assertDatabaseHas('inventory_transactions', [
            'inventory_item_id' => $item->id,
            'transaction_type' => 'extend_loan',
            'from_status' => 'on_loan',
            'to_status' => 'on_loan',
            'loan_form_no' => 'LF-EXT-01',
        ]);
    }

    public function test_extend_loan_rejects_past_due_date(): void
    {
        $item = InventoryItem::create([
            'product_id' => $this->product->id,
            'serial_number' => 'SN-EXT-PAST',
            'quantity' => 1,
            'location_id' => $this->clientSite->id,
            'client_id' => $this->client->id,
            'status' => 'on_loan',
        ]);

        $response = $this->from(route('dashboard'))
            ->post(route('inventory.process_extend_loan', $item->id), [
                'new_end_date' => Carbon::now()->subDays(2)->toDateString(),
            ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHasErrors('new_end_date');
    }

    public function test_user_can_modify_loan_status_from_dashboard(): void
    {
        $returnItem = InventoryItem::create([
            'product_id' => $this->product->id,
            'serial_number' => 'SN-MOD-RET',
            'quantity' => 1,
            'location_id' => $this->clientSite->id,
            'client_id' => $this->client->id,
            'status' => 'on_loan',
        ]);

        $response = $this->from(route('dashboard'))
            ->post(route('inventory.process_loan_status', $returnItem->id), [
                'target_status' => 'in_stock',
                'location_id' => $this->warehouse->id,
                'notes' => 'Returned via dashboard modal',
            ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        $returnItem->refresh();
        $this->assertEquals('in_stock', $returnItem->status);
        $this->assertEquals($this->warehouse->id, $returnItem->location_id);
        $this->assertNull($returnItem->client_id);

        $this->assertDatabaseHas('inventory_transactions', [
            'inventory_item_id' => $returnItem->id,
            'transaction_type' => 'return_loan',
            'from_status' => 'on_loan',
            'to_status' => 'in_stock',
        ]);

        $rmaItem = InventoryItem::create([
            'product_id' => $this->product->id,
            'serial_number' => 'SN-MOD-RMA',
            'quantity' => 1,
            'location_id' => $this->clientSite->id,
            'client_id' => $this->client->id,
            'status' => 'on_loan',
        ]);

        $rmaResponse = $this->from(route('dashboard'))
            ->post(route('inventory.process_loan_status', $rmaItem->id), [
                'target_status' => 'rma_defective',
                'rma_ticket_no' => 'RMA-2026-777',
                'vendor_name' => 'Basler Support',
                'person_in_charge' => 'Charlie Tech',
                'issue_description' => 'Sensor fault during PoC',
            ]);

        $rmaResponse->assertRedirect(route('dashboard'));
        $rmaResponse->assertSessionHas('success');

        $rmaItem->refresh();
        $this->assertEquals('rma_defective', $rmaItem->status);
        $this->assertDatabaseHas('inventory_transactions', [
            'inventory_item_id' => $rmaItem->id,
            'transaction_type' => 'send_rma',
            'from_status' => 'on_loan',
            'to_status' => 'rma_defective',
            'reference_number' => 'RMA-2026-777',
        ]);
    }

    public function test_user_can_receive_and_issue_bulk_in_house_consumables_for_projects(): void
    {
        $bulkProduct = Product::create([
            'sku' => 'SCR-M4-001',
            'name' => 'M4 Stainless Screw',
            'category' => 'Camera',
            'tracking_type' => 'bulk',
            'unit_price' => 0.50,
        ]);

        // Receive 100 units without serial numbers
        $receive1 = $this->post(route('inventory.store'), [
            'product_id' => $bulkProduct->id,
            'location_id' => $this->warehouse->id,
            'quantity' => 100,
            'notes' => 'Initial box of 100 screws',
        ]);

        $receive1->assertRedirect(route('inventory.product', $bulkProduct->id));

        // Receive 50 more at the same rack -> should increment existing row to 150
        $this->post(route('inventory.store'), [
            'product_id' => $bulkProduct->id,
            'location_id' => $this->warehouse->id,
            'quantity' => 50,
        ]);

        $bulkItem = InventoryItem::where('product_id', $bulkProduct->id)->first();
        $this->assertNotNull($bulkItem);
        $this->assertNull($bulkItem->serial_number);
        $this->assertEquals(150, $bulkItem->quantity);

        // Product view shows Issue to Project (Out) and hides Loan Out / Sell / Transfer
        $productPage = $this->get(route('inventory.product', $bulkProduct->id));
        $productPage->assertStatus(200);
        $productPage->assertSee('Issue to Project (Out)');
        $productPage->assertDontSee('Loan Out');

        // Cannot issue more than available stock
        $overIssue = $this->from(route('inventory.product', $bulkProduct->id))
            ->post(route('inventory.process_issue_bulk', $bulkItem->id), [
                'quantity' => 200,
                'project_name' => 'Line 3 Retrofit',
                'person_in_charge' => 'Technician A',
            ]);
        $overIssue->assertSessionHasErrors('quantity');

        // Issue 25 units to a project
        $issueResponse = $this->post(route('inventory.process_issue_bulk', $bulkItem->id), [
            'quantity' => 25,
            'project_name' => 'Line 3 Retrofit',
            'person_in_charge' => 'Technician A',
            'notes' => 'Used for bracket assembly',
        ]);

        $issueResponse->assertRedirect(route('inventory.product', $bulkProduct->id));
        $bulkItem->refresh();
        $this->assertEquals(125, $bulkItem->quantity);

        $this->assertDatabaseHas('inventory_transactions', [
            'inventory_item_id' => $bulkItem->id,
            'transaction_type' => 'bulk_out',
            'from_location_id' => $this->warehouse->id,
            'from_status' => 'in_stock',
            'to_status' => 'in_house_used',
            'quantity' => 25,
            'reference_number' => 'Line 3 Retrofit',
            'person_in_charge' => 'Technician A',
        ]);
    }
}
