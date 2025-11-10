<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;

class DashboardBootstrapController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $products = Product::query()
            ->with(['variations' => fn ($query) => $query->orderBy('name')])
            ->orderBy('name')
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'variations' => $product->variations->pluck('name')->values()->all(),
            ]);

        $paymentMethods = PaymentMethod::query()
            ->with(['transactions' => fn ($query) => $query->orderBy('occurred_at')->orderBy('id')])
            ->orderBy('label')
            ->get()
            ->map(function (PaymentMethod $method) {
                $running = 0;

                $transactions = $method->transactions->map(function ($transaction) use (&$running) {
                    $amount = (float) $transaction->amount;
                    $running += $transaction->type === 'income' ? $amount : -$amount;

                    return [
                        'id' => $transaction->id,
                        'sale_id' => $transaction->sale_id,
                        'type' => $transaction->type,
                        'amount' => $amount,
                        'phone' => $transaction->phone,
                        'occurred_at' => $transaction->occurred_at->toIso8601String(),
                        'balance_after' => $running,
                    ];
                });

                return [
                    'id' => $method->id,
                    'label' => $method->label,
                    'slug' => $method->slug,
                    'balance' => (float) $running,
                    'transactions' => $transactions,
                ];
            });

        $sales = Sale::query()
            ->with('paymentMethod')
            ->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Sale $sale) => [
                'id' => $sale->id,
                'serial_number' => $sale->serial_number,
                'purchase_date' => $sale->purchase_date->toDateString(),
                'product_name' => $sale->product_name,
                'remarks' => $sale->remarks,
                'phone' => $sale->phone,
                'email' => $sale->email,
                'sales_amount' => (float) $sale->sales_amount,
                'payment_method_slug' => $sale->paymentMethod?->slug,
                'payment_method_label' => $sale->paymentMethod?->label,
                'created_at' => $sale->created_at?->toIso8601String(),
            ]);

        return response()->json([
            'products' => $products,
            'payment_methods' => $paymentMethods,
            'sales' => $sales,
        ]);
    }
}
