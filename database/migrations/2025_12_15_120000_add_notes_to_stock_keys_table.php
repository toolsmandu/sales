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
        if (!Schema::hasTable('stock_keys') || Schema::hasColumn('stock_keys', 'notes')) {
            return;
        }

        Schema::table('stock_keys', function (Blueprint $table): void {
            $table->text('notes')->nullable()->after('activation_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('stock_keys') && Schema::hasColumn('stock_keys', 'notes')) {
            Schema::table('stock_keys', function (Blueprint $table): void {
                $table->dropColumn('notes');
            });
        }
    }
};
