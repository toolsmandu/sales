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
        Schema::table('users', function (Blueprint $table) {
            $table->string('stock_pin_hash')->nullable()->after('remember_token');
        });

        Schema::table('stock_keys', function (Blueprint $table) {
            $table->foreignId('viewed_by_user_id')
                ->nullable()
                ->after('viewed_at')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('stock_pin_hash');
        });

        Schema::table('stock_keys', function (Blueprint $table) {
            $table->dropConstrainedForeignId('viewed_by_user_id');
        });
    }
};
