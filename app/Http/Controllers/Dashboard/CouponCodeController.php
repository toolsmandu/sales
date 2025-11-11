<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\CouponCode;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CouponCodeController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $filters = [
            'product' => $request->integer('filter_product') ?: null,
            'code' => trim((string) $request->input('filter_code', '')),
        ];

        $couponsQuery = CouponCode::query()
            ->with('product')
            ->latest();

        if ($filters['product']) {
            $couponsQuery->where('product_id', $filters['product']);
        }

        if ($filters['code'] !== '') {
            $code = $filters['code'];
            $couponsQuery->where('code', 'like', '%' . $code . '%');
        }

        $coupons = $couponsQuery->paginate(25)->withQueryString();

        return view('coupons.index', [
            'products' => $products,
            'coupons' => $coupons,
            'filters' => $filters,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $entries = collect($request->input('coupon_entries', []))
            ->map(fn (array $entry) => [
                'code' => trim($entry['code'] ?? ''),
                'remarks' => trim($entry['remarks'] ?? ''),
            ])
            ->filter(fn (array $entry) => $entry['code'] !== '')
            ->values();

        if ($entries->isEmpty()) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['coupon_entries' => 'Add at least one coupon code before saving.']);
        }

        $request->merge(['coupon_entries' => $entries->all()]);

        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'coupon_entries' => ['required', 'array', 'min:1'],
            'coupon_entries.*.code' => [
                'required',
                'string',
                'max:255',
                'distinct',
                Rule::unique('coupon_codes', 'code'),
            ],
            'coupon_entries.*.remarks' => ['nullable', 'string', 'max:255'],
        ]);

        $timestamp = now();

        $payload = collect($data['coupon_entries'])
            ->map(fn (array $entry) => [
                'product_id' => $data['product_id'],
                'code' => $entry['code'],
                'remarks' => $entry['remarks'] ?: null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ])
            ->all();

        CouponCode::query()->insert($payload);

        return redirect()
            ->route('coupons.index')
            ->with('status', 'Coupon codes added successfully.');
    }

    public function update(Request $request, CouponCode $couponCode): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('coupon_codes', 'code')->ignore($couponCode->id),
            ],
            'remarks' => ['nullable', 'string', 'max:255'],
        ]);

        $couponCode->update($data);

        return redirect()
            ->route('coupons.index', request()->only('filter_product', 'filter_code'))
            ->with('status', 'Coupon updated successfully.');
    }

    public function destroy(CouponCode $couponCode): RedirectResponse
    {
        $couponCode->delete();

        return redirect()
            ->route('coupons.index', request()->only('filter_product', 'filter_code'))
            ->with('status', 'Coupon deleted successfully.');
    }
}
