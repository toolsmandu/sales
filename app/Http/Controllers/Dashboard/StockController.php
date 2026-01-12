<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockKey;
use App\Models\StockKeyView;
use App\Models\ProductVariation;
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
            ->with(['product:id,name', 'variation:id,notes'])
            ->fresh()
            ->latest('created_at')
            ->paginate(25, ['id', 'product_id', 'variation_id', 'activation_key', 'view_limit', 'view_count', 'created_at'], 'fresh_page')
            ->through(function (StockKey $key): StockKey {
                $activationKey = (string) $key->activation_key;
                $visible = substr($activationKey, 0, 5);
                $masked = $visible . str_repeat('*', max(strlen($activationKey) - 5, 0));
                $key->setAttribute('masked_activation_key', $masked);
                $key->setAttribute('original_activation_key', $activationKey);
                $key->makeHidden(['activation_key']);

                return $key;
            });

        $viewedKeys = StockKey::query()
            ->with([
                'product:id,name',
                'variation:id,notes',
                'viewedBy:id,name',
                'viewLogs' => fn ($q) => $q->with(['viewer:id,name'])->orderByDesc('viewed_at'),
            ])
            ->viewed()
            ->latest('viewed_at')
            ->paginate(25, ['id', 'product_id', 'variation_id', 'activation_key', 'view_limit', 'view_count', 'created_at', 'viewed_at', 'viewed_by_user_id', 'viewed_remarks'], 'viewed_page');

        $products = Product::query()
            ->with(['variations' => fn ($query) => $query->orderBy('name')])
            ->orderBy('name')
            ->get(['id', 'name']);
        $productOptions = $this->buildProductOptions($products);

        return view('stock.index', [
            'freshKeys' => $freshKeys,
            'viewedKeys' => $viewedKeys,
            'products' => $products,
            'productOptions' => $productOptions,
        ]);
    }

    public function createKeys(Request $request): View
    {
        $products = Product::query()
            ->with(['variations' => fn ($query) => $query->orderBy('name')])
            ->orderBy('name')
            ->get(['id', 'name']);
        $productOptions = $this->buildProductOptions($products);

        return view('stock.keys', [
            'products' => $products,
            'productOptions' => $productOptions,
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'variation_id' => ['nullable', 'integer', 'exists:product_variations,id'],
            'keys' => ['required', 'string'],
            'view_limit' => ['nullable', 'integer', 'min:1', 'max:1000'],
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
        $viewLimit = (int) ($data['view_limit'] ?? 1);
        $variationId = array_key_exists('variation_id', $data) ? (int) $data['variation_id'] : null;

        if ($variationId) {
            $variation = ProductVariation::query()->select('id', 'product_id')->find($variationId);
            if (!$variation || $variation->product_id !== (int) $data['product_id']) {
                throw ValidationException::withMessages([
                    'variation_id' => 'The selected variation does not match the product.',
                ]);
            }
        }

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
                'variation_id' => $variationId ?: null,
                'activation_key' => Crypt::encryptString($key),
                'view_limit' => $viewLimit,
                'view_count' => 0,
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
            'view_limit' => ['nullable', 'integer', 'min:1', 'max:1000'],
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

        if (array_key_exists('view_limit', $data) && $data['view_limit'] !== null) {
            $payload['view_limit'] = (int) $data['view_limit'];
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
        $viewLimit = (int) ($stockKey->view_limit ?? 1);
        $currentCount = (int) ($stockKey->view_count ?? 0);
        $nextCount = $currentCount + 1;
        $reachedLimit = $nextCount >= $viewLimit;

        $stockKey->view_count = $nextCount;

        if ($reachedLimit) {
            $stockKey->markAsViewed($user, $remarks);
        } else {
            $stockKey->save();
        }

        StockKeyView::create([
            'stock_key_id' => $stockKey->id,
            'viewed_by_user_id' => $user->id,
            'remarks' => $remarks,
            'viewed_at' => now(),
        ]);

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
            'view_limit' => $viewLimit,
            'view_count' => $nextCount,
            'view_logs' => $stockKey->viewLogs()
                ->with('viewer:id,name')
                ->orderByDesc('viewed_at')
                ->limit(20)
                ->get()
                ->map(fn (StockKeyView $log) => [
                    'viewed_at' => optional($log->viewed_at)->toIso8601String(),
                    'viewer' => $log->viewer?->name ?? '—',
                    'remarks' => $log->remarks ?? '—',
                ]),
        ]);
    }

    public function updateVariationNotes(Request $request, ProductVariation $variation): JsonResponse
    {
        $data = $request->validate([
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $notes = array_key_exists('notes', $data) ? trim((string) $data['notes']) : null;

        if ($notes === '') {
            $notes = null;
        }

        $variation->notes = $notes;
        $variation->save();

        return response()->json([
            'message' => 'Variation notes updated.',
            'notes' => $variation->notes,
        ]);
    }

    /**
     * Build a flattened list of products with variation labels for selectors.
     */
    private function buildProductOptions($products): array
    {
        return $products
            ->flatMap(function (Product $product) {
                $options = [];
                $baseName = trim((string) $product->name);

                if ($baseName !== '') {
                    $options[] = [
                        'id' => $product->id,
                        'label' => $baseName,
                        'variation_id' => null,
                        'notes' => null,
                    ];
                }

                foreach ($product->variations as $variation) {
                    $variationName = trim((string) $variation->name);
                    if ($variationName === '') {
                        continue;
                    }

                    $options[] = [
                        'id' => $product->id,
                        'label' => $baseName !== '' ? "{$baseName} - {$variationName}" : $variationName,
                        'variation_id' => $variation->id,
                        'notes' => $variation->notes,
                    ];
                }

                return $options;
            })
            ->filter(fn ($option) => !empty($option['label']))
            ->values()
            ->all();
    }
}
