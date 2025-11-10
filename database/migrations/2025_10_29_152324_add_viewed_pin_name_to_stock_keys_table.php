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
        Schema::table('stock_keys', function (Blueprint $table) {
            $table->string('viewed_by_pin_name')->nullable()->after('viewed_by_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_keys', function (Blueprint $table) {
            $table->dropColumn('viewed_by_pin_name');
        });
    }
};
