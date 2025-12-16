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
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'stock_pin_hash')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('stock_pin_hash')->nullable()->after('remember_token');
            });
        }

        if (Schema::hasTable('stock_keys') && !Schema::hasColumn('stock_keys', 'viewed_by_user_id')) {
            Schema::table('stock_keys', function (Blueprint $table) {
                $table->foreignId('viewed_by_user_id')
                    ->nullable()
                    ->after('viewed_at')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'stock_pin_hash')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('stock_pin_hash');
            });
        }

        if (Schema::hasTable('stock_keys') && Schema::hasColumn('stock_keys', 'viewed_by_user_id')) {
            Schema::table('stock_keys', function (Blueprint $table) {
                $table->dropConstrainedForeignId('viewed_by_user_id');
            });
        }
    }
};
