<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('qr_codes') && !Schema::hasColumn('qr_codes', 'visible_to_employees')) {
            Schema::table('qr_codes', function (Blueprint $table): void {
                $table->boolean('visible_to_employees')
                    ->default(true)
                    ->after('payment_method_number');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('qr_codes') && Schema::hasColumn('qr_codes', 'visible_to_employees')) {
            Schema::table('qr_codes', function (Blueprint $table): void {
                $table->dropColumn('visible_to_employees');
            });
        }
    }
};
