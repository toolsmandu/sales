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
        if (!Schema::hasColumn('stock_keys', 'viewed_remarks')) {
            Schema::table('stock_keys', function (Blueprint $table) {
                $table->string('viewed_remarks')->nullable()->after('viewed_by_pin_name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('stock_keys', 'viewed_remarks')) {
            Schema::table('stock_keys', function (Blueprint $table) {
                $table->dropColumn('viewed_remarks');
            });
        }
    }
};
