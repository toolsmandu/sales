<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Database\Schema\Blueprint;
use Symfony\Component\HttpFoundation\Response;

class FamilySheetController extends Controller
{
    public function __construct()
    {
        $this->ensureTables();
    }

    public function index(Request $request)
    {
        $products = DB::table('family_products')
            ->orderBy('name')
            ->get();
        $siteProducts = DB::table('products')->orderBy('name')->get();
        $variations = DB::table('product_variations')->orderBy('product_id')->orderBy('name')->get()->groupBy('product_id');

        $selectedProductId = (int) $request->query('product_id', $products->first()->id ?? 0);
        $selectedProduct = $selectedProductId ? $products->firstWhere('id', $selectedProductId) : null;
        if (!$selectedProduct && $products->count()) {
            $selectedProduct = $products->first();
            $selectedProductId = $selectedProduct->id;
        }
        $nextAccountIndex = 1;

        $accounts = collect();
        $membersByAccount = collect();

        if ($selectedProduct) {
            $accounts = DB::table('family_accounts')
                ->where('family_product_id', $selectedProduct->id)
                ->orderByDesc('account_index')
                ->orderBy('name')
                ->get()
                ->map(function ($account) {
                    $account->member_count = DB::table('family_members')
                        ->where('family_account_id', $account->id)
                        ->count();
                    $account->remaining = max(0, ($account->capacity ?? 0) - $account->member_count);
                    return $account;
                });

            $maxIndex = DB::table('family_accounts')
                ->where('family_product_id', $selectedProduct->id)
                ->max('account_index');
            $nextAccountIndex = ($maxIndex ?? 0) + 1;

            $membersByAccount = DB::table('family_members')
                ->where('family_product_id', $selectedProduct->id)
                ->orderBy('family_account_id')
                ->orderByDesc('purchase_date')
                ->orderByDesc('id')
                ->get()
                ->map(function ($member) {
                    $member = $this->decryptSensitiveFields($member);
                    $member->remaining_days = $this->computeRemainingDays($member->purchase_date ?? null, $member->expiry ?? null);
                    return $member;
                })
                ->groupBy('family_account_id');
        }

        return view('family-sheet.index', [
            'products' => $products,
            'selectedProduct' => $selectedProduct,
            'accounts' => $accounts,
            'membersByAccount' => $membersByAccount,
            'nextAccountIndex' => $nextAccountIndex,
            'siteProducts' => $siteProducts,
            'variations' => $variations,
        ]);
    }

