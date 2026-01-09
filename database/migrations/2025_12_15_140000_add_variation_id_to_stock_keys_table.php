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
        if (!Schema::hasTable('stock_keys') || Schema::hasColumn('stock_keys', 'variation_id')) {
            return;
        }

        Schema::table('stock_keys', function (Blueprint $table): void {
            $table->foreignId('variation_id')
                ->nullable()
                ->after('product_id')
                ->constrained('product_variations')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('stock_keys') && Schema::hasColumn('stock_keys', 'variation_id')) {
            Schema::table('stock_keys', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('variation_id');
            });
        }
    }
};
