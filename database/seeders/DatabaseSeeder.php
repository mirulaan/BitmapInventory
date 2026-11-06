<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Location;
use App\Models\Client;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. CLEAR EXISTING DATA 
        DB::statement('SET CONSTRAINTS ALL DEFERRED');
        InventoryTransaction::truncate();
        InventoryItem::truncate();
        Product::truncate();
        Brand::truncate();
        Category::truncate();
        Client::truncate();
        Location::truncate();
        User::truncate();

        // 2. CREATE SYSTEM USERS FOR 4 ROLES
        $admin = User::firstOrCreate(
            ['email' => 'admin@bitmap.com'],
            [
                'name' => 'Super Administrator',
                'username' => 'admin',
                'password' => bcrypt('password'),
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'warehouse@bitmap.com'],
            [
                'name' => 'Warehouse Manager',
                'username' => 'warehouse',
                'password' => bcrypt('password'),
                'role' => User::ROLE_WAREHOUSE,
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'engineer@bitmap.com'],
            [
                'name' => 'Field Engineer',
                'username' => 'engineer',
                'password' => bcrypt('password'),
                'role' => User::ROLE_ENGINEER,
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'viewer@bitmap.com'],
            [
                'name' => 'Auditor / Viewer',
                'username' => 'viewer',
                'password' => bcrypt('password'),
                'role' => User::ROLE_VIEWER,
                'is_active' => true,
            ]
        );

        // Also maintain admin@example.com for backward compatibility
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'System Admin',
                'username' => 'admin_legacy',
                'password' => bcrypt('password'),
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
            ]
        );

        // 3. CREATE CATEGORIES & BRANDS
        $categories = [
            Category::create(['name' => 'Camera']),
            Category::create(['name' => 'IPC']),
            Category::create(['name' => 'Network Switch']),
        ];

        $brands = [
            Brand::create(['name' => 'Basler']),
            Brand::create(['name' => 'Cognex']),
            Brand::create(['name' => 'Advantech']),
            Brand::create(['name' => 'Cisco']),
            Brand::create(['name' => 'Computar']),
            Brand::create(['name' => 'Hikrobot']),
        ];

        // 4. CREATE PRODUCTS
        $products = [
            Product::create(['sku' => 'CAM-BASLER-01', 'name' => 'Basler ace 2', 'brand' => 'Basler', 'category' => 'Camera', 'unit_price' => 1200.00]),
            Product::create(['sku' => 'CAM-COGNEX-05', 'name' => 'Cognex In-Sight 8000', 'brand' => 'Cognex', 'category' => 'Camera', 'unit_price' => 3500.00]),
            Product::create(['sku' => 'IPC-ADVAN-01', 'name' => 'Advantech UNO-2484G', 'brand' => 'Advantech', 'category' => 'IPC', 'unit_price' => 2100.00]),
            Product::create(['sku' => 'NET-CISCO-01', 'name' => 'Cisco Catalyst 1000', 'brand' => 'Cisco', 'category' => 'Network Switch', 'unit_price' => 450.00]),
        ];

        // 5. CREATE LOCATIONS
        $hqLocation = Location::create(['name' => 'Rack A-01', 'type' => 'internal', 'branch' => 'HQ Warehouse', 'floor' => 'Floor 1']);
        Location::create(['name' => 'Rack B-02', 'type' => 'internal', 'branch' => 'HQ Warehouse', 'floor' => 'Floor 1']);
        Location::create(['name' => 'Main Receiving Bay', 'type' => 'internal', 'branch' => 'HQ Warehouse', 'floor' => 'Ground']);
        $siteA = Location::create(['name' => 'Factory Line A', 'type' => 'client_site']);
        $siteB = Location::create(['name' => 'Quality Control Lab', 'type' => 'client_site']);

        // 6. CREATE CLIENTS
        $clientA = Client::create(['company_name' => 'TechCorp Manufacturing', 'contact_person' => 'Jane Doe', 'email' => 'jane@techcorp.com']);
        $clientB = Client::create(['company_name' => 'Global Vision Systems', 'contact_person' => 'John Smith', 'email' => 'john@globalvision.com']);

        // 7. GENERATE INVENTORY & TRANSACTIONS

        // SCENARIO 1: Hardware currently In Stock (Newly received)
        for ($i = 1; $i <= 3; $i++) {
            $item = InventoryItem::create([
                'product_id' => $products[0]->id, // Basler Camera
                'serial_number' => 'SN-BAS-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'quantity' => 1,
                'location_id' => $hqLocation->id,
                'status' => 'in_stock',
            ]);

            InventoryTransaction::create([
                'inventory_item_id' => $item->id,
                'user_id' => $admin->id,
                'transaction_type' => 'stock_in',
                'from_location_id' => null,
                'to_location_id' => $hqLocation->id,
                'from_status' => null,
                'to_status' => 'in_stock',
                'quantity' => 1,
                'reference_number' => 'PO-RCV-001',
                'created_at' => Carbon::now()->subDays(30),
            ]);
        }

        // SCENARIO 2: Hardware currently On Loan (Active Early Warning)
        $loanItem = InventoryItem::create([
            'product_id' => $products[1]->id, // Cognex Camera
            'serial_number' => 'SN-COG-9912',
            'quantity' => 1,
            'location_id' => $siteA->id,
            'client_id' => $clientA->id,
            'status' => 'on_loan',
        ]);

        // Stock in first...
        InventoryTransaction::create([
            'inventory_item_id' => $loanItem->id,
            'user_id' => $admin->id,
            'transaction_type' => 'stock_in',
            'to_location_id' => $hqLocation->id,
            'to_status' => 'in_stock',
            'quantity' => 1,
            'created_at' => Carbon::now()->subMonths(2),
        ]);

        // ... then dispatch on loan (Expiring in 5 days to trigger the Orange Badge!)
        InventoryTransaction::create([
            'inventory_item_id' => $loanItem->id,
            'user_id' => $admin->id,
            'transaction_type' => 'dispatch_loan',
            'from_location_id' => $hqLocation->id,
            'to_location_id' => $siteA->id,
            'from_status' => 'in_stock',
            'to_status' => 'on_loan',
            'quantity' => 1,
            'person_in_charge' => 'Ali Bin Ahmad',
            'loan_form_no' => 'LF-2026-881',
            'loan_start_date' => Carbon::now()->subMonths(2),
            'loan_end_date' => Carbon::now()->addDays(5), 
            'created_at' => Carbon::now()->subMonths(2),
        ]);


        // SCENARIO 3: Hardware Overdue (Triggers Red Pulsing Badge)
        $overdueItem = InventoryItem::create([
            'product_id' => $products[2]->id, // IPC
            'serial_number' => 'SN-IPC-4411',
            'quantity' => 1,
            'location_id' => $siteB->id,
            'client_id' => $clientB->id,
            'status' => 'on_loan',
        ]);

        InventoryTransaction::create([
            'inventory_item_id' => $overdueItem->id,
            'user_id' => $admin->id,
            'transaction_type' => 'dispatch_loan',
            'from_location_id' => $hqLocation->id,
            'to_location_id' => $siteB->id,
            'from_status' => 'in_stock',
            'to_status' => 'on_loan',
            'quantity' => 1,
            'person_in_charge' => 'Sarah Connor',
            'loan_form_no' => 'LF-2026-102',
            'loan_start_date' => Carbon::now()->subMonths(6),
            'loan_end_date' => Carbon::now()->subDays(10), // Overdue by 10 days
            'created_at' => Carbon::now()->subMonths(6),
        ]);


        // SCENARIO 4: Hardware Converted to Sale
        $soldItem = InventoryItem::create([
            'product_id' => $products[3]->id, // Switch
            'serial_number' => 'SN-NET-5555',
            'quantity' => 1,
            'location_id' => $siteA->id,
            'client_id' => $clientA->id,
            'status' => 'sold',
        ]);

        // Dispatched on loan 4 months ago
        InventoryTransaction::create([
            'inventory_item_id' => $soldItem->id,
            'user_id' => $admin->id,
            'transaction_type' => 'dispatch_loan',
            'from_location_id' => $hqLocation->id,
            'to_location_id' => $siteA->id,
            'from_status' => 'in_stock',
            'to_status' => 'on_loan',
            'quantity' => 1,
            'loan_form_no' => 'LF-2026-050',
            'created_at' => Carbon::now()->subMonths(4),
        ]);

        // Converted to sale today
        InventoryTransaction::create([
            'inventory_item_id' => $soldItem->id,
            'user_id' => $admin->id,
            'transaction_type' => 'convert_to_sale',
            'from_location_id' => $siteA->id,
            'to_location_id' => $siteA->id,
            'from_status' => 'on_loan',
            'to_status' => 'sold',
            'quantity' => 1,
            'person_in_charge' => 'Jane Doe',
            'loan_form_no' => 'LF-2026-050',
            'po_customer' => 'POC-2026-991',
            'po_supplier' => 'POS-2026-441',
            'created_at' => Carbon::now(),
        ]);
        
        $this->command->info('Inventory System thoroughly seeded with new lifecycle data!');
    }
}