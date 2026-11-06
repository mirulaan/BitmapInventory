<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('product_id')->constrained()->restrictOnDelete();
        $table->string('serial_number')->nullable()->unique(); // Nullable for non-serialized bulk items
        $table->integer('quantity')->default(1); 
        
        $table->foreignId('location_id')->constrained('locations');
        $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete(); // Populated if On Loan or Sold
        
        $table->enum('status', [
            'in_stock', 
            'on_loan', 
            'sold', 
            'rma_defective',
            'in_transit',
            'reserved',
            'decommissioned'
        ])->default('in_stock');
        
        $table->timestamps();
        
        $table->index(['status', 'location_id']);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
