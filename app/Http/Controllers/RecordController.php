<?php

namespace App\Http\Controllers;

use App\Models\RecordProduct;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Collection;

class RecordController extends Controller
{
    public function index(): View
    {
        $this->ensureLinkColumns();
        $products = RecordProduct::query()->orderBy('name')->get();
        $siteProducts = DB::table('products')->orderBy('name')->get();
        $variations = DB::table('product_variations')->orderBy('product_id')->orderBy('name')->get()->groupBy('product_id');

        return view('records.index', [
            'products' => $products,
            'siteProducts' => $siteProducts,
            'variations' => $variations,
        ]);
    }

    public function products(): JsonResponse
    {
        $this->ensureLinkColumns();
        $products = RecordProduct::query()->orderBy('name')->get();

        return response()->json([
            'products' => $products,
        ]);
    }

    public function storeProduct(Request $request): JsonResponse
    {
        $this->ensureLinkColumns();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'linked_product_id' => ['nullable', 'integer', 'exists:products,id'],
            'linked_variation_ids' => ['array'],
            'linked_variation_ids.*' => ['integer'],
        ]);

        $requestedName = trim($validated['name']);
        $slug = Str::slug($requestedName) ?: Str::random(8);
        $tableSafeSlug = str_replace('-', '_', $slug);
        $tableName = 'record_' . $tableSafeSlug;
        $baseSlug = $slug;
        $baseTableName = $tableName;
        $suffix = 1;

        while (RecordProduct::where('slug', $slug)->exists() || Schema::hasTable($tableName)) {
            $slug = "{$baseSlug}-{$suffix}";
            $tableName = "{$baseTableName}_{$suffix}";
            $suffix++;
        }

        $this->createTableIfMissing($tableName);

        $product = RecordProduct::create([
            'name' => $requestedName,
            'slug' => $slug,
            'table_name' => $tableName,
            'linked_product_id' => $validated['linked_product_id'] ?? null,
            'linked_variation_ids' => !empty($validated['linked_variation_ids']) ? json_encode($validated['linked_variation_ids']) : null,
        ]);

        return response()->json([
            'product' => $product,
        ], Response::HTTP_CREATED);
    }

    public function listEntries(RecordProduct $recordProduct): JsonResponse
    {
        $tableName = $recordProduct->table_name;
        $this->createTableIfMissing($tableName);

        $records = DB::table($tableName)
            ->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->get()
            ->map(fn ($record) => $this->decryptSensitiveFields($record));

        return response()->json([
            'records' => $records,
        ]);
    }

    public function linkProduct(Request $request): JsonResponse
    {
        $this->ensureLinkColumns();
        $data = $request->validate([
            'record_product_id' => ['required', 'integer', 'exists:record_products,id'],
            'linked_product_id' => ['nullable', 'integer', 'exists:products,id'],
            'linked_variation_ids' => ['array'],
            'linked_variation_ids.*' => ['integer'],
        ]);

        $product = RecordProduct::findOrFail($data['record_product_id']);
        $product->update([
            'linked_product_id' => $data['linked_product_id'] ?? null,
            'linked_variation_ids' => !empty($data['linked_variation_ids']) ? json_encode($data['linked_variation_ids']) : null,
        ]);

        return response()->json([
            'product' => $product->fresh(),
        ]);
    }

    public function storeEntry(Request $request, RecordProduct $recordProduct): JsonResponse
    {
        $tableName = $recordProduct->table_name;
        $this->createTableIfMissing($tableName);

        $validated = $this->validateEntry($request);
        $now = Carbon::now();

        $payload = $this->normalizePayload($validated, $recordProduct->name);
        $payload['created_at'] = $now;
        $payload['updated_at'] = $now;

        $recordId = DB::table($tableName)->insertGetId($this->encryptSensitiveFields($payload));
        $record = $this->decryptSensitiveFields(DB::table($tableName)->find($recordId));

        return response()->json([
            'record' => $record,
        ], Response::HTTP_CREATED);
    }

    public function updateEntry(
        Request $request,
        RecordProduct $recordProduct,
        int $entryId
    ): JsonResponse {
        $tableName = $recordProduct->table_name;
        $this->createTableIfMissing($tableName);

        $existing = DB::table($tableName)->find($entryId);
        if (!$existing) {
            return response()->json([
                'message' => 'Record not found for this product.',
            ], Response::HTTP_NOT_FOUND);
        }

        $validated = $this->validateEntry($request, true);
        if (empty($validated)) {
            return response()->json([
                'message' => 'No changes provided.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $payload = $this->normalizePayload($validated, $recordProduct->name, true);
        $payload['updated_at'] = Carbon::now();

        DB::table($tableName)
            ->where('id', $entryId)
            ->update($this->encryptSensitiveFields($payload, true));

        $record = $this->decryptSensitiveFields(DB::table($tableName)->find($entryId));

        return response()->json([
            'record' => $record,
        ]);
    }

    public function deleteEntry(
        RecordProduct $recordProduct,
        int $entryId
    ): JsonResponse {
        if (auth()->user()?->role === 'employee') {
            return response()->json([
                'message' => 'Employees are not allowed to delete records.',
            ], Response::HTTP_FORBIDDEN);
        }

        $tableName = $recordProduct->table_name;
        $this->createTableIfMissing($tableName);

        $existing = DB::table($tableName)->find($entryId);
        if (!$existing) {
            return response()->json([
                'message' => 'Record not found for this product.',
            ], Response::HTTP_NOT_FOUND);
        }

        DB::table($tableName)->where('id', $entryId)->delete();

        return response()->json([
            'message' => 'Deleted',
        ]);
    }

    public function importEntries(Request $request, RecordProduct $recordProduct): JsonResponse
    {
        $tableName = $recordProduct->table_name;
        $this->createTableIfMissing($tableName);

        $request->validate([
            'file' => ['required', 'file'],
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, ['csv', 'txt'], true)) {
            return response()->json([
                'message' => 'Please upload a CSV file.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $handle = fopen($file->getRealPath(), 'r');
        if (! $handle) {
            return response()->json([
                'message' => 'Unable to read uploaded file.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $headers = [];
        $inserted = 0;

        try {
            if (($row = fgetcsv($handle, 0, ',')) !== false) {
                $headers = array_map(function ($header) {
                    return trim(mb_strtolower($header ?? ''));
                }, $row);
            }

            if (empty($headers)) {
                return response()->json([
                    'message' => 'CSV is missing headers.',
                ], Response::HTTP_BAD_REQUEST);
            }

            $allowed = [
                'purchase' => 'purchase_date',
                'purchase_date' => 'purchase_date',
                'email' => 'email',
                'password' => 'password',
                'phone' => 'phone',
                'product' => 'product',
                'sales_amount' => 'sales_amount',
                'price' => 'sales_amount',
                'expiry' => 'expiry',
                'period' => 'expiry',
                'remaining_days' => 'remaining_days',
                'remarks' => 'remarks',
                'two_factor' => 'two_factor',
                'email2' => 'email2',
                'password2' => 'password2',
            ];

            while (($row = fgetcsv($handle, 0, ',')) !== false) {
                $payload = [];
                foreach ($headers as $index => $header) {
                    $value = $row[$index] ?? null;
                    if (! array_key_exists($header, $allowed)) {
                        continue;
                    }
                    $column = $allowed[$header];
                    $payload[$column] = $value;
                }

                if (empty($payload)) {
                    continue;
                }

                if (isset($payload['purchase_date']) && $payload['purchase_date'] !== null) {
                    try {
                        $payload['purchase_date'] = Carbon::parse($payload['purchase_date'])->toDateString();
                    } catch (\Exception $e) {
                        $payload['purchase_date'] = null;
                    }
                }

                if (isset($payload['sales_amount'])) {
                    $payload['sales_amount'] = is_numeric($payload['sales_amount'])
                        ? (int) $payload['sales_amount']
                        : null;
                }

                if (isset($payload['expiry'])) {
                    $payload['expiry'] = is_numeric($payload['expiry'])
                        ? (int) $payload['expiry']
                        : null;
                }

                $payload['product'] = $payload['product'] ?? $recordProduct->name;
                $payload['created_at'] = Carbon::now();
                $payload['updated_at'] = Carbon::now();

                DB::table($tableName)->insert($this->encryptSensitiveFields($payload));
                $inserted++;
            }
        } finally {
            fclose($handle);
        }

        return response()->json([
            'inserted' => $inserted,
        ]);
    }

    private function validateEntry(Request $request, bool $partial = false): array
    {
        $rules = [
            'email' => [$partial ? 'sometimes' : 'required', 'nullable', 'string', 'max:255'],
            'password' => [$partial ? 'sometimes' : 'nullable', 'string', 'max:255'],
            'phone' => [$partial ? 'sometimes' : 'nullable', 'string', 'max:60'],
            'product' => [$partial ? 'sometimes' : 'nullable', 'string', 'max:190'],
            'sales_amount' => [$partial ? 'sometimes' : 'nullable', 'numeric', 'min:0'],
            'purchase_date' => [$partial ? 'sometimes' : 'required', 'date'],
            'expiry' => [$partial ? 'sometimes' : 'nullable', 'integer', 'min:0'],
            'remaining_days' => [$partial ? 'sometimes' : 'nullable', 'integer'],
            'remarks' => [$partial ? 'sometimes' : 'nullable', 'string', 'max:500'],
            'two_factor' => [$partial ? 'sometimes' : 'nullable', 'string', 'max:255'],
            'email2' => [$partial ? 'sometimes' : 'nullable', 'string', 'max:255'],
            'password2' => [$partial ? 'sometimes' : 'nullable', 'string', 'max:255'],
        ];

        return $request->validate($rules);
    }

    private function normalizePayload(array $data, string $productName, bool $partial = false): array
    {
        if (!$partial) {
            $data['product'] = $data['product'] ?? $productName;
        }

        if (isset($data['purchase_date'])) {
            $data['purchase_date'] = Carbon::parse($data['purchase_date'])->toDateString();
        }

        if (array_key_exists('sales_amount', $data)) {
            $data['sales_amount'] = $data['sales_amount'] === null || $data['sales_amount'] === ''
                ? null
                : (int) $data['sales_amount'];
        }

        return $data;
    }

    private function encryptSensitiveFields(array $payload, bool $partial = false): array
    {
        foreach (['password', 'password2'] as $field) {
            if ($partial && !array_key_exists($field, $payload)) {
                continue;
            }

            if (isset($payload[$field]) && $payload[$field] !== '') {
                $payload[$field] = Crypt::encryptString($payload[$field]);
            }
        }

        return $payload;
    }

    private function decryptSensitiveFields(object $record): object
    {
        foreach (['password', 'password2'] as $field) {
            if (!isset($record->{$field}) || $record->{$field} === null) {
                continue;
            }

            try {
                $record->{$field} = Crypt::decryptString($record->{$field});
            } catch (\Throwable $e) {
                Log::warning("Unable to decrypt {$field} for record", [
                    'record_id' => $record->id ?? null,
                    'error' => $e->getMessage(),
                ]);
                $record->{$field} = $record->{$field};
            }
        }

        return $record;
    }

    private function createTableIfMissing(string $tableName): void
    {
        $tableExists = Schema::hasTable($tableName);

        if (!$tableExists) {
            Schema::create($tableName, function (Blueprint $table): void {
                $table->id();
                $table->string('email')->nullable();
                $table->string('password')->nullable();
                $table->string('phone')->nullable();
                $table->string('product')->nullable();
                $table->integer('sales_amount')->nullable();
                $table->date('purchase_date')->nullable();
                $table->integer('expiry')->nullable(); // manual number input
                $table->integer('remaining_days')->nullable();
                $table->text('remarks')->nullable();
                $table->string('two_factor')->nullable();
                $table->string('email2')->nullable();
                $table->string('password2')->nullable();
                $table->timestamps();
            });
            return;
        }

        // Add any missing columns for existing tables
        if (!Schema::hasColumn($tableName, 'sales_amount')) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->integer('sales_amount')->nullable()->after('product');
            });
        }
    }

    private function ensureLinkColumns(): void
    {
        if (!Schema::hasTable('record_products')) {
            return;
        }

        if (!Schema::hasColumn('record_products', 'linked_product_id')) {
            Schema::table('record_products', function (Blueprint $table): void {
                $table->foreignId('linked_product_id')->nullable()->after('table_name')->constrained('products')->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('record_products', 'linked_variation_ids')) {
            Schema::table('record_products', function (Blueprint $table): void {
                $table->json('linked_variation_ids')->nullable()->after('linked_product_id');
            });
        }
    }
}
