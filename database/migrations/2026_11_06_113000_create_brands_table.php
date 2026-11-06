<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Seed initial industry brands
        $initialBrands = [
            'Basler',
            'Cognex',
            'Advantech',
            'Cisco',
            'Computar',
            'Hikrobot',
            'Intel',
            'Omron',
            'Keyence',
        ];

        // Also extract any existing brands already assigned to products
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'brand')) {
            $existing = DB::table('products')
                ->whereNotNull('brand')
                ->where('brand', '!=', '')
                ->distinct()
                ->pluck('brand')
                ->toArray();
            $initialBrands = array_unique(array_merge($initialBrands, $existing));
        }

        foreach ($initialBrands as $brand) {
            if (! empty(trim($brand))) {
                DB::table('brands')->insertOrIgnore([
                    'name' => trim($brand),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
