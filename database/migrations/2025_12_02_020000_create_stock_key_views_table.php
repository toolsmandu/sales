<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('stock_keys')) {
            return;
        }

        Schema::create('stock_key_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_key_id')->constrained('stock_keys')->cascadeOnDelete();
            $table->foreignId('viewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamp('viewed_at')->useCurrent();
            $table->timestamps();

            $table->index(['stock_key_id', 'viewed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_key_views');
    }
};
