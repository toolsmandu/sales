<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Illuminate\View\View;

class TrackingController extends Controller
{
    public function home(Request $request)
    {
        if ($request->user()) {
            return redirect()->route('dashboard');
        }

        $trackingState = [];
        $orders = collect();

        if ($request->isMethod('post')) {
            if ($request->boolean('reset')) {
                return $this->renderHome([], collect());
            }
            if ($request->filled('otp')) {
                return $this->handleVerify($request, $trackingState, $orders);
            }

            return $this->handleSend($request, $trackingState, $orders);
        }

        return $this->renderHome($trackingState, $orders);
    }

    private function handleSend(Request $request, array $trackingState, $orders)
    {
        $phoneInput = (string) $request->input('phone', '');
        $normalizedPhone = preg_replace('/\D+/', '', $phoneInput);
        $errors = new MessageBag();

        if ($normalizedPhone === '') {
            $errors->add('phone', 'Please enter a valid phone number.');

            return $this->renderHome([
                'phone_display' => $phoneInput,
            ], $orders, $errors);
        }

        $sale = Sale::query()
            ->whereRaw("REGEXP_REPLACE(phone, '[^0-9]+', '') = ?", [$normalizedPhone])
            ->whereNotNull('email')
            ->latest('purchase_date')
            ->latest('id')
            ->first();

        if (! $sale) {
            $errors->add('phone', 'No orders found for that phone number.');

            return $this->renderHome([
                'phone_display' => $phoneInput,
            ], $orders, $errors);
        }

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $maskedEmail = $this->maskEmail($sale->email);
        $cacheKey = $this->otpCacheKey($normalizedPhone);

        Cache::put($cacheKey, [
            'code' => $otp,
            'email' => $sale->email,
            'masked_email' => $maskedEmail,
            'phone' => $normalizedPhone,
        ], now()->addMinutes(10));

        Mail::raw(
            "Your verification code is {$otp} to track your order.",
            function ($message) use ($sale) {
                $message->to($sale->email)
                    ->subject('Your Verification Code');
            }
        );

        $trackingState = [
            'status' => 'otp_sent',
            'masked_email' => $maskedEmail,
            'phone_display' => $phoneInput,
        ];

        return $this->renderHome($trackingState, $orders);
    }

    private function handleVerify(Request $request, array $trackingState, $orders)
    {
        $phoneInput = (string) $request->input('phone', '');
        $otp = trim((string) $request->input('otp', ''));
        $normalizedPhone = preg_replace('/\D+/', '', $phoneInput);
        $errors = new MessageBag();

        if ($normalizedPhone === '') {
            $errors->add('phone', 'Please enter a valid phone number.');

            return $this->renderHome([
                'status' => 'otp_sent',
                'masked_email' => null,
                'phone_display' => $phoneInput,
            ], $orders, $errors);
        }

        $cacheKey = $this->otpCacheKey($normalizedPhone);
        $cached = Cache::get($cacheKey);

        if (! $cached || ($cached['code'] ?? null) !== $otp) {
            $errors->add('otp', 'Invalid or expired OTP. Please resend.');

            return $this->renderHome([
                'status' => 'otp_sent',
                'masked_email' => $cached['masked_email'] ?? null,
                'phone_display' => $phoneInput,
            ], $orders, $errors);
        }

        Cache::forget($cacheKey);

        $orders = Sale::query()
            ->whereRaw("REGEXP_REPLACE(phone, '[^0-9]+', '') = ?", [$normalizedPhone])
            ->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->get([
                'serial_number',
                'product_name',
                'purchase_date',
                'status',
                'email',
                'phone',
                'sales_amount',
                'id',
            ]);

        $trackingState = [
            'status' => 'verified',
            'masked_email' => $cached['masked_email'] ?? $this->maskEmail($cached['email'] ?? ''),
            'phone_display' => $phoneInput,
        ];

        return $this->renderHome($trackingState, $orders);
    }

    private function renderHome(array $trackingState, $orders, MessageBag $errors = null): View
    {
        $view = view('auth.login', [
            'isHomeLogin' => true,
            'trackingState' => $trackingState,
            'trackingOrders' => $orders,
        ]);

        if ($errors && $errors->isNotEmpty()) {
            $bag = new ViewErrorBag();
            $bag->put('default', $errors);
            $view->with('errors', $bag);
        }

        return $view;
    }

    private function maskEmail(string $email): string
    {
        if (! str_contains($email, '@')) {
            return $email;
        }

        [$user, $domain] = explode('@', $email, 2);
        $length = strlen($user);
        if ($length === 0) {
            return "***@{$domain}";
        }

        $hideCount = max(1, (int) ceil($length * 0.25));
        $start = max(0, (int) floor(($length - $hideCount) / 2));

        $maskedUser = substr($user, 0, $start)
            . str_repeat('*', $hideCount)
            . substr($user, $start + $hideCount);

        return "{$maskedUser}@{$domain}";
    }

    private function otpCacheKey(string $normalizedPhone): string
    {
        return "track_otp_{$normalizedPhone}";
    }
}
