<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('coupon_codes')) {
            return;
        }

        if (!Schema::hasColumn('coupon_codes', 'instructions')) {
            Schema::table('coupon_codes', function (Blueprint $table): void {
                $table->text('instructions')->nullable()->after('remarks');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('coupon_codes') && Schema::hasColumn('coupon_codes', 'instructions')) {
            Schema::table('coupon_codes', function (Blueprint $table): void {
                $table->dropColumn('instructions');
            });
        }
    }
};
