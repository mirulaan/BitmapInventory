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
        Schema::create('locations', function (Blueprint $table) {
        $table->id();
        $table->foreignId('parent_id')->nullable()->constrained('locations')->nullOnDelete();
        $table->string('name'); // e.g., "Office A", "Floor 2", "Rack 12B"
        $table->string('type'); // e.g., 'building', 'floor', 'room', 'rack', 'client_site'
        $table->timestamps();
        $table->softDeletes();
        
        // Indexing for faster hierarchical queries
        $table->index(['parent_id', 'type']);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
