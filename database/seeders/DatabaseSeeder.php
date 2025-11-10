<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Services\SerialNumberGenerator;
use Illuminate\Support\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $product = Product::firstOrCreate(['name' => 'Demo Product']);
        $product->variations()->firstOrCreate(['name' => 'Sample variation']);

        $defaultMethods = ['eSewa', 'Khalti', 'Bank Transfer'];

        foreach ($defaultMethods as $label) {
            PaymentMethod::firstOrCreate(
                ['slug' => PaymentMethod::generateSlug($label)],
                ['label' => $label]
            );
        }

        if (!Sale::exists()) {
            $primaryMethod = PaymentMethod::where('label', 'eSewa')->first();

            if ($primaryMethod) {
                $serials = app(SerialNumberGenerator::class);
                $purchaseDate = Carbon::now()->startOfDay();

                $sale = Sale::create([
                    'serial_number' => $serials->next(),
                    'purchase_date' => $purchaseDate,
                    'product_name' => 'Demo Product - Sample variation',
                    'phone' => '9800000000',
                    'email' => 'customer@example.com',
                    'sales_amount' => 4999,
                    'payment_method_id' => $primaryMethod->id,
                ]);

                PaymentTransaction::create([
                    'payment_method_id' => $primaryMethod->id,
                    'sale_id' => $sale->id,
                    'type' => 'income',
                    'amount' => $sale->sales_amount,
                    'phone' => $sale->phone,
                    'occurred_at' => $purchaseDate,
                ]);

                $primaryMethod->recalculateBalance();
            }
        }
    }
}
