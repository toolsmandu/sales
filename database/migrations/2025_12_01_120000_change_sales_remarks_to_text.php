<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('sales') || !Schema::hasColumn('sales', 'remarks')) {
            return;
        }

        DB::statement('ALTER TABLE `sales` MODIFY `remarks` TEXT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('sales') || !Schema::hasColumn('sales', 'remarks')) {
            return;
        }

        DB::statement('ALTER TABLE `sales` MODIFY `remarks` VARCHAR(255) NULL');
    }
};
