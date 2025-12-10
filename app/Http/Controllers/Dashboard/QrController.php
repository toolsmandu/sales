<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\QrCode;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class QrController extends Controller
{
    public function index(Request $request): View
    {
        $qrsQuery = QrCode::query()->latest();

        if (! $request->user()?->isAdmin()) {
            $qrsQuery->where('visible_to_employees', true);
        }

        $qrs = $qrsQuery->get();

        $methods = PaymentMethod::query()
            ->whereNotNull('unique_number')
            ->get(['id', 'label', 'unique_number', 'monthly_limit']);

        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        $monthlySales = Sale::query()
            ->selectRaw('payment_method_id, COALESCE(SUM(sales_amount), 0) as total_sales')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->groupBy('payment_method_id')
            ->pluck('total_sales', 'payment_method_id');

        $paymentMethodLimits = $methods->mapWithKeys(function (PaymentMethod $method) use ($monthlySales) {
            $limit = (float) $method->monthly_limit;
            $used = (float) ($monthlySales[$method->id] ?? 0);
            $available = $limit > 0 ? max($limit - $used, 0) : null;

            return [
                $method->unique_number => [
                    'label' => $method->label,
                    'monthly_limit' => $limit,
                    'used' => $used,
                    'available' => $available,
                ],
            ];
        })->all();

        return view('qr.index', [
            'qrs' => $qrs,
            'paymentMethodLimits' => $paymentMethodLimits,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'qr_image' => ['required', 'image', 'max:4096'],
            'description' => ['nullable', 'string', 'max:500'],
             'payment_method_number' => ['nullable', 'string', 'max:255'],
            'visible_to_employees' => ['nullable', 'boolean'],
        ]);

        $path = $request->file('qr_image')->store('qr-codes', 'public');

        QrCode::create([
            'name' => $data['name'],
            'file_path' => $path,
            'description' => $data['description'] ?? null,
            'payment_method_number' => $data['payment_method_number'] ?? null,
            'visible_to_employees' => $request->boolean('visible_to_employees', true),
        ]);

        return redirect()
            ->route('qr.scan')
            ->with('status', 'QR saved successfully.');
    }

    public function update(Request $request, QrCode $qrCode): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'qr_image' => ['nullable', 'image', 'max:4096'],
            'description' => ['nullable', 'string', 'max:500'],
            'payment_method_number' => ['nullable', 'string', 'max:255'],
            'visible_to_employees' => ['nullable', 'boolean'],
        ]);

        $payload = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'payment_method_number' => $data['payment_method_number'] ?? null,
            'visible_to_employees' => $request->boolean('visible_to_employees', $qrCode->visible_to_employees),
        ];

        if ($request->hasFile('qr_image')) {
            if ($qrCode->file_path && Storage::disk('public')->exists($qrCode->file_path)) {
                Storage::disk('public')->delete($qrCode->file_path);
            }

            $payload['file_path'] = $request->file('qr_image')->store('qr-codes', 'public');
        }

        $qrCode->update($payload);

        return redirect()
            ->route('qr.scan')
            ->with('status', 'QR updated successfully.');
    }

    public function destroy(QrCode $qrCode): RedirectResponse
    {
        if ($qrCode->file_path && Storage::disk('public')->exists($qrCode->file_path)) {
            Storage::disk('public')->delete($qrCode->file_path);
        }

        $qrCode->delete();

        return redirect()
            ->route('qr.scan')
            ->with('status', 'QR deleted successfully.');
    }
}
