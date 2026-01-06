<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('employee_settings')) {
            Schema::table('employee_settings', function (Blueprint $table): void {
                if (!Schema::hasColumn('employee_settings', 'daily_hours_quota')) {
                    $table->unsignedInteger('daily_hours_quota')->default(0)->after('monthly_hours_quota');
                }
                if (!Schema::hasColumn('employee_settings', 'holiday_weekdays')) {
                    $table->json('holiday_weekdays')->nullable()->after('daily_hours_quota');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('employee_settings')) {
            Schema::table('employee_settings', function (Blueprint $table): void {
                $columns = [];
                if (Schema::hasColumn('employee_settings', 'daily_hours_quota')) {
                    $columns[] = 'daily_hours_quota';
                }
                if (Schema::hasColumn('employee_settings', 'holiday_weekdays')) {
                    $columns[] = 'holiday_weekdays';
                }

                if (!empty($columns)) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
};