    public function storeProduct(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'default_capacity' => ['nullable', 'integer', 'min:1'],
            'linked_product_id' => ['nullable', 'integer', 'exists:products,id'],
            'linked_variation_ids' => ['array'],
            'linked_variation_ids.*' => ['integer'],
        ]);

        $name = trim($data['name']);
        $slug = Str::slug($name) ?: Str::random(8);
        $baseSlug = $slug;
        $suffix = 1;

        while (DB::table('family_products')->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        DB::table('family_products')->insert([
            'name' => $name,
            'slug' => $slug,
            'default_capacity' => $data['default_capacity'] ?? null,
            'linked_product_id' => $data['linked_product_id'] ?? null,
            'linked_variation_ids' => !empty($data['linked_variation_ids']) ? json_encode($data['linked_variation_ids']) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('family-sheet.index')->with('status', 'Family product created.');
    }

    public function linkProduct(Request $request)
    {
        $data = $request->validate([
            'family_product_id' => ['required', 'integer', 'exists:family_products,id'],
            'linked_product_id' => ['nullable', 'integer', 'exists:products,id'],
            'linked_variation_ids' => ['array'],
            'linked_variation_ids.*' => ['integer'],
        ]);

        DB::table('family_products')
            ->where('id', $data['family_product_id'])
            ->update([
                'linked_product_id' => $data['linked_product_id'] ?? null,
                'linked_variation_ids' => !empty($data['linked_variation_ids']) ? json_encode($data['linked_variation_ids']) : null,
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('family-sheet.index', ['product_id' => $data['family_product_id']])
            ->with('status', 'Linking saved.');
    }

    public function storeAccount(Request $request)
    {
        $data = $request->validate([
            'family_product_id' => ['required', 'integer', 'exists:family_products,id'],
            'name' => ['required', 'string', 'max:190'],
            'capacity' => ['required', 'integer', 'min:1'],
            'account_index' => ['nullable', 'integer', 'min:1'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $providedIndex = $data['account_index'] ?? null;
        $maxIndex = DB::table('family_accounts')
            ->where('family_product_id', $data['family_product_id'])
            ->max('account_index');
        $autoIndex = ($maxIndex ?? 0) + 1;
        $accountIndex = $providedIndex ?: $autoIndex;

        DB::table('family_accounts')->insert([
            'family_product_id' => $data['family_product_id'],
            'name' => trim($data['name']),
            'capacity' => (int) $data['capacity'],
            'account_index' => $accountIndex,
            'remarks' => $data['remarks'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('family-sheet.index', ['product_id' => $data['family_product_id']])
            ->with('status', 'Main account created.');
    }

    public function destroyAccount(int $accountId)
    {
        $account = DB::table('family_accounts')->where('id', $accountId)->first();
        if (!$account) {
            return redirect()->back()->withErrors(['account' => 'Main account not found.']);
        }

        DB::table('family_accounts')->where('id', $accountId)->delete();

        return redirect()
            ->route('family-sheet.index', ['product_id' => $account->family_product_id])
            ->with('status', 'Main account deleted.');
    }

    public function updateAccount(Request $request, int $accountId)
    {
        $account = DB::table('family_accounts')->where('id', $accountId)->first();
        if (!$account) {
            return redirect()->back()->withErrors(['account' => 'Main account not found.']);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'capacity' => ['required', 'integer', 'min:1'],
            'account_index' => ['nullable', 'integer', 'min:1'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $accountIndex = $data['account_index'] ?? null;

        $memberCount = DB::table('family_members')
            ->where('family_account_id', $account->id)
            ->count();

        if ($memberCount > $data['capacity']) {
            return back()
                ->withInput()
                ->withErrors(['capacity' => "Capacity cannot be less than the current member count ({$memberCount})."]);
        }

        DB::table('family_accounts')
            ->where('id', $accountId)
            ->update([
                'name' => trim($data['name']),
                'capacity' => (int) $data['capacity'],
                'account_index' => $accountIndex,
                'remarks' => $data['remarks'] ?? null,
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('family-sheet.index', ['product_id' => $account->family_product_id])
            ->with('status', 'Main account updated.');
    }

    public function storeMember(Request $request)
    {
        $data = array_merge([
            'email' => null,
            'password' => null,
            'phone' => null,
            'product' => null,
            'sales_amount' => null,
            'purchase_date' => null,
            'expiry' => null,
            'remaining_days' => null,
            'remarks' => null,
            'two_factor' => null,
        ], $this->validateMember($request));

        $account = DB::table('family_accounts')->where('id', $data['family_account_id'])->first();
        if (!$account) {
            return back()->withErrors(['family_account_id' => 'Main account not found.']);
        }

        $memberCount = DB::table('family_members')
            ->where('family_account_id', $account->id)
            ->count();

        if ($account->capacity !== null && $memberCount >= $account->capacity) {
            return redirect()
                ->back()
                ->withErrors(['family_account_id' => 'This main account is full. Create a new main account to add more members.'])
                ->withInput();
        }

        $now = Carbon::now();
        $resolvedExpiry = $data['expiry'] ?? $this->inferExpiryDays($data['product'] ?? null);
        $payload = $this->encryptSensitiveFields([
            'family_product_id' => $account->family_product_id,
            'family_account_id' => $account->id,
            'email' => $data['email'] ?? null,
            'password' => $data['password'] ?? null,
            'phone' => $data['phone'] ?? null,
            'product' => $data['product'] ?? '',
            'sales_amount' => $data['sales_amount'] ?? null,
            'purchase_date' => !empty($data['purchase_date']) ? Carbon::parse($data['purchase_date'])->toDateString() : null,
            'expiry' => $resolvedExpiry,
            'remaining_days' => $this->computeRemainingDays($data['purchase_date'] ?? null, $resolvedExpiry),
            'remarks' => $data['remarks'] ?? null,
            'two_factor' => $data['two_factor'] ?? null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('family_members')->insert($payload);

        return redirect()
            ->route('family-sheet.index', ['product_id' => $account->family_product_id])
            ->with('status', 'Member added.');
    }

    private function validateMember(Request $request): array
    {
        return $request->validate([
            'family_account_id' => ['required', 'integer', 'exists:family_accounts,id'],
            'email' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:60'],
            'product' => ['nullable', 'string', 'max:190'],
            'sales_amount' => ['nullable', 'numeric', 'min:0'],
            'purchase_date' => ['nullable', 'date'],
            'expiry' => ['nullable', 'integer', 'min:0'],
            'remaining_days' => ['nullable', 'integer'],
            'remarks' => ['nullable', 'string', 'max:500'],
            'two_factor' => ['nullable', 'string', 'max:255'],
        ]);
    }

    public function updateMember(Request $request, int $memberId)
    {
        $member = DB::table('family_members')->where('id', $memberId)->first();
        if (!$member) {
            return redirect()->back()->withErrors(['member' => 'Member not found.']);
        }

        $data = array_merge([
            'email' => null,
            'password' => null,
            'phone' => null,
            'product' => null,
            'sales_amount' => null,
            'purchase_date' => null,
            'expiry' => null,
            'remaining_days' => null,
            'remarks' => null,
            'two_factor' => null,
        ], $this->validateMember($request));

        $account = DB::table('family_accounts')->where('id', $data['family_account_id'])->first();
        if (!$account) {
            return back()->withErrors(['family_account_id' => 'Main account not found.']);
        }

        $memberCount = DB::table('family_members')
            ->where('family_account_id', $account->id)
            ->when($account->id === $member->family_account_id, fn ($q) => $q->where('id', '!=', $member->id))
            ->count();

        if ($account->capacity !== null && $memberCount >= $account->capacity) {
            return redirect()
                ->back()
                ->withErrors(['family_account_id' => 'This main account is full. Create a new main account to add more members.'])
                ->withInput();
        }

        $resolvedExpiry = $data['expiry'] ?? $this->inferExpiryDays($data['product'] ?? null);
        $payload = $this->encryptSensitiveFields([
            'family_product_id' => $account->family_product_id,
            'family_account_id' => $account->id,
            'email' => $data['email'] ?? null,
            'password' => $data['password'] ?? null,
            'phone' => $data['phone'] ?? null,
            'product' => $data['product'] ?? '',
            'sales_amount' => $data['sales_amount'] ?? null,
            'purchase_date' => !empty($data['purchase_date']) ? Carbon::parse($data['purchase_date'])->toDateString() : null,
            'expiry' => $resolvedExpiry,
            'remaining_days' => $this->computeRemainingDays($data['purchase_date'] ?? null, $resolvedExpiry),
            'remarks' => $data['remarks'] ?? null,
            'two_factor' => $data['two_factor'] ?? null,
            'updated_at' => now(),
        ]);

        DB::table('family_members')->where('id', $memberId)->update($payload);

        return redirect()
            ->route('family-sheet.index', ['product_id' => $account->family_product_id])
            ->with('status', 'Member updated.');
    }

    private function inferExpiryDays(?string $productName): ?int
    {
        $normalized = trim((string) $productName);
        if ($normalized === '') {
            return null;
        }

        $row = DB::table('product_variations')
            ->join('products', 'product_variations.product_id', '=', 'products.id')
            ->whereRaw('LOWER(CONCAT(products.name, " - ", product_variations.name)) = ?', [mb_strtolower($normalized)])
            ->select('product_variations.expiry_days')
            ->first();

        return $row?->expiry_days !== null ? (int) $row->expiry_days : null;
    }

    private function computeRemainingDays(?string $purchaseDate, ?int $expiryDays): ?int
    {
        if (empty($purchaseDate) || $expiryDays === null) {
            return null;
        }

        try {
            $endDate = Carbon::parse($purchaseDate)->startOfDay()->addDays((int) $expiryDays);
            return Carbon::today()->startOfDay()->diffInDays($endDate, false);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function ensureTables(): void
    {
        if (!Schema::hasTable('family_products')) {
            Schema::create('family_products', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->integer('default_capacity')->nullable();
                $table->foreignId('linked_product_id')->nullable()->constrained('products')->nullOnDelete();
                $table->json('linked_variation_ids')->nullable();
                $table->timestamps();
            });
        } else {
            if (!Schema::hasColumn('family_products', 'linked_product_id')) {
                Schema::table('family_products', function (Blueprint $table) {
                    $table->foreignId('linked_product_id')->nullable()->after('default_capacity')->constrained('products')->nullOnDelete();
                });
            }
            if (!Schema::hasColumn('family_products', 'linked_variation_ids')) {
                Schema::table('family_products', function (Blueprint $table) {
                    $table->json('linked_variation_ids')->nullable()->after('linked_product_id');
                });
            }
        }

        if (!Schema::hasTable('family_accounts')) {
            Schema::create('family_accounts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('family_product_id')->constrained('family_products')->cascadeOnDelete();
                $table->string('name');
                $table->integer('account_index')->nullable();
                $table->integer('capacity')->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();
            });
        } elseif (!Schema::hasColumn('family_accounts', 'account_index')) {
            Schema::table('family_accounts', function (Blueprint $table) {
                $table->integer('account_index')->nullable()->after('name');
            });
        } elseif (!Schema::hasColumn('family_accounts', 'remarks')) {
            Schema::table('family_accounts', function (Blueprint $table) {
                $table->text('remarks')->nullable()->after('capacity');
            });
        }

        if (!Schema::hasTable('family_members')) {
            Schema::create('family_members', function (Blueprint $table) {
                $table->id();
                $table->foreignId('family_product_id')->constrained('family_products')->cascadeOnDelete();
                $table->foreignId('family_account_id')->constrained('family_accounts')->cascadeOnDelete();
                $table->string('email')->nullable();
                $table->string('password')->nullable();
                $table->string('phone')->nullable();
                $table->string('product')->nullable();
                $table->integer('sales_amount')->nullable();
                $table->date('purchase_date')->nullable();
                $table->integer('expiry')->nullable();
                $table->integer('remaining_days')->nullable();
                $table->text('remarks')->nullable();
                $table->string('two_factor')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }
    }

    private function encryptSensitiveFields(array $payload): array
    {
        foreach (['password'] as $field) {
            if (isset($payload[$field]) && $payload[$field] !== '') {
                try {
                    $payload[$field] = Crypt::encryptString($payload[$field]);
                } catch (\Throwable $e) {
                    Log::warning('Unable to encrypt field', ['field' => $field, 'error' => $e->getMessage()]);
                    $payload[$field] = $payload[$field];
                }
            }
        }

        return $payload;
    }

    private function decryptSensitiveFields(object $record): object
    {
        foreach (['password'] as $field) {
            if (!isset($record->{$field}) || $record->{$field} === null) {
                continue;
            }

            try {
                $record->{$field} = Crypt::decryptString($record->{$field});
            } catch (\Throwable $e) {
                Log::warning("Unable to decrypt {$field} for family member", [
                    'record_id' => $record->id ?? null,
                    'error' => $e->getMessage(),
                ]);
                $record->{$field} = $record->{$field};
            }
        }

        return $record;
    }
}
