<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('record_products')) {
            return;
        }

        Schema::table('record_products', function (Blueprint $table): void {
            if (!Schema::hasColumn('record_products', 'linked_product_id')) {
                $table->foreignId('linked_product_id')->nullable()->after('table_name')->constrained('products')->nullOnDelete();
            }
            if (!Schema::hasColumn('record_products', 'linked_variation_ids')) {
                $table->json('linked_variation_ids')->nullable()->after('linked_product_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('record_products')) {
            return;
        }

        Schema::table('record_products', function (Blueprint $table): void {
            if (Schema::hasColumn('record_products', 'linked_product_id')) {
                $table->dropForeign(['linked_product_id']);
                $table->dropColumn('linked_product_id');
            }
            if (Schema::hasColumn('record_products', 'linked_variation_ids')) {
                $table->dropColumn('linked_variation_ids');
            }
        });
    }
};
