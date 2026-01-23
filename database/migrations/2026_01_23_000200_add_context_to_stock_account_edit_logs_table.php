<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stock_account_edit_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_account_edit_logs', 'context')) {
                $table->string('context', 32)->nullable()->after('actor_id');
            }
        });

        DB::table('stock_account_edit_logs')
            ->whereNull('context')
            ->update(['context' => 'stock-account']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_account_edit_logs', function (Blueprint $table) {
            if (Schema::hasColumn('stock_account_edit_logs', 'context')) {
                $table->dropColumn('context');
            }
        });
    }
};
