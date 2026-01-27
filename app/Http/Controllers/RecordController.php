<?php

namespace App\Http\Controllers;

use App\Models\RecordProduct;
use App\Models\RecordTablePreference;
use App\Models\StockAccountEditLog;
use App\Models\StockProduct;
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
        $modelClass = $this->productModelClass();
        $products = $modelClass::query()->orderBy('name')->get();
        $siteProducts = DB::table('products')->orderBy('name')->get();
        $variations = DB::table('product_variations')->orderBy('product_id')->orderBy('name')->get()->groupBy('product_id');
        $viewName = request()->routeIs('stock-account.*') ? 'stock-account.index' : 'records.index';

        return view($viewName, [
            'products' => $products,
            'siteProducts' => $siteProducts,
            'variations' => $variations,
        ]);
    }

    public function products(): JsonResponse
    {
        $this->ensureLinkColumns();
        $modelClass = $this->productModelClass();
        $products = $modelClass::query()->orderBy('name')->get();

        return response()->json([
            'products' => $products,
        ]);
    }

    public function storeProduct(Request $request): JsonResponse
    {
        $this->ensureLinkColumns();
        $rules = [
            'name' => ['required', 'string', 'max:190'],
            'linked_product_id' => ['nullable', 'integer', 'exists:products,id'],
            'linked_variation_ids' => ['array', 'max:1'],
            'linked_variation_ids.*' => ['integer'],
        ];
        if ($this->isStockContext()) {
            $rules['expiry_days'] = ['nullable', 'integer', 'min:0'];
        }
        $validated = $request->validate($rules);

        $requestedName = trim($validated['name']);
        $slug = Str::slug($requestedName) ?: Str::random(8);
        $tableSafeSlug = str_replace('-', '_', $slug);
        $modelClass = $this->productModelClass();
        $tablePrefix = $this->productTablePrefix();
        $tableName = $tablePrefix . $tableSafeSlug;
        $baseSlug = $slug;
        $baseTableName = $tableName;
        $suffix = 1;

        while ($modelClass::where('slug', $slug)->exists() || Schema::hasTable($tableName)) {
            $slug = "{$baseSlug}-{$suffix}";
            $tableName = "{$baseTableName}_{$suffix}";
            $suffix++;
        }

        $this->createTableIfMissing($tableName);

        $payload = [
            'name' => $requestedName,
            'slug' => $slug,
            'table_name' => $tableName,
            'linked_product_id' => $validated['linked_product_id'] ?? null,
            'linked_variation_ids' => !empty($validated['linked_variation_ids'])
                ? array_slice($validated['linked_variation_ids'], 0, 1)
                : null,
        ];
        if ($this->isStockContext()) {
            $payload['expiry_days'] = $validated['expiry_days'] ?? null;
        }

        $product = $modelClass::create($payload);

        return response()->json([
            'product' => $product,
        ], Response::HTTP_CREATED);
    }

    public function updateProduct(Request $request, string $recordProduct): JsonResponse
    {
        if (!$this->isStockContext()) {
            return response()->json([
                'message' => 'Not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        $this->ensureLinkColumns();
        $product = $this->findProduct($recordProduct);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'expiry_days' => ['nullable', 'integer', 'min:0'],
            'stock_account_note' => ['nullable', 'string', 'max:5000'],
        ]);
        $note = array_key_exists('stock_account_note', $validated)
            ? trim((string) $validated['stock_account_note'])
            : null;
        if ($note === '') {
            $note = null;
        }
        if ($note !== null) {
            $wordCount = str_word_count(strip_tags($note));
            if ($wordCount > 500) {
                return response()->json([
                    'message' => 'Stock Account Note must be 500 words or fewer.',
                    'errors' => ['stock_account_note' => ['Stock Account Note must be 500 words or fewer.']],
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $payload = [
            'name' => trim($validated['name']),
            'expiry_days' => $validated['expiry_days'] ?? null,
        ];
        if ($this->isStockContext() && array_key_exists('stock_account_note', $validated)) {
            $payload['stock_account_note'] = $note;
        }
        $product->update($payload);

        return response()->json([
            'product' => $product->fresh(),
        ]);
    }

    public function listEntries(string $recordProduct): JsonResponse
    {
        $product = $this->findProduct($recordProduct);
        $tableName = $product->table_name;
        $this->createTableIfMissing($tableName);
        $this->ensureStockIndexes($tableName);

        $records = DB::table($tableName)
            ->orderByRaw('remaining_days is null')
            ->orderBy('remaining_days')
            ->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->get()
            ->map(fn ($record) => $this->decryptSensitiveFields($record));

        return response()->json([
            'records' => $records,
        ]);
    }

    public function tablePreferences(string $recordProduct): JsonResponse
    {
        $this->ensurePreferencesTable();
        $product = $this->findProduct($recordProduct);
        $context = $this->preferencesContext();

        $specific = RecordTablePreference::query()
            ->where('context', $context)
            ->where('record_product_id', $product->id)
            ->first();

        $global = RecordTablePreference::query()
            ->where('context', $context)
            ->whereNull('record_product_id')
            ->first();

        return response()->json([
            'preferences' => $specific?->preferences,
            'global' => $global?->preferences,
        ]);
    }

    public function updateTablePreferences(Request $request, string $recordProduct): JsonResponse
    {
        $this->ensurePreferencesTable();
        $product = $this->findProduct($recordProduct);
        $context = $this->preferencesContext();

        $validated = $request->validate([
            'columnOrder' => ['present', 'array'],
            'hiddenColumns' => ['present', 'array'],
            'columnWidths' => ['present', 'array'],
        ]);

        $preference = RecordTablePreference::query()->updateOrCreate(
            [
                'context' => $context,
                'record_product_id' => $product->id,
            ],
            [
                'preferences' => [
                    'columnOrder' => array_values($validated['columnOrder']),
                    'hiddenColumns' => array_values($validated['hiddenColumns']),
                    'columnWidths' => $validated['columnWidths'],
                ],
            ]
        );

        return response()->json([
            'preferences' => $preference->preferences,
        ]);
    }

    public function lastSelectedProduct(): JsonResponse
    {
        $this->ensurePreferencesTable();
        $context = $this->preferencesContext();
        $global = RecordTablePreference::query()
            ->where('context', $context)
            ->whereNull('record_product_id')
            ->first();

        return response()->json([
            'last_product_id' => $global?->preferences['last_product_id'] ?? null,
        ]);
    }

    public function updateLastSelectedProduct(Request $request): JsonResponse
    {
        $this->ensurePreferencesTable();
        $context = $this->preferencesContext();
        $validated = $request->validate([
            'last_product_id' => ['nullable', 'integer'],
        ]);

        $preference = RecordTablePreference::query()->firstOrNew([
            'context' => $context,
            'record_product_id' => null,
        ]);

        $preferences = $preference->preferences ?? [];
        $preferences['last_product_id'] = $validated['last_product_id'] ?? null;
        $preference->preferences = $preferences;
        $preference->save();

        return response()->json([
            'last_product_id' => $preference->preferences['last_product_id'] ?? null,
        ]);
    }

    public function exportEntries(string $recordProduct)
    {
        $product = $this->findProduct($recordProduct);
        $tableName = $product->table_name;
        $this->createTableIfMissing($tableName);
        $this->ensureStockIndexes($tableName);

        $records = DB::table($tableName)
            ->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->get()
            ->map(fn ($record) => $this->decryptSensitiveFields($record));

        $headers = [
            'serial_number',
            'purchase_date',
            'product',
            'email',
            'password',
            'phone',
            'sales_amount',
            'expiry',
            'remaining_days',
            'remarks',
            'two_factor',
            'email2',
            'password2',
        ];

        $filename = sprintf('sheet-%s-%s.csv', $product->id, Carbon::now()->format('Ymd_His'));

        return response()->streamDownload(function () use ($headers, $records) {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            fputcsv($handle, $headers);

            foreach ($records as $record) {
                $remaining = $record->remaining_days ?? $this->computeRemainingDays(
                    $record->purchase_date ?? null,
                    $record->expiry ?? null
                );

                fputcsv($handle, [
                    $record->serial_number ?? null,
                    $record->purchase_date ?? null,
                    $record->product ?? null,
                    $record->email ?? null,
                    $record->password ?? null,
                    $record->phone ?? null,
                    $record->sales_amount ?? null,
                    $record->expiry ?? null,
                    $remaining,
                    $record->remarks ?? null,
                    $record->two_factor ?? null,
                    $record->email2 ?? null,
                    $record->password2 ?? null,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function linkProduct(Request $request): JsonResponse
    {
        $this->ensureLinkColumns();
        $tableName = $this->productTableName();
        $data = $request->validate([
            'record_product_id' => ['required', 'integer', "exists:{$tableName},id"],
            'linked_product_id' => ['nullable', 'integer', 'exists:products,id'],
            'linked_variation_ids' => ['array', 'max:1'],
            'linked_variation_ids.*' => ['integer'],
            'stock_account_note' => ['nullable', 'string', 'max:5000'],
        ]);
        if ($this->isStockContext() && !empty($data['stock_account_note'])) {
            $wordCount = str_word_count(strip_tags((string) $data['stock_account_note']));
            if ($wordCount > 500) {
                return response()->json([
                    'message' => 'Stock Account Note must be 500 words or fewer.',
                    'errors' => ['stock_account_note' => ['Stock Account Note must be 500 words or fewer.']],
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $product = $this->findProduct((string) $data['record_product_id']);
        $variationIds = !empty($data['linked_variation_ids'])
            ? array_slice($data['linked_variation_ids'], 0, 1)
            : [];
        $payload = [
            'linked_product_id' => $data['linked_product_id'] ?? null,
            'linked_variation_ids' => !empty($variationIds) ? $variationIds : null,
        ];
        if ($this->isStockContext()) {
            $payload['stock_account_note'] = $data['stock_account_note'] ?? null;
        }
        $product->update($payload);

        return response()->json([
            'product' => $product->fresh(),
        ]);
    }

    public function storeEntry(Request $request, string $recordProduct): JsonResponse
    {
        $product = $this->findProduct($recordProduct);
        $tableName = $product->table_name;
        $this->createTableIfMissing($tableName);
        $this->ensureSaleSyncColumns();

        $validated = $this->validateEntry($request);
        $now = Carbon::now();

        $payload = $this->normalizePayload($validated, $product->name);
        if ($this->isStockContext() && $this->isStockTable($tableName)) {
            $payload['stock_index'] = $this->nextStockIndex($tableName);
        }
        $payload['created_at'] = $now;
        $payload['updated_at'] = $now;

        $recordId = DB::table($tableName)->insertGetId($this->encryptSensitiveFields($payload));
        $record = $this->decryptSensitiveFields(DB::table($tableName)->find($recordId));
        $this->markSheetSynced($record->serial_number ?? null);

        return response()->json([
            'record' => $record,
        ], Response::HTTP_CREATED);
    }

    public function updateEntry(
        Request $request,
        string $recordProduct,
        int $entryId
    ): JsonResponse {
        $product = $this->findProduct($recordProduct);
        $tableName = $product->table_name;
        $this->createTableIfMissing($tableName);

        $existing = DB::table($tableName)->find($entryId);
        if (!$existing) {
            return response()->json([
                'message' => 'Record not found for this product.',
            ], Response::HTTP_NOT_FOUND);
        }
        $existing = $this->decryptSensitiveFields($existing);

        $validated = $this->validateEntry($request, true);
        if (empty($validated)) {
            return response()->json([
                'message' => 'No changes provided.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $payload = $this->normalizePayload($validated, $product->name, true);
        $context = $this->preferencesContext();
        $trackedFields = $this->isStockContext()
            ? ['purchase_date', 'email', 'password', 'phone']
            : array_keys($payload);
        $changesForLog = [];
        foreach ($trackedFields as $field) {
            if (!array_key_exists($field, $payload)) {
                continue;
            }
            $oldValue = (string) ($existing->{$field} ?? '');
            $newValue = (string) ($payload[$field] ?? '');
            if ($oldValue !== $newValue) {
                $changesForLog[] = [
                    'field' => $field,
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }
        $payload['updated_at'] = Carbon::now();

        DB::table($tableName)
            ->where('id', $entryId)
            ->update($this->encryptSensitiveFields($payload, true));

        $record = $this->decryptSensitiveFields(DB::table($tableName)->find($entryId));
        $this->markSheetSynced($record->serial_number ?? null);
        $this->logRecordChanges(
            $request,
            $context,
            $product->name ?? 'Unknown',
            $record->stock_index ?? $existing->stock_index ?? $record->serial_number ?? null,
            $changesForLog
        );

        return response()->json([
            'record' => $record,
        ]);
    }

    public function deleteEntry(
        string $recordProduct,
        int $entryId
    ): JsonResponse {
        if (auth()->user()?->role === 'employee') {
            return response()->json([
                'message' => 'Employees are not allowed to delete records.',
            ], Response::HTTP_FORBIDDEN);
        }

        $product = $this->findProduct($recordProduct);
        $tableName = $product->table_name;
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

    public function importEntries(Request $request, string $recordProduct): JsonResponse
    {
        $product = $this->findProduct($recordProduct);
        $tableName = $product->table_name;
        $this->createTableIfMissing($tableName);
        $this->ensureSaleSyncColumns();
        $nextStockIndex = ($this->isStockContext() && $this->isStockTable($tableName))
            ? $this->nextStockIndex($tableName)
            : null;

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
        $syncedSerials = [];

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

                $payload['product'] = $payload['product'] ?? $product->name;
                if ($nextStockIndex !== null) {
                    $payload['stock_index'] = $nextStockIndex;
                    $nextStockIndex++;
                }
                $payload['created_at'] = Carbon::now();
                $payload['updated_at'] = Carbon::now();

                DB::table($tableName)->insert($this->encryptSensitiveFields($payload));
                if (!empty($payload['serial_number'])) {
                    $syncedSerials[] = trim((string) $payload['serial_number']);
                }
                $inserted++;
            }
        } finally {
            fclose($handle);
        }

        $this->markSheetSyncedMany($syncedSerials);

        return response()->json([
            'inserted' => $inserted,
        ]);
    }

    private function validateEntry(Request $request, bool $partial = false): array
    {
        $rules = [
            'serial_number' => [$partial ? 'sometimes' : 'nullable', 'nullable', 'max:190'],
            'email' => [$partial ? 'sometimes' : 'required', 'nullable', 'string', 'max:255'],
            'password' => [$partial ? 'sometimes' : 'nullable', 'string', 'max:255'],
            'phone' => [$partial ? 'sometimes' : 'nullable', 'nullable', 'string', 'max:60'],
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

    private function computeRemainingDays(?string $purchaseDate, $expiryDays): ?int
    {
        if (empty($purchaseDate) || $expiryDays === null || $expiryDays === '') {
            return null;
        }

        try {
            $endDate = Carbon::parse($purchaseDate)->startOfDay()->addDays((int) $expiryDays);
            return Carbon::today()->startOfDay()->diffInDays($endDate, false);
        } catch (\Throwable $e) {
            return null;
        }
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

    private function logRecordChanges(
        Request $request,
        string $context,
        string $stockName,
        $stockIndex,
        array $changes
    ): void {
        if (!Schema::hasTable('stock_account_edit_logs')) {
            return;
        }
        if (empty($changes)) {
            return;
        }

        $actor = $request->user();
        $actorName = $actor?->name ?? 'User';
        $actorId = $actor?->id;
        $indexLabel = $stockIndex === null || $stockIndex === '' ? 'N/A' : $stockIndex;
        $indexTitle = $context === 'stock-account' ? 'Index Value' : 'Order ID';

        foreach ($changes as $change) {
            $oldValue = trim((string) ($change['old'] ?? ''));
            $newValue = trim((string) ($change['new'] ?? ''));
            $message = $actorName . " changed following information.\n\n"
                . "Stock Name: {$stockName}\n"
                . "{$indexTitle}: {$indexLabel}\n"
                . "Old Data: " . ($oldValue !== '' ? $oldValue : 'N/A') . "\n"
                . "New Data: " . ($newValue !== '' ? $newValue : 'N/A');

            StockAccountEditLog::create([
                'actor_id' => $actorId,
                'context' => $context,
                'message' => $message,
            ]);
        }
    }

    private function isStockContext(): bool
    {
        return request()->routeIs('stock-account.*');
    }

    private function preferencesContext(): string
    {
        return $this->isStockContext() ? 'stock-account' : 'sheet';
    }

    private function ensurePreferencesTable(): void
    {
        if (Schema::hasTable('record_table_preferences')) {
            return;
        }

        Schema::create('record_table_preferences', function (Blueprint $table) {
            $table->id();
            $table->string('context', 32);
            $table->unsignedBigInteger('record_product_id')->nullable();
            $table->json('preferences');
            $table->timestamps();

            $table->unique(['context', 'record_product_id'], 'record_table_prefs_context_product_unique');
        });
    }

    private function productModelClass(): string
    {
        return $this->isStockContext() ? StockProduct::class : RecordProduct::class;
    }

    private function productTableName(): string
    {
        return $this->isStockContext() ? 'stock_products' : 'record_products';
    }

    private function productTablePrefix(): string
    {
        return $this->isStockContext() ? 'stock_' : 'record_';
    }

    private function findProduct(string $recordProduct): object
    {
        $modelClass = $this->productModelClass();

        return $modelClass::query()->findOrFail($recordProduct);
    }

    private function isStockTable(string $tableName): bool
    {
        return str_starts_with($tableName, 'stock_');
    }

    private function nextStockIndex(string $tableName): int
    {
        $max = DB::table($tableName)->max('stock_index');
        $maxValue = is_numeric($max) ? (int) $max : 0;

        return $maxValue + 1;
    }

    private function ensureStockIndexes(string $tableName): void
    {
        if (!$this->isStockTable($tableName)) {
            return;
        }

        $max = DB::table($tableName)->max('stock_index');
        $nextIndex = is_numeric($max) ? ((int) $max + 1) : 1;

        $rows = DB::table($tableName)
            ->whereNull('stock_index')
            ->orderBy('id')
            ->get(['id']);

        foreach ($rows as $row) {
            DB::table($tableName)
                ->where('id', $row->id)
                ->update(['stock_index' => $nextIndex]);
            $nextIndex++;
        }
    }

    private function createTableIfMissing(string $tableName): void
    {
        $tableExists = Schema::hasTable($tableName);
        $isStockTable = $this->isStockTable($tableName);

        if (!$tableExists) {
            Schema::create($tableName, function (Blueprint $table) use ($isStockTable): void {
                $table->id();
                if ($isStockTable) {
                    $table->integer('stock_index')->nullable();
                }
                $table->string('serial_number')->nullable();
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
        if ($isStockTable && !Schema::hasColumn($tableName, 'stock_index')) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->integer('stock_index')->nullable()->after('id');
            });
        }
        if (!Schema::hasColumn($tableName, 'sales_amount')) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->integer('sales_amount')->nullable()->after('product');
            });
        }

        if (!Schema::hasColumn($tableName, 'serial_number')) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->string('serial_number')->nullable()->after('id');
            });
        }
    }

    private function ensureLinkColumns(): void
    {
        $tableName = $this->productTableName();
        if (!Schema::hasTable($tableName)) {
            return;
        }

        if (!Schema::hasColumn($tableName, 'linked_product_id')) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->foreignId('linked_product_id')->nullable()->after('table_name')->constrained('products')->nullOnDelete();
            });
        }

        if (!Schema::hasColumn($tableName, 'linked_variation_ids')) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->json('linked_variation_ids')->nullable()->after('linked_product_id');
            });
        }

        if ($this->isStockContext() && !Schema::hasColumn($tableName, 'stock_account_note')) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->string('stock_account_note')->nullable()->after('linked_variation_ids');
            });
        }

        if ($this->isStockContext() && !Schema::hasColumn($tableName, 'expiry_days')) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->integer('expiry_days')->nullable()->after('stock_account_note');
            });
        }
    }

    private function ensureSaleSyncColumns(): void
    {
        if (!Schema::hasTable('sales')) {
            return;
        }

        if (!Schema::hasColumn('sales', 'sheet_sync_state')) {
            Schema::table('sales', function (Blueprint $table): void {
                $table->string('sheet_sync_state')->nullable()->after('status');
            });
        }
    }

    private function markSheetSynced(?string $serialNumber): void
    {
        $serial = trim((string) $serialNumber);
        if ($serial === '' || !Schema::hasTable('sales') || !Schema::hasColumn('sales', 'sheet_sync_state')) {
            return;
        }

        DB::table('sales')
            ->where('serial_number', $serial)
            ->update(['sheet_sync_state' => 'active']);
    }

    private function markSheetSyncedMany(array $serialNumbers): void
    {
        $serials = array_values(array_filter(array_map(static fn ($value) => trim((string) $value), $serialNumbers)));
        if (empty($serials) || !Schema::hasTable('sales') || !Schema::hasColumn('sales', 'sheet_sync_state')) {
            return;
        }

        DB::table('sales')
            ->whereIn('serial_number', array_unique($serials))
            ->update(['sheet_sync_state' => 'active']);
    }
}
