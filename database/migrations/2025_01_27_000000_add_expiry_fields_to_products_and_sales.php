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
        if (Schema::hasTable('product_variations') && !Schema::hasColumn('product_variations', 'expiry_days')) {
            Schema::table('product_variations', function (Blueprint $table) {
                $table->unsignedInteger('expiry_days')->nullable()->after('name');
            });
        }

        if (Schema::hasTable('sales') && !Schema::hasColumn('sales', 'product_expiry_days')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->unsignedInteger('product_expiry_days')->nullable()->after('product_name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('product_variations') && Schema::hasColumn('product_variations', 'expiry_days')) {
            Schema::table('product_variations', function (Blueprint $table) {
                $table->dropColumn('expiry_days');
            });
        }

        if (Schema::hasTable('sales') && Schema::hasColumn('sales', 'product_expiry_days')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropColumn('product_expiry_days');
            });
        }
    }
};
