<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_settings', function (Blueprint $table): void {
            $table->unsignedInteger('daily_hours_quota')->default(0)->after('monthly_hours_quota');
            $table->json('holiday_weekdays')->nullable()->after('daily_hours_quota');
        });
    }

    public function down(): void
    {
        Schema::table('employee_settings', function (Blueprint $table): void {
            $table->dropColumn(['daily_hours_quota', 'holiday_weekdays']);
        });
    }
};
