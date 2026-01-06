<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tasks') || Schema::hasColumn('tasks', 'custom_interval_days')) {
            return;
        }

        Schema::table('tasks', function (Blueprint $table): void {
            $table->unsignedInteger('custom_interval_days')->nullable()->after('custom_weekdays');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('tasks') || !Schema::hasColumn('tasks', 'custom_interval_days')) {
            return;
        }

        Schema::table('tasks', function (Blueprint $table): void {
            $table->dropColumn('custom_interval_days');
        });
    }
};
