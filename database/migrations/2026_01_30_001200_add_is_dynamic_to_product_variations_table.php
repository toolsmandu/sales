<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('product_variations') || Schema::hasColumn('product_variations', 'is_dynamic')) {
            return;
        }

        Schema::table('product_variations', function (Blueprint $table): void {
            $table->boolean('is_dynamic')->default(false)->after('is_in_stock');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('product_variations') || !Schema::hasColumn('product_variations', 'is_dynamic')) {
            return;
        }

        Schema::table('product_variations', function (Blueprint $table): void {
            $table->dropColumn('is_dynamic');
        });
    }
};
