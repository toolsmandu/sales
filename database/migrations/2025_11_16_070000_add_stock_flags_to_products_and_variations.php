<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'is_in_stock')) {
            Schema::table('products', function (Blueprint $table) {
                $table->boolean('is_in_stock')->default(true)->after('name');
            });
        }

        if (Schema::hasTable('product_variations') && !Schema::hasColumn('product_variations', 'is_in_stock')) {
            Schema::table('product_variations', function (Blueprint $table) {
                $table->boolean('is_in_stock')->default(true)->after('expiry_days');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('product_variations') && Schema::hasColumn('product_variations', 'is_in_stock')) {
            Schema::table('product_variations', function (Blueprint $table) {
                $table->dropColumn('is_in_stock');
            });
        }

        if (Schema::hasTable('products') && Schema::hasColumn('products', 'is_in_stock')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('is_in_stock');
            });
        }
    }
};
