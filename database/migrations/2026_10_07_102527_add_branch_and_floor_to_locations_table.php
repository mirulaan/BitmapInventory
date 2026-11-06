<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Clear the old nested data so you start with a clean slate
        \Illuminate\Support\Facades\DB::table('locations')->delete();

        Schema::table('locations', function (Blueprint $table) {
            $table->string('branch')->nullable()->after('type');
            $table->string('floor')->nullable()->after('branch');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            //
        });
    }
};
