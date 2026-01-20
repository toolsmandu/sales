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

        if (Schema::hasColumn('stock_products', 'stock_account_note')) {
            Schema::table('stock_products', function (Blueprint $table): void {
                $table->text('stock_account_note')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('stock_products')) {
            return;
        }

        if (Schema::hasColumn('stock_products', 'stock_account_note')) {
            Schema::table('stock_products', function (Blueprint $table): void {
                $table->string('stock_account_note')->nullable()->change();
            });
        }
    }
};
