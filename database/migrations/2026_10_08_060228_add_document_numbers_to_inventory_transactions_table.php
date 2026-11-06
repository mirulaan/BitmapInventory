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
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->string('loan_form_no')->nullable()->after('person_in_charge');
            $table->string('po_customer')->nullable()->after('loan_form_no');
            $table->string('po_supplier')->nullable()->after('po_customer');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropColumn(['loan_form_no', 'po_customer', 'po_supplier']);
        });
    }
};
