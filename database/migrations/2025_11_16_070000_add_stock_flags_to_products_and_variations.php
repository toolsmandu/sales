<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_in_stock')->default(true)->after('name');
        });

        Schema::table('product_variations', function (Blueprint $table) {
            $table->boolean('is_in_stock')->default(true)->after('expiry_days');
        });
    }

    public function down(): void
    {
        Schema::table('product_variations', function (Blueprint $table) {
            $table->dropColumn('is_in_stock');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('is_in_stock');
        });
    }
};
