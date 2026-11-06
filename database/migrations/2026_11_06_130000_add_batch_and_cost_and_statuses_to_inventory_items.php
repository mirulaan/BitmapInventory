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
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->string('batch_no')->nullable()->after('serial_number');
            $table->decimal('unit_cost', 12, 2)->nullable()->default(0.00)->after('quantity');
        });

        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->string('batch_no')->nullable()->after('quantity');
            $table->decimal('unit_cost', 12, 2)->nullable()->default(0.00)->after('batch_no');
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE inventory_items DROP CONSTRAINT IF EXISTS inventory_items_status_check;");
            DB::statement("ALTER TABLE inventory_items ADD CONSTRAINT inventory_items_status_check CHECK (status::text = ANY (ARRAY['in_stock'::character varying, 'on_loan'::character varying, 'sold'::character varying, 'rma_defective'::character varying, 'in_transit'::character varying, 'reserved'::character varying, 'decommissioned'::character varying]::text[]));");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE inventory_items DROP CONSTRAINT IF EXISTS inventory_items_status_check;");
            DB::statement("ALTER TABLE inventory_items ADD CONSTRAINT inventory_items_status_check CHECK (status::text = ANY (ARRAY['in_stock'::character varying, 'on_loan'::character varying, 'sold'::character varying, 'rma_defective'::character varying, 'in_transit'::character varying]::text[]));");
        }

        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropColumn(['batch_no', 'unit_cost']);
        });

        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropColumn(['batch_no', 'unit_cost']);
        });
    }
};
