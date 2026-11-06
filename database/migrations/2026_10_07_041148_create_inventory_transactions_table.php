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
        Schema::create('inventory_transactions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
        $table->foreignId('user_id')->constrained(); // The staff member who executed the move
        
        $table->string('transaction_type'); // 'stock_in', 'direct_sale', 'loan_out', 'loan_return', 'loan_to_sale'
        
        // Movement tracking
        $table->foreignId('from_location_id')->nullable()->constrained('locations');
        $table->foreignId('to_location_id')->nullable()->constrained('locations');
        
        // Status tracking
        $table->string('from_status')->nullable();
        $table->string('to_status')->nullable();
        
        $table->integer('quantity')->default(1);
        $table->string('reference_number')->nullable(); // PO number, Invoice ID, or Loan Agreement ID
        $table->text('notes')->nullable();
        
        $table->timestamps(); // The 'when'
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
