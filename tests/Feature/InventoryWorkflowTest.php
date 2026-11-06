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
        $page->assertSee('<option value="' . $this->product->id . '" selected', false);

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
        $page->assertSee('<option value="' . $this->product->id . '" selected', false);

        // Location remains selected
        $page->assertSee('<option value="' . $this->warehouse->id . '" selected', false);

        // Duplicate serial preserved in table for user to fix
        $page->assertSee('SN-DUP-01');
    }
}
