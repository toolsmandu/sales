<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockKey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StockController extends Controller
{

    public function index(Request $request): View
    {
        $freshKeys = StockKey::query()
            ->with(['product:id,name'])
            ->fresh()
            ->latest('created_at')
            ->get(['id', 'product_id', 'activation_key', 'created_at'])
            ->map(function (StockKey $key): StockKey {
                $activationKey = (string) $key->activation_key;
                $visible = substr($activationKey, 0, 5);
                $masked = $visible . str_repeat('*', max(strlen($activationKey) - 5, 0));
                $key->setAttribute('masked_activation_key', $masked);
                $key->setAttribute('original_activation_key', $activationKey);
                $key->makeHidden(['activation_key']);

                return $key;
            });

        $viewedKeys = StockKey::query()
            ->with(['product:id,name', 'viewedBy:id,name'])
            ->viewed()
            ->latest('viewed_at')
            ->get(['id', 'product_id', 'activation_key', 'created_at', 'viewed_at', 'viewed_by_user_id', 'viewed_remarks']);

        $products = Product::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('stock.index', [
            'freshKeys' => $freshKeys,
            'viewedKeys' => $viewedKeys,
            'products' => $products,
        ]);
    }

    public function createKeys(Request $request): View
    {
        $products = Product::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('stock.keys', [
            'products' => $products,
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'keys' => ['required', 'string'],
        ]);

        $keys = collect(preg_split("/\r\n|\r|\n/", $data['keys']))
            ->map(fn ($value) => trim((string) $value))
            ->filter();

        if ($keys->isEmpty()) {
            throw ValidationException::withMessages([
                'keys' => 'Enter at least one activation key.',
            ]);
        }

        $uniqueKeys = $keys->unique()->values();

        $existingKeys = StockKey::query()
            ->pluck('activation_key')
            ->map(function ($value) {
                try {
                    return strtolower(Crypt::decryptString($value));
                } catch (\Throwable $e) {
                    return null;
                }
            })
            ->filter()
            ->all();

        $duplicates = [];
        $payload = [];
        $now = now();
        $seenKeys = [];

        foreach ($uniqueKeys as $key) {
            $normalized = strtolower($key);

            if (in_array($normalized, $seenKeys, true)) {
                $duplicates[] = $key;
                continue;
            }

            $seenKeys[] = $normalized;

            if (in_array($normalized, $existingKeys, true)) {
                $duplicates[] = $key;
                continue;
            }

            $payload[] = [
                'product_id' => (int) $data['product_id'],
                'activation_key' => Crypt::encryptString($key),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (empty($payload)) {
            $message = 'All of the provided keys already exist.';
            throw ValidationException::withMessages([
                'keys' => $message,
            ]);
        }

        StockKey::query()->insert($payload);

        $savedCount = count($payload);
        $duplicateMessage = '';

        if (!empty($duplicates)) {
            $duplicateList = implode(', ', $duplicates);
            $duplicateMessage = " The following keys were skipped because they were duplicates or already exist: {$duplicateList}.";
        }

        $message = "{$savedCount} activation key" . ($savedCount === 1 ? '' : 's') . ' added to stock.' . $duplicateMessage;

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
            ], 201);
        }

        return redirect()
            ->route('stock.keys.create')
            ->with('status', $message);
    }

    public function update(Request $request, StockKey $stockKey): JsonResponse
    {
        abort_unless($request->user()?->role === 'admin', 403);

        $data = $request->validate([
            'activation_key' => ['required', 'string', 'max:255'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
        ]);

        $activationKey = trim($data['activation_key']);
        $normalizedKey = strtolower($activationKey);

        $existingKeys = StockKey::query()
            ->whereKeyNot($stockKey->getKey())
            ->pluck('activation_key')
            ->map(function ($value) {
                try {
                    return strtolower(Crypt::decryptString($value));
                } catch (\Throwable $e) {
                    return null;
                }
            })
            ->filter()
            ->all();

        if (in_array($normalizedKey, $existingKeys, true)) {
            throw ValidationException::withMessages([
                'activation_key' => 'This activation key already exists.',
            ]);
        }

        $payload = ['activation_key' => $activationKey];

        if (array_key_exists('product_id', $data) && $data['product_id'] !== null) {
            $payload['product_id'] = (int) $data['product_id'];
        }

        $stockKey->fill($payload);

        if ($stockKey->isDirty()) {
            $stockKey->save();
        }

        $stockKey->loadMissing('product:id,name');

        $product = $stockKey->product;

        $activationKey = (string) $stockKey->activation_key;
        $masked = substr($activationKey, 0, 5) . str_repeat('*', max(strlen($activationKey) - 5, 0));

        return response()->json([
            'message' => 'Stock key updated successfully.',
            'activation_key' => $activationKey,
            'masked_key' => $masked,
            'product' => $product ? [
                'id' => $product->id,
                'name' => $product->name,
            ] : null,
        ]);
    }

    public function destroy(Request $request, StockKey $stockKey): JsonResponse
    {
        abort_unless($request->user()?->role === 'admin', 403);

        $stockKey->delete();

        return response()->json([
            'message' => 'Stock key deleted successfully.',
        ]);
    }

    public function reveal(Request $request, StockKey $stockKey): JsonResponse
    {
        $data = $request->validate([
            'remarks' => ['required', 'string', 'max:255', function (string $attribute, $value, $fail): void {
                if (trim((string) $value) === '') {
                    $fail('The remarks field must not be blank.');
                }
            }],
        ]);

        $user = $request->user();
        if (!$user) {
            abort(403);
        }

        $stockKey->loadMissing('product:id,name');

        $remarks = preg_replace('/\s+/u', ' ', trim($data['remarks']));

        $stockKey->markAsViewed($user, $remarks);

        return response()->json([
            'id' => $stockKey->id,
            'activation_key' => $stockKey->activation_key,
            'product' => [
                'id' => $stockKey->product->id,
                'name' => $stockKey->product->name,
            ],
            'viewer' => [
                'id' => $user->id,
                'name' => $user->name,
            ],
            'viewer_pin' => null,
            'viewed_at' => optional($stockKey->viewed_at)->toIso8601String(),
            'remarks' => $stockKey->viewed_remarks,
        ]);
    }

}
