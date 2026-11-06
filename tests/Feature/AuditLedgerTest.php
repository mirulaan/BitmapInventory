<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Client;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\Location;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLedgerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Product $product;
    private Location $warehouse;
    private Location $clientSite;
    private Client $client;
    private InventoryItem $item;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['name' => 'John Auditor']);
        $this->actingAs($this->user);

        Category::firstOrCreate(['name' => 'Cameras']);

        $this->product = Product::create([
            'sku' => 'CAM-BAS-AUDIT',
            'name' => 'Basler Dart Audit Model',
            'brand' => 'Basler',
            'category' => 'Cameras',
            'unit_price' => 1200.00,
        ]);

        $this->warehouse = Location::create([
            'name' => 'Rack A1',
            'type' => 'internal',
            'branch' => 'KL',
            'floor' => 'Floor 1',
        ]);

        $this->clientSite = Location::create([
            'name' => 'ViTrox Tech Line 1',
            'type' => 'client_site',
        ]);

        $this->client = Client::create([
            'company_name' => 'ViTrox Technologies',
            'contact_person' => 'Mr. Tan',
        ]);

        $this->item = InventoryItem::create([
            'product_id' => $this->product->id,
            'location_id' => $this->warehouse->id,
            'serial_number' => 'SN-AUDIT-999',
            'status' => 'in_stock',
        ]);
    }

    public function test_audit_page_renders_with_metrics_and_ledger_logs(): void
    {
        InventoryTransaction::create([
            'inventory_item_id' => $this->item->id,
            'user_id' => $this->user->id,
            'transaction_type' => 'stock_in',
            'to_location_id' => $this->warehouse->id,
            'to_status' => 'in_stock',
            'quantity' => 1,
            'reference_number' => 'PO-SUPP-1234',
        ]);

        $response = $this->get(route('inventory.audit'));

        $response->assertStatus(200);
        $response->assertSee('Basler Dart Audit Model');
        $response->assertSee('SN-AUDIT-999');
        $response->assertSee('Stock In');
    }

    public function test_can_filter_audit_by_transaction_type(): void
    {
        // 1. Stock In Log
        InventoryTransaction::create([
            'inventory_item_id' => $this->item->id,
            'user_id' => $this->user->id,
            'transaction_type' => 'stock_in',
            'to_location_id' => $this->warehouse->id,
            'to_status' => 'in_stock',
            'quantity' => 1,
            'reference_number' => 'RECEIPT-001',
        ]);

        // 2. Dispatch Loan Log
        InventoryTransaction::create([
            'inventory_item_id' => $this->item->id,
            'user_id' => $this->user->id,
            'transaction_type' => 'dispatch_loan',
            'from_location_id' => $this->warehouse->id,
            'to_location_id' => $this->clientSite->id,
            'from_status' => 'in_stock',
            'to_status' => 'on_loan',
            'quantity' => 1,
            'loan_form_no' => 'LF-2026-FILTER-TEST',
            'person_in_charge' => 'Engineer Alex',
        ]);

        $response = $this->get(route('inventory.audit', ['transaction_type' => 'dispatch_loan']));

        $response->assertStatus(200);
        $response->assertSee('LF-2026-FILTER-TEST');
        $response->assertSee('Engineer Alex');
        $response->assertDontSee('RECEIPT-001');
    }

    public function test_can_search_audit_across_model_brand_and_loan_form(): void
    {
        InventoryTransaction::create([
            'inventory_item_id' => $this->item->id,
            'user_id' => $this->user->id,
            'transaction_type' => 'dispatch_loan',
            'from_location_id' => $this->warehouse->id,
            'to_location_id' => $this->clientSite->id,
            'from_status' => 'in_stock',
            'to_status' => 'on_loan',
            'quantity' => 1,
            'loan_form_no' => 'LF-SEARCH-TARGET',
            'po_customer' => 'PO-CUST-VITROX-77',
        ]);

        // Search by Loan Form No
        $resForm = $this->get(route('inventory.audit', ['search' => 'LF-SEARCH-TARGET']));
        $resForm->assertStatus(200);
        $resForm->assertSee('PO-CUST-VITROX-77');

        // Search by Product Name
        $resProd = $this->get(route('inventory.audit', ['search' => 'Basler Dart']));
        $resProd->assertStatus(200);
        $resProd->assertSee('SN-AUDIT-999');
    }

    public function test_can_export_audit_ledger_as_csv(): void
    {
        InventoryTransaction::create([
            'inventory_item_id' => $this->item->id,
            'user_id' => $this->user->id,
            'transaction_type' => 'dispatch_loan',
            'from_location_id' => $this->warehouse->id,
            'to_location_id' => $this->clientSite->id,
            'from_status' => 'in_stock',
            'to_status' => 'on_loan',
            'quantity' => 1,
            'loan_form_no' => 'LF-CSV-EXPORT',
            'po_customer' => 'PO-CSV-001',
            'person_in_charge' => 'David Lee',
        ]);

        $response = $this->get(route('inventory.audit.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        // Capture streamed response content
        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $this->assertStringContainsString('Log ID', $content);
        $this->assertStringContainsString('Serial Number', $content);
        $this->assertStringContainsString('SN-AUDIT-999', $content);
        $this->assertStringContainsString('LF-CSV-EXPORT', $content);
        $this->assertStringContainsString('PO-CSV-001', $content);
        $this->assertStringContainsString('David Lee', $content);
    }
}
