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
        if (!Schema::hasColumn('users', 'stock_pin_name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('stock_pin_name')->nullable()->after('stock_pin_hash');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'stock_pin_name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('stock_pin_name');
            });
        }
    }
};
