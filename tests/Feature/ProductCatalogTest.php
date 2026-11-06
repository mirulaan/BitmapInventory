<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\InventoryItem;
use App\Models\Location;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductCatalogTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        Category::firstOrCreate(['name' => 'Industrial Cameras']);
    }

    /**
     * Generate a valid PNG UploadedFile without relying on PHP's GD extension.
     */
    private function createFakePng(string $filename = 'camera.png', int $sizeKilobytes = 50): UploadedFile
    {
        $pngBase = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        $targetBytes = $sizeKilobytes * 1024;
        $padding = $targetBytes > strlen($pngBase) ? str_repeat('0', $targetBytes - strlen($pngBase)) : '';

        return UploadedFile::fake()->createWithContent($filename, $pngBase . $padding);
    }

    public function test_user_can_view_product_catalog_index(): void
    {
        Product::create([
            'sku' => 'CAM-CAT-001',
            'name' => 'Basler Dart Vision',
            'category' => 'Industrial Cameras',
            'unit_price' => 850.00,
        ]);

        $response = $this->get(route('products.index'));

        $response->assertStatus(200);
        $response->assertSee('Basler Dart Vision');
        $response->assertSee('CAM-CAT-001');
    }

    public function test_product_create_page_renders_with_unit_price_field(): void
    {
        $response = $this->get(route('products.create'));

        $response->assertStatus(200);
        $response->assertSee('Default Unit Price (RM)');
        $response->assertSee('name="unit_price"', false);
    }

    public function test_product_create_page_renders_brand_dropdown_select(): void
    {
        \App\Models\Brand::create(['name' => 'Computar Optics']);

        $response = $this->get(route('products.create'));

        $response->assertStatus(200);
        $response->assertSee('select id="brand"', false);
        $response->assertSee('name="brand"', false);
        $response->assertSee('Computar Optics');
        $response->assertDontSee('datalist id="brands_list"', false);
    }

    public function test_can_create_product_without_image(): void
    {
        $response = $this->post(route('products.store'), [
            'sku' => 'CAM-NO-IMG',
            'name' => 'Basic Sensor Model',
            'category' => 'Industrial Cameras',
            'unit_price' => 300.00,
            'description' => 'Compact camera sensor unit',
        ]);

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'sku' => 'CAM-NO-IMG',
            'name' => 'Basic Sensor Model',
            'image_path' => null,
        ]);
    }

    public function test_can_create_product_with_valid_image(): void
    {
        Storage::fake('public');

        $file = $this->createFakePng('camera_photo.png', 500); // 500 KB

        $response = $this->post(route('products.store'), [
            'sku' => 'CAM-WITH-IMG',
            'name' => 'Ace 2 Pro GigE',
            'category' => 'Industrial Cameras',
            'unit_price' => 1250.00,
            'image' => $file,
        ]);

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('success');

        $product = Product::where('sku', 'CAM-WITH-IMG')->firstOrFail();
        $this->assertNotNull($product->image_path);

        Storage::disk('public')->assertExists($product->image_path);
    }

    public function test_can_upload_large_image_without_2mb_rejection(): void
    {
        Storage::fake('public');

        // 3000 KB previously failed under 2MB limit, now accepted and auto-optimized
        $largeFile = $this->createFakePng('large_photo.png', 3000);

        $response = $this->post(route('products.store'), [
            'sku' => 'CAM-LARGE-IMG',
            'name' => 'High Res Camera',
            'category' => 'Industrial Cameras',
            'image' => $largeFile,
        ]);

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'sku' => 'CAM-LARGE-IMG',
        ]);
        $product = Product::where('sku', 'CAM-LARGE-IMG')->firstOrFail();
        $this->assertNotNull($product->image_path);
        Storage::disk('public')->assertExists($product->image_path);
    }

    public function test_product_forms_render_auto_compression_indicator(): void
    {
        $response = $this->get(route('products.create'));
        $response->assertStatus(200);
        $response->assertSee('Auto-Compressed');
        $response->assertDontSee('Max size 2MB');
        $response->assertSee('compression_status');
    }

    public function test_rejects_non_image_files(): void
    {
        Storage::fake('public');

        $fakePdf = UploadedFile::fake()->create('manual.pdf', 500, 'application/pdf');

        $response = $this->from(route('products.create'))->post(route('products.store'), [
            'sku' => 'CAM-PDF-FILE',
            'name' => 'PDF Attached Camera',
            'category' => 'Industrial Cameras',
            'image' => $fakePdf,
        ]);

        $response->assertRedirect(route('products.create'));
        $response->assertSessionHasErrors(['image']);

        $this->assertDatabaseMissing('products', [
            'sku' => 'CAM-PDF-FILE',
        ]);
    }

    public function test_can_create_product_with_compressed_base64_image(): void
    {
        Storage::fake('public');

        // Create a 1x1 transparent PNG data URI
        $base64Png = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $response = $this->post(route('products.store'), [
            'sku' => 'CAM-BASE64-01',
            'name' => 'Base64 Compressed Camera',
            'category' => 'Industrial Cameras',
            'image_base64' => $base64Png,
        ]);

        $response->assertRedirect(route('products.index'));

        $product = Product::where('sku', 'CAM-BASE64-01')->first();
        $this->assertNotNull($product);
        $this->assertNotNull($product->image_path);
        $this->assertStringStartsWith('products/', $product->image_path);
        $this->assertStringEndsWith('.png', $product->image_path);

        Storage::disk('public')->assertExists($product->image_path);
    }

    public function test_can_update_product_with_compressed_base64_image(): void
    {
        Storage::fake('public');

        $initialFile = $this->createFakePng('old_camera.png', 400);
        $oldPath = $initialFile->store('products', 'public');

        $product = Product::create([
            'sku' => 'CAM-BASE64-UPDATE',
            'name' => 'Vision Base64 Old',
            'category' => 'Industrial Cameras',
            'image_path' => $oldPath,
        ]);

        Storage::disk('public')->assertExists($oldPath);

        // 1x1 base64 jpeg
        $base64Jpg = 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA=';

        $response = $this->put(route('products.update', $product->id), [
            'sku' => 'CAM-BASE64-UPDATE',
            'name' => 'Vision Base64 Updated',
            'category' => 'Industrial Cameras',
            'image_base64' => $base64Jpg,
        ]);

        $response->assertRedirect(route('products.index'));

        $product->refresh();
        $this->assertNotEquals($oldPath, $product->image_path);
        $this->assertStringEndsWith('.jpg', $product->image_path);

        // Old file must be purged
        Storage::disk('public')->assertMissing($oldPath);
        // New file must exist
        Storage::disk('public')->assertExists($product->image_path);
    }

    public function test_updating_product_with_new_image_replaces_and_deletes_old_image(): void
    {
        Storage::fake('public');

        $initialFile = $this->createFakePng('old_camera.png', 400);
        $oldPath = $initialFile->store('products', 'public');

        $product = Product::create([
            'sku' => 'CAM-REPLACE-IMG',
            'name' => 'Vision Model V1',
            'category' => 'Industrial Cameras',
            'image_path' => $oldPath,
        ]);

        Storage::disk('public')->assertExists($oldPath);

        $newFile = $this->createFakePng('new_camera.png', 600);

        $response = $this->put(route('products.update', $product->id), [
            'sku' => 'CAM-REPLACE-IMG',
            'name' => 'Vision Model V2',
            'category' => 'Industrial Cameras',
            'image' => $newFile,
        ]);

        $response->assertRedirect(route('products.index'));

        $product->refresh();
        $this->assertNotEquals($oldPath, $product->image_path);

        // Old file must be purged from disk to prevent storage bloat
        Storage::disk('public')->assertMissing($oldPath);
        // New file must exist
        Storage::disk('public')->assertExists($product->image_path);
    }

    public function test_can_remove_image_from_product(): void
    {
        Storage::fake('public');

        $file = $this->createFakePng('to_remove.png', 300);
        $path = $file->store('products', 'public');

        $product = Product::create([
            'sku' => 'CAM-REMOVE-IMG',
            'name' => 'Vision Model Clean',
            'category' => 'Industrial Cameras',
            'image_path' => $path,
        ]);

        Storage::disk('public')->assertExists($path);

        $response = $this->put(route('products.update', $product->id), [
            'sku' => 'CAM-REMOVE-IMG',
            'name' => 'Vision Model Clean',
            'category' => 'Industrial Cameras',
            'remove_image' => '1',
        ]);

        $response->assertRedirect(route('products.index'));

        $product->refresh();
        $this->assertNull($product->image_path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_deleting_product_purges_image_from_storage(): void
    {
        Storage::fake('public');

        $file = $this->createFakePng('camera_to_delete.png', 300);
        $path = $file->store('products', 'public');

        $product = Product::create([
            'sku' => 'CAM-DELETE-TEST',
            'name' => 'Discontinued Camera',
            'category' => 'Industrial Cameras',
            'image_path' => $path,
        ]);

        Storage::disk('public')->assertExists($path);

        $response = $this->delete(route('products.destroy', $product->id));

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_cannot_delete_product_with_active_inventory_units(): void
    {
        $product = Product::create([
            'sku' => 'CAM-IN-USE',
            'name' => 'Active Hardware Product',
            'category' => 'Industrial Cameras',
        ]);

        $location = Location::create([
            'name' => 'Rack Test',
            'type' => 'internal',
            'branch' => 'KL',
            'floor' => '1F',
        ]);

        InventoryItem::create([
            'product_id' => $product->id,
            'location_id' => $location->id,
            'serial_number' => 'SN-ACTIVE-001',
            'status' => 'in_stock',
        ]);

        $response = $this->delete(route('products.destroy', $product->id));

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_can_create_product_with_brand_and_min_stock_level(): void
    {
        $response = $this->post(route('products.store'), [
            'sku' => 'CAM-BAS-ACE2',
            'name' => 'Basler ace 2 Pro',
            'brand' => 'Basler',
            'category' => 'Industrial Cameras',
            'unit_price' => 1500.00,
            'min_stock_level' => 4,
            'description' => 'Flagship machine vision camera',
        ]);

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'sku' => 'CAM-BAS-ACE2',
            'name' => 'Basler ace 2 Pro',
            'brand' => 'Basler',
            'min_stock_level' => 4,
        ]);
    }

    public function test_can_update_product_brand_and_min_stock_level(): void
    {
        $product = Product::create([
            'sku' => 'LENS-COMP-01',
            'name' => '16mm Machine Vision Lens',
            'brand' => 'Generic Optics',
            'category' => 'Industrial Cameras',
            'unit_price' => 450.00,
            'min_stock_level' => 1,
        ]);

        $response = $this->put(route('products.update', $product->id), [
            'sku' => 'LENS-COMP-01',
            'name' => '16mm Machine Vision Lens',
            'brand' => 'Computar',
            'category' => 'Industrial Cameras',
            'unit_price' => 480.00,
            'min_stock_level' => 6,
        ]);

        $response->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'brand' => 'Computar',
            'min_stock_level' => 6,
        ]);
    }

    public function test_can_filter_and_sort_products_by_brand(): void
    {
        Product::create([
            'sku' => 'PROD-BASLER',
            'name' => 'Basler Ace',
            'brand' => 'Basler',
            'category' => 'Industrial Cameras',
        ]);

        Product::create([
            'sku' => 'PROD-COMPUTAR',
            'name' => 'Computar Lens',
            'brand' => 'Computar',
            'category' => 'Industrial Cameras',
        ]);

        // Filter by Brand Basler
        $filterResponse = $this->get(route('products.index', ['brand' => 'Basler']));
        $filterResponse->assertStatus(200);
        $filterResponse->assertSee('Basler Ace');
        $filterResponse->assertDontSee('Computar Lens');

        // Sort by Brand Ascending
        $sortAscResponse = $this->get(route('products.index', ['sort' => 'brand_asc']));
        $sortAscResponse->assertStatus(200);

        // Sort by Brand Descending
        $sortDescResponse = $this->get(route('products.index', ['sort' => 'brand_desc']));
        $sortDescResponse->assertStatus(200);
    }
}
