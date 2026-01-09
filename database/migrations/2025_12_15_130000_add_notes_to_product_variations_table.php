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
        if (!Schema::hasTable('product_variations') || Schema::hasColumn('product_variations', 'notes')) {
            return;
        }

        Schema::table('product_variations', function (Blueprint $table): void {
            $table->text('notes')->nullable()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('product_variations') && Schema::hasColumn('product_variations', 'notes')) {
            Schema::table('product_variations', function (Blueprint $table): void {
                $table->dropColumn('notes');
            });
        }
    }
};
