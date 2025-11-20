<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = (int) $request->query('per_page', 50);
        $perPage = in_array($perPage, [25, 50, 100, 200], true) ? $perPage : 50;

        $stockStatus = $request->query('stock_status', 'all');
        $stockStatus = in_array($stockStatus, ['in', 'out'], true) ? $stockStatus : 'all';

        $search = trim((string) $request->query('search'));

        $products = Product::query()
            ->with(['variations' => fn ($query) => $query->orderBy('name')])
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->when($stockStatus !== 'all', fn ($query) => $query->where('is_in_stock', $stockStatus === 'in'))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        $productOptions = Product::query()
            ->where('is_in_stock', true)
            ->with(['variations' => fn ($query) => $query
                ->where('is_in_stock', true)
                ->orderBy('name')])
            ->orderBy('name')
            ->get()
            ->flatMap(function (Product $product) {
                $name = trim($product->name);
                if ($name === '') {
                    return collect();
                }

                if ($product->variations->isEmpty()) {
                    return collect([
                        [
                            'label' => $name,
                            'expiry_days' => null,
                        ],
                    ]);
                }

                return $product->variations
                    ->map(function ($variation) use ($name) {
                        $variationName = trim((string) $variation->name);
                        if ($variationName === '') {
                            return null;
                        }

                        return [
                            'label' => sprintf('%s - %s', $name, $variationName),
                            'expiry_days' => $variation->expiry_days,
                        ];
                    })
                    ->filter()
                    ->values();
            })
            ->unique('label')
            ->values()
            ->all();

        $productToEdit = null;
        if ($request->filled('edit')) {
            $productToEdit = Product::with(['variations' => fn ($query) => $query->orderBy('name')])
                ->find((int) $request->input('edit'));
        }

        return view('products.index', [
            'products' => $products,
            'productToEdit' => $productToEdit,
            'perPage' => $perPage,
            'stockStatus' => $stockStatus,
            'search' => $search,
            'productOptions' => $productOptions,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);

        $product = Product::create([
            'name' => $data['name'],
            'is_in_stock' => $data['is_in_stock'],
        ]);

        $this->syncVariations($product, $data['variations']);

        $message = 'Product created successfully.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
            ], 201);
        }

        return redirect()
            ->route('products.index')
            ->with('status', $message);
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validatePayload($request, $product);

        $product->update([
            'name' => $data['name'],
            'is_in_stock' => $data['is_in_stock'],
        ]);

        $this->syncVariations($product, $data['variations']);

        $message = 'Product updated successfully.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
            ]);
        }

        return redirect()
            ->route('products.index')
            ->with('status', $message);
    }

    public function destroy(Request $request, Product $product)
    {
        $product->delete();

        $message = 'Product deleted successfully.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
            ]);
        }

        return redirect()
            ->route('products.index')
            ->with('status', $message);
    }

    /**
     * @return array{name: string, is_in_stock: bool, variations: array<int, array{name: string, expiry_days: ?int, is_in_stock: bool}>}
     */
    private function validatePayload(Request $request, ?Product $product = null): array
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products')->ignore($product?->id),
            ],
            'is_in_stock' => ['required', 'boolean'],
            'variations' => ['nullable', 'array'],
            'variations.*.name' => ['nullable', 'string', 'max:255'],
            'variations.*.expiry_days' => ['nullable', 'integer', 'min:0'],
            'variations.*.is_in_stock' => ['nullable', 'boolean'],
            'product_variations' => ['nullable', 'array'],
            'product_variations.*' => ['nullable', 'string', 'max:255'],
        ]);

        $structuredVariations = collect($data['variations'] ?? [])
            ->map(function ($value) {
                $name = is_array($value) ? ($value['name'] ?? '') : '';
                $expiry = is_array($value) ? ($value['expiry_days'] ?? null) : null;

                return [
                    'name' => trim((string) $name),
                    'expiry_days' => isset($expiry) && $expiry !== '' ? max(0, (int) $expiry) : null,
                    'is_in_stock' => $this->normalizeBoolean(is_array($value) ? ($value['is_in_stock'] ?? null) : null),
                ];
            })
            ->filter(fn (array $value) => $value['name'] !== '')
            ->values();

        if ($structuredVariations->isEmpty() && !empty($data['product_variations'])) {
            $structuredVariations = collect($data['product_variations'])
                ->map(fn ($value) => [
                    'name' => trim(is_string($value) ? $value : ''),
                    'expiry_days' => null,
                    'is_in_stock' => true,
                ])
                ->filter(fn (array $value) => $value['name'] !== '')
                ->values();
        }

        return [
            'name' => $data['name'],
            'is_in_stock' => $this->normalizeBoolean($data['is_in_stock']),
            'variations' => $structuredVariations
                ->unique(fn (array $value) => mb_strtolower($value['name']))
                ->values()
                ->all(),
        ];
    }

    private function syncVariations(Product $product, array $variations): void
    {
        if (count($variations) === 0) {
            $product->variations()->delete();
            return;
        }

        $variationNames = collect($variations)->pluck('name')->all();

        $product->variations()
            ->whereNotIn('name', $variationNames)
            ->delete();

        foreach ($variations as $variation) {
            $product->variations()->updateOrCreate(
                ['name' => $variation['name']],
                [
                    'expiry_days' => $variation['expiry_days'],
                    'is_in_stock' => $variation['is_in_stock'] ?? true,
                ]
            );
        }
    }

    private function normalizeBoolean(mixed $value, bool $default = true): bool
    {
        if ($value === null || $value === '') {
            return $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        $normalized = strtolower((string) $value);

        return in_array($normalized, ['1', 'true', 'on', 'yes'], true);
    }
}
