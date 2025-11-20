<?php

namespace App\Http\Controllers;

use App\Models\RecordProduct;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class RecordController extends Controller
{
    public function index(): View
    {
        $products = RecordProduct::query()->orderBy('name')->get();

        return view('records.index', [
            'products' => $products,
        ]);
    }

    public function products(): JsonResponse
    {
        $products = RecordProduct::query()->orderBy('name')->get();

        return response()->json([
            'products' => $products,
        ]);
    }

    public function storeProduct(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:190'],
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
            ->get();

        return response()->json([
            'records' => $records,
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

        $recordId = DB::table($tableName)->insertGetId($payload);
        $record = DB::table($tableName)->find($recordId);

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
            ->update($payload);

        $record = DB::table($tableName)->find($entryId);

        return response()->json([
            'record' => $record,
        ]);
    }

    public function deleteEntry(
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

        DB::table($tableName)->where('id', $entryId)->delete();

        return response()->json([
            'message' => 'Deleted',
        ]);
    }

    private function validateEntry(Request $request, bool $partial = false): array
    {
        $rules = [
            'email' => [$partial ? 'sometimes' : 'required', 'nullable', 'string', 'max:255'],
            'password' => [$partial ? 'sometimes' : 'nullable', 'string', 'max:255'],
            'phone' => [$partial ? 'sometimes' : 'nullable', 'string', 'max:60'],
            'product' => [$partial ? 'sometimes' : 'nullable', 'string', 'max:190'],
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

        return $data;
    }

    private function createTableIfMissing(string $tableName): void
    {
        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table): void {
            $table->id();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('phone')->nullable();
            $table->string('product')->nullable();
            $table->date('purchase_date')->nullable();
            $table->integer('expiry')->nullable(); // manual number input
            $table->integer('remaining_days')->nullable();
            $table->text('remarks')->nullable();
            $table->string('two_factor')->nullable();
            $table->string('email2')->nullable();
            $table->string('password2')->nullable();
            $table->timestamps();
        });
    }

}
