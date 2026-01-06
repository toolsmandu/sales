<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payment_methods')) {
            return;
        }

        if (!Schema::hasColumn('payment_methods', 'unique_number') && !Schema::hasColumn('payment_methods', 'monthly_limit')) {
            Schema::table('payment_methods', function (Blueprint $table): void {
                $table->string('unique_number')->nullable()->after('slug');
                $table->decimal('monthly_limit', 12, 2)->default(0)->after('unique_number');
            });
        }

        if (!Schema::hasColumn('payment_methods', 'unique_number')) {
            return;
        }

        Schema::table('payment_methods', function (Blueprint $table): void {
            // Ensure monthly_limit exists for later steps even if table already had unique_number.
            if (!Schema::hasColumn('payment_methods', 'monthly_limit')) {
                $table->decimal('monthly_limit', 12, 2)->default(0)->after('unique_number');
            }
        });

        DB::table('payment_methods')->select('id', 'unique_number', 'slug')->orderBy('id')->chunkById(100, function ($methods): void {
            foreach ($methods as $method) {
                $unique = $method->unique_number;
                if (!$unique) {
                    $base = $method->slug ?: 'payment-method-'.$method->id;
                    $unique = strtoupper(Str::limit(Str::slug($base, ''), 12, '')); // fallback
                    if ($unique === '') {
                        $unique = 'PMT'.$method->id;
                    }
                }
                DB::table('payment_methods')
                    ->where('id', $method->id)
                    ->update(['unique_number' => $unique]);
            }
        });

        $hasUniqueIndex = collect(DB::select("SHOW INDEX FROM payment_methods WHERE Key_name = 'payment_methods_unique_number_unique'"))->isNotEmpty();

        Schema::table('payment_methods', function (Blueprint $table) use ($hasUniqueIndex): void {
            if ($hasUniqueIndex) {
                $table->string('unique_number')->nullable(false)->change();
            } else {
                $table->string('unique_number')->nullable(false)->unique()->change();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('payment_methods')) {
            return;
        }

        $hasUniqueIndex = collect(DB::select("SHOW INDEX FROM payment_methods WHERE Key_name = 'payment_methods_unique_number_unique'"))->isNotEmpty();

        Schema::table('payment_methods', function (Blueprint $table) use ($hasUniqueIndex): void {
            if (Schema::hasColumn('payment_methods', 'unique_number')) {
                if ($hasUniqueIndex) {
                    $table->dropUnique(['unique_number']);
                }
                $table->dropColumn('unique_number');
            }
            if (Schema::hasColumn('payment_methods', 'monthly_limit')) {
                $table->dropColumn('monthly_limit');
            }
        });
    }
};
