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
        Schema::create('record_table_preferences', function (Blueprint $table) {
            $table->id();
            $table->string('context', 32);
            $table->unsignedBigInteger('record_product_id')->nullable();
            $table->json('preferences');
            $table->timestamps();

            $table->unique(['context', 'record_product_id'], 'record_table_prefs_context_product_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('record_table_preferences');
    }
};
