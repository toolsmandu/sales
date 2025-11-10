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

        $products = Product::query()
            ->with(['variations' => fn ($query) => $query->orderBy('name')])
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        $productToEdit = null;
        if ($request->filled('edit')) {
            $productToEdit = Product::with(['variations' => fn ($query) => $query->orderBy('name')])
                ->find((int) $request->input('edit'));
        }

        return view('products.index', [
            'products' => $products,
            'productToEdit' => $productToEdit,
            'perPage' => $perPage,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);

        $product = Product::create(['name' => $data['name']]);

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

        $product->update(['name' => $data['name']]);

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
     * @return array{name: string, variations: array<int, string>}
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
            'variations' => ['nullable', 'array'],
            'variations.*' => ['nullable', 'string', 'max:255'],
            'product_variations' => ['nullable', 'array'],
            'product_variations.*' => ['nullable', 'string', 'max:255'],
        ]);

        $rawVariations = $data['variations'] ?? $data['product_variations'] ?? [];

        return [
            'name' => $data['name'],
            'variations' => collect($rawVariations)
                ->map(fn ($value) => is_string($value) ? trim($value) : '')
                ->filter()
                ->unique()
                ->values()
                ->all(),
        ];
    }

    private function syncVariations(Product $product, array $variations): void
    {
        $variations = array_values($variations);

        if (count($variations) === 0) {
            $product->variations()->delete();
            return;
        }

        $product->variations()
            ->whereNotIn('name', $variations)
            ->delete();

        foreach ($variations as $variation) {
            $product->variations()->firstOrCreate(['name' => $variation]);
        }
    }
}
