<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Client;
use App\Models\Location;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_settings_pages_render_under_settings_prefix(): void
    {
        $catResponse = $this->get(route('categories.index'));
        $catResponse->assertStatus(200);
        $this->assertEquals(url('/settings/categories'), url()->current());

        $brandResponse = $this->get(route('brands.index'));
        $brandResponse->assertStatus(200);
        $this->assertEquals(url('/settings/brands'), url()->current());

        $locResponse = $this->get(route('locations.index'));
        $locResponse->assertStatus(200);
        $this->assertEquals(url('/settings/locations'), url()->current());

        $clientResponse = $this->get(route('clients.index'));
        $clientResponse->assertStatus(200);
        $this->assertEquals(url('/settings/clients'), url()->current());
    }

    public function test_legacy_routes_redirect_to_settings(): void
    {
        $locRedirect = $this->get('/locations');
        $locRedirect->assertRedirect('/settings/locations');

        $clientRedirect = $this->get('/clients');
        $clientRedirect->assertRedirect('/settings/clients');

        $brandRedirect = $this->get('/brands');
        $brandRedirect->assertRedirect('/settings/brands');
    }

    public function test_can_create_and_rename_brand_with_cascade(): void
    {
        $response = $this->post(route('brands.store'), [
            'name' => 'Basler AG',
        ]);
        $response->assertRedirect(route('brands.index'));

        $this->assertDatabaseHas('brands', ['name' => 'Basler AG']);

        $brand = Brand::where('name', 'Basler AG')->firstOrFail();

        // Create product using this brand
        $product = Product::create([
            'sku' => 'CAM-BAS-BRAND',
            'name' => 'Basler Ace 2 Pro',
            'brand' => 'Basler AG',
            'category' => 'Camera',
        ]);

        // Rename Brand
        $updateResponse = $this->put(route('brands.update', $brand->id), [
            'name' => 'Basler Vision',
        ]);
        $updateResponse->assertRedirect(route('brands.index'));

        $this->assertDatabaseHas('brands', ['name' => 'Basler Vision']);
        $product->refresh();
        $this->assertEquals('Basler Vision', $product->brand);

        // Deleting in-use brand should be prevented
        $deleteResponse = $this->delete(route('brands.destroy', $brand->id));
        $deleteResponse->assertSessionHas('error');
        $this->assertDatabaseHas('brands', ['name' => 'Basler Vision']);
    }

    public function test_can_create_and_rename_category_with_cascade(): void
    {
        $response = $this->post(route('categories.store'), [
            'name' => 'Vision Sensors',
        ]);
        $response->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories', ['name' => 'Vision Sensors']);

        $category = Category::where('name', 'Vision Sensors')->firstOrFail();

        // Create product using this category
        $product = Product::create([
            'sku' => 'CAM-SENS-01',
            'name' => 'Optic Sensor V1',
            'category' => 'Vision Sensors',
        ]);

        // Rename Category
        $updateResponse = $this->put(route('categories.update', $category->id), [
            'name' => 'Smart Sensors',
        ]);
        $updateResponse->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories', ['name' => 'Smart Sensors']);
        $product->refresh();
        $this->assertEquals('Smart Sensors', $product->category);
    }

    public function test_can_create_internal_rack_and_client_site(): void
    {
        // Internal Rack
        $internalResp = $this->post(route('locations.store'), [
            'creation_mode' => 'internal',
            'branch' => 'Penang',
            'floor' => 'Floor 2',
            'rack_name' => 'Rack B2 - Lighting',
        ]);
        $internalResp->assertRedirect(route('locations.index'));

        $this->assertDatabaseHas('locations', [
            'type' => 'internal',
            'branch' => 'Penang',
            'floor' => 'Floor 2',
            'name' => 'Rack B2 - Lighting',
        ]);

        // Client Site
        $clientSiteResp = $this->post(route('locations.store'), [
            'creation_mode' => 'client_site',
            'client_site_name' => 'Intel Penang Automation Lab',
        ]);
        $clientSiteResp->assertRedirect(route('locations.index'));

        $this->assertDatabaseHas('locations', [
            'type' => 'client_site',
            'name' => 'Intel Penang Automation Lab',
        ]);
    }

    public function test_can_create_update_and_search_clients(): void
    {
        $resp = $this->post(route('clients.store'), [
            'company_name' => 'Jabil Circuit Malaysia',
            'contact_person' => 'Tan Kok Keong',
            'contact_no' => '+60 4-644 1234',
            'email' => 'tan_kk@jabil.com',
        ]);
        $resp->assertRedirect(route('clients.index'));

        $this->assertDatabaseHas('clients', [
            'company_name' => 'Jabil Circuit Malaysia',
            'contact_person' => 'Tan Kok Keong',
        ]);

        $client = Client::where('company_name', 'Jabil Circuit Malaysia')->firstOrFail();

        // Update Client
        $updateResp = $this->put(route('clients.update', $client->id), [
            'company_name' => 'Jabil Circuit Malaysia Sdn Bhd',
            'contact_person' => 'Tan Kok Keong',
            'contact_no' => '+60 4-644 5678',
            'email' => 'tan_kk@jabil.com',
        ]);
        $updateResp->assertRedirect(route('clients.index'));

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'company_name' => 'Jabil Circuit Malaysia Sdn Bhd',
        ]);

        // Search Client
        $searchResp = $this->get(route('clients.index', ['search' => 'Jabil']));
        $searchResp->assertStatus(200);
        $searchResp->assertSee('Jabil Circuit Malaysia Sdn Bhd');
    }
}
