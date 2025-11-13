<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\PaymentTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class WithdrawalController extends Controller
{
    public function index(Request $request): View
    {
        $paymentMethods = PaymentMethod::query()
            ->select('payment_methods.*')
            ->selectSub(
                PaymentTransaction::query()
                    ->selectRaw('COALESCE(SUM(CASE WHEN type = "income" THEN amount ELSE -amount END), 0)')
                    ->whereColumn('payment_transactions.payment_method_id', 'payment_methods.id'),
                'computed_balance'
            )
            ->orderBy('label')
            ->get()
            ->map(function (PaymentMethod $method) {
                $method->available_balance = (float) ($method->computed_balance ?? $method->balance ?? 0);
                return $method;
            });
        $perPage = (int) $request->query('per_page', 25);
        $perPage = in_array($perPage, [25, 50, 100], true) ? $perPage : 25;

        $withdrawals = PaymentTransaction::query()
            ->select('payment_transactions.*')
            ->selectRaw("
                COALESCE(
                    (
                        SELECT NULLIF(CAST(SUBSTRING(sales.serial_number, 3) AS UNSIGNED), 0)
                        FROM sales
                        WHERE sales.id = payment_transactions.sale_id
                            AND sales.serial_number LIKE 'TM%'
                        LIMIT 1
                    ),
                    payment_transactions.id
                ) AS serial_numeric
            ")
            ->with(['paymentMethod', 'sale'])
            ->where('type', 'expense')
            ->orderByDesc('serial_numeric')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('payments.withdraw', [
            'paymentMethods' => $paymentMethods,
            'withdrawals' => $withdrawals,
            'perPage' => $perPage,
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'payment_method' => ['required', 'string', 'exists:payment_methods,slug'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'date' => ['required', 'date_format:Y-m-d'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($data) {
            $method = PaymentMethod::where('slug', $data['payment_method'])
                ->lockForUpdate()
                ->firstOrFail();

            $amount = (float) $data['amount'];

            if ($amount > (float) $method->balance) {
                throw ValidationException::withMessages([
                    'amount' => 'Withdrawal amount exceeds the available balance.',
                ]);
            }

            PaymentTransaction::create([
                'payment_method_id' => $method->id,
                'type' => 'expense',
                'amount' => $amount,
                'phone' => $data['note'],
                'occurred_at' => Carbon::parse($data['date'])->startOfDay(),
            ]);

            $method->recalculateBalance();
        });

        $message = 'Withdrawal recorded successfully.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
            ]);
        }

        return redirect()
            ->route('payments.withdraw')
            ->with('status', $message);
    }
}
