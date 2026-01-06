<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('qr_codes') && !Schema::hasColumn('qr_codes', 'description')) {
            Schema::table('qr_codes', function (Blueprint $table): void {
                $table->text('description')->nullable()->after('file_path');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('qr_codes') && Schema::hasColumn('qr_codes', 'description')) {
            Schema::table('qr_codes', function (Blueprint $table): void {
                $table->dropColumn('description');
            });
        }
    }
};
