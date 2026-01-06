<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sales')) {
            return;
        }

        if (Schema::hasColumn('sales', 'payment_method_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropForeign(['payment_method_id']);
            });
        }

        if (Schema::hasColumn('sales', 'product_name')) {
            DB::statement('ALTER TABLE sales MODIFY product_name VARCHAR(255) NULL');
        }
        if (Schema::hasColumn('sales', 'email')) {
            DB::statement('ALTER TABLE sales MODIFY email VARCHAR(255) NULL');
        }
        if (Schema::hasColumn('sales', 'sales_amount')) {
            DB::statement('ALTER TABLE sales MODIFY sales_amount DECIMAL(14, 2) NULL');
        }
        if (Schema::hasColumn('sales', 'payment_method_id')) {
            DB::statement('ALTER TABLE sales MODIFY payment_method_id BIGINT UNSIGNED NULL');
        }

        if (Schema::hasTable('payment_methods') && Schema::hasColumn('sales', 'payment_method_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->foreign('payment_method_id')
                    ->references('id')
                    ->on('payment_methods')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('sales')) {
            return;
        }

        // Backfill nulls with safe defaults before reverting to NOT NULL.
        if (Schema::hasColumn('sales', 'product_name')) {
            DB::table('sales')->whereNull('product_name')->update(['product_name' => '']);
        }
        if (Schema::hasColumn('sales', 'email')) {
            DB::table('sales')->whereNull('email')->update(['email' => '']);
        }
        if (Schema::hasColumn('sales', 'sales_amount')) {
            DB::table('sales')->whereNull('sales_amount')->update(['sales_amount' => 0]);
        }

        $fallbackMethodId = DB::table('payment_methods')->orderBy('id')->value('id');
        if ($fallbackMethodId) {
            DB::table('sales')->whereNull('payment_method_id')->update(['payment_method_id' => $fallbackMethodId]);
        }

        if (Schema::hasColumn('sales', 'payment_method_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropForeign(['payment_method_id']);
            });
        }

        if (Schema::hasColumn('sales', 'product_name')) {
            DB::statement('ALTER TABLE sales MODIFY product_name VARCHAR(255) NOT NULL');
        }
        if (Schema::hasColumn('sales', 'email')) {
            DB::statement('ALTER TABLE sales MODIFY email VARCHAR(255) NOT NULL');
        }
        if (Schema::hasColumn('sales', 'sales_amount')) {
            DB::statement('ALTER TABLE sales MODIFY sales_amount DECIMAL(14, 2) NOT NULL');
        }
        if (Schema::hasColumn('sales', 'payment_method_id')) {
            DB::statement('ALTER TABLE sales MODIFY payment_method_id BIGINT UNSIGNED NOT NULL');
        }

        if (Schema::hasTable('payment_methods') && Schema::hasColumn('sales', 'payment_method_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->foreign('payment_method_id')
                    ->references('id')
                    ->on('payment_methods')
                    ->restrictOnDelete();
            });
        }
    }
};
