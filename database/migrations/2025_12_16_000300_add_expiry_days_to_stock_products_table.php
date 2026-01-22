<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('stock_products')) {
            return;
        }

        if (!Schema::hasColumn('stock_products', 'expiry_days')) {
            Schema::table('stock_products', function (Blueprint $table): void {
                $table->integer('expiry_days')->nullable()->after('stock_account_note');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('stock_products')) {
            return;
        }

        if (Schema::hasColumn('stock_products', 'expiry_days')) {
            Schema::table('stock_products', function (Blueprint $table): void {
                $table->dropColumn('expiry_days');
            });
        }
    }
};
