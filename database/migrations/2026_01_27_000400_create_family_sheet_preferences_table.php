<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('family_sheet_preferences')) {
            return;
        }

        Schema::create('family_sheet_preferences', function (Blueprint $table): void {
            $table->id();
            $table->string('context', 50);
            $table->unsignedBigInteger('family_product_id')->nullable();
            $table->json('preferences');
            $table->timestamps();

            $table->unique(['context', 'family_product_id'], 'family_sheet_prefs_context_product_unique');
            $table->index('family_product_id', 'family_sheet_prefs_product_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_sheet_preferences');
    }
};
