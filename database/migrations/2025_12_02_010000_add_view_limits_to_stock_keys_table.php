<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('stock_keys')) {
            return;
        }

        Schema::table('stock_keys', function (Blueprint $table): void {
            if (!Schema::hasColumn('stock_keys', 'view_limit')) {
                $table->unsignedSmallInteger('view_limit')->default(1)->after('activation_key');
            }
            if (!Schema::hasColumn('stock_keys', 'view_count')) {
                $table->unsignedSmallInteger('view_count')->default(0)->after('view_limit');
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('stock_keys')) {
            Schema::table('stock_keys', function (Blueprint $table): void {
                if (Schema::hasColumn('stock_keys', 'view_count')) {
                    $table->dropColumn('view_count');
                }
                if (Schema::hasColumn('stock_keys', 'view_limit')) {
                    $table->dropColumn('view_limit');
                }
            });
        }
    }
};
