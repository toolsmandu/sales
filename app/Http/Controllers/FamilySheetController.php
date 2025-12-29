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
        $accountsById = collect();
        $hasFamilyProductId = Schema::hasColumn('family_accounts', 'family_product_id');
        $hasFamilyProductName = Schema::hasColumn('family_accounts', 'family_product_name');
        $hasMemberAccountId = Schema::hasColumn('family_members', 'family_account_id');
        $hasMemberProductId = Schema::hasColumn('family_members', 'family_product_id');
        $hasMemberProductName = Schema::hasColumn('family_members', 'family_product_name');

        if ($selectedProduct) {
            if ($hasMemberProductName && $hasFamilyProductName) {
                $this->backfillMemberProductNames();
            }

            $accountQuery = DB::table('family_accounts')
                ->orderByDesc('family_accounts.account_index')
                ->orderBy('family_accounts.name');

            if ($hasFamilyProductId) {
                $accountQuery->join('family_products', 'family_accounts.family_product_id', '=', 'family_products.id')
                    ->where('family_accounts.family_product_id', $selectedProduct->id)
                    ->select([
                        'family_accounts.*',
                        'family_products.name as product_name',
                        'family_accounts.family_product_name',
                    ]);
            } elseif ($hasFamilyProductName) {
                $accountQuery->where('family_accounts.family_product_name', $selectedProduct->name)
                    ->select([
                        'family_accounts.*',
                        'family_accounts.family_product_name',
                    ]);
            } else {
                $accountQuery->select(['family_accounts.*']);
            }

            $accounts = $accountQuery
                ->get()
                ->map(function ($account) use ($hasMemberAccountId) {
                    if ($hasMemberAccountId) {
                        $account->member_count = DB::table('family_members')
                            ->where('family_account_id', $account->id)
                            ->count();
                    } else {
                        $account->member_count = 0;
                    }
                    $account->remaining = max(0, ($account->capacity ?? 0) - $account->member_count);
                    return $account;
                });

            $accountsById = $accounts->keyBy('id');
            $accountIds = $accounts->pluck('id')->filter()->values();

            $maxIndexQuery = DB::table('family_accounts');
            if ($hasFamilyProductId) {
                $maxIndexQuery->where('family_product_id', $selectedProduct->id);
            } elseif ($hasFamilyProductName) {
                $maxIndexQuery->where('family_product_name', $selectedProduct->name);
            }
            $maxIndex = $maxIndexQuery->max('account_index');
            $nextAccountIndex = ($maxIndex ?? 0) + 1;

            // Build allowed product names for filtering members to linked variations only.
            $allowedProductNames = collect();
            $linkedProductId = $selectedProduct->linked_product_id ?? null;
            $linkedProduct = $linkedProductId ? $siteProducts->firstWhere('id', $linkedProductId) : null;
            $linkedProductName = $linkedProduct ? trim((string) $linkedProduct->name) : null;
            $linkedVariationIds = collect();
            if (!empty($selectedProduct->linked_variation_ids)) {
                $decoded = is_string($selectedProduct->linked_variation_ids)
                    ? json_decode($selectedProduct->linked_variation_ids, true)
                    : $selectedProduct->linked_variation_ids;
                $linkedVariationIds = collect(is_array($decoded) ? $decoded : []);
            }

            if ($linkedProductName !== null) {
                $allowedProductNames->push($linkedProductName);
                if ($linkedVariationIds->isNotEmpty()) {
                    $productVariations = $variations->get($selectedProduct->linked_product_id) ?? collect();
                    $allowedProductNames = $allowedProductNames->merge(
                        $linkedVariationIds
                            ->map(function ($id) use ($productVariations, $linkedProductName) {
                                $variation = $productVariations->firstWhere('id', $id);
                                return $variation ? trim($linkedProductName . ' - ' . $variation->name) : null;
                            })
                            ->filter()
                    );
                } else {
                    // If no specific variations linked, allow all variations of the linked product.
                    $productVariations = $variations->get($selectedProduct->linked_product_id) ?? collect();
                    $allowedProductNames = $allowedProductNames->merge(
                        $productVariations->map(fn ($variation) => trim($linkedProductName . ' - ' . $variation->name))
                    );
                }
            }
            $allowedProductNames = $allowedProductNames->filter()->unique()->values();

            $membersQuery = DB::table('family_members')
                ->orderByDesc('purchase_date')
                ->orderByDesc('id');

            if ($hasMemberAccountId && $accountIds->isNotEmpty()) {
                $membersQuery->whereIn('family_account_id', $accountIds)
                    ->orderBy('family_account_id');
            }
            if ($hasMemberProductId && $hasFamilyProductId) {
                $membersQuery->where('family_product_id', $selectedProduct->id);
            }
            if ($allowedProductNames->isNotEmpty()) {
                $membersQuery->whereIn('product', $allowedProductNames);
            }

            $membersByAccount = $membersQuery
                ->get()
                ->map(function ($member) use ($accountsById) {
                    $member = $this->decryptSensitiveFields($member);
                    $member->remaining_days = $this->computeRemainingDays($member->purchase_date ?? null, $member->expiry ?? null);
                    $account = $accountsById->get($member->family_account_id);
                    $member->family_name = $this->resolveFamilyName($account ?? null, [], $member);
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
        // Make sure linkage columns exist (in case they were removed).
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

        $data = $request->validate([
            'family_product_id' => ['required', 'integer', 'exists:family_products,id'],
            'linked_product_id' => ['nullable', 'integer', 'exists:products,id'],
            'linked_variation_ids' => ['array'],
            'linked_variation_ids.*' => ['integer'],
        ]);

        $linkedVariations = collect($data['linked_variation_ids'] ?? [])->map(function ($id) {
            return $id !== null ? (int) $id : null;
        });
        $linkedProductId = $data['linked_product_id'] ? (int) $data['linked_product_id'] : null;

        if ($linkedProductId) {
            $allowedVariationIds = DB::table('product_variations')
                ->where('product_id', $linkedProductId)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
            $linkedVariations = $linkedVariations
                ->filter()
                ->unique()
                ->filter(fn ($id) => in_array((int) $id, $allowedVariationIds, true));
        } else {
            // No linked product -> do not keep any variations.
            $linkedVariations = collect();
        }

        DB::table('family_products')
            ->where('id', $data['family_product_id'])
            ->update([
                'linked_product_id' => $linkedProductId,
                'linked_variation_ids' => $linkedVariations->isNotEmpty() ? json_encode($linkedVariations->values()->all()) : null,
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

        $hasFamilyProductId = Schema::hasColumn('family_accounts', 'family_product_id');

        $providedIndex = $data['account_index'] ?? null;
        $maxIndexQuery = DB::table('family_accounts');
        if ($hasFamilyProductId) {
            $maxIndexQuery->where('family_product_id', $data['family_product_id']);
        }
        $maxIndex = $maxIndexQuery->max('account_index');
        $autoIndex = ($maxIndex ?? 0) + 1;
        $accountIndex = $providedIndex ?: $autoIndex;

        $product = DB::table('family_products')->where('id', $data['family_product_id'])->first();
        if (!$product) {
            return back()->withErrors(['family_product_id' => 'Family product not found.']);
        }

        $payload = [
            'family_product_name' => $product->name,
            'name' => trim($data['name']),
            'capacity' => (int) $data['capacity'],
            'account_index' => $accountIndex,
            'remarks' => $data['remarks'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if ($hasFamilyProductId) {
            $payload['family_product_id'] = $data['family_product_id'];
        }

        DB::table('family_accounts')->insert($payload);

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
            ->route('family-sheet.index', ['product_id' => $account->family_product_id ?? null])
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
            ->when(Schema::hasColumn('family_members', 'family_account_id'), fn ($q) => $q->where('family_account_id', $account->id))
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
            ->route('family-sheet.index', ['product_id' => $account->family_product_id ?? null])
            ->with('status', 'Main account updated.');
    }

    public function storeMember(Request $request)
    {
        $data = array_merge([
            'email' => null,
            'password' => null,
            'order_id' => null,
            'family_name' => null,
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
            ->when(Schema::hasColumn('family_members', 'family_account_id'), fn ($q) => $q->where('family_account_id', $account->id))
            ->count();

        if ($account->capacity !== null && $memberCount >= $account->capacity) {
            return redirect()
                ->back()
                ->withErrors(['family_account_id' => 'This main account is full. Create a new main account to add more members.'])
                ->withInput();
        }

        $now = Carbon::now();
        $resolvedExpiry = $data['expiry'] ?? $this->inferExpiryDays($data['product'] ?? null);
        $hasMemberProductName = Schema::hasColumn('family_members', 'family_product_name');
        $hasMemberAccountName = Schema::hasColumn('family_members', 'account_name');

        $payload = $this->encryptSensitiveFields([
            'family_name' => $this->resolveFamilyName($account, $data, $member),
            'email' => $data['email'] ?? null,
            'password' => $data['password'] ?? null,
            'order_id' => $data['order_id'] ?? null,
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

        if ($hasMemberProductName) {
            $payload['family_product_name'] = $account->family_product_name ?? null;
        }
        if ($hasMemberAccountName) {
            $payload['account_name'] = $account->name ?? null;
        }
        if (Schema::hasColumn('family_members', 'family_product_id') && Schema::hasColumn('family_accounts', 'family_product_id')) {
            $payload['family_product_id'] = $account->family_product_id;
        }
        if (Schema::hasColumn('family_members', 'family_account_id')) {
            $payload['family_account_id'] = $account->id;
        }

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
            'order_id' => ['nullable', 'string', 'max:190'],
            'family_name' => ['nullable', 'string', 'max:190'],
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
            'order_id' => null,
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
        $hasMemberProductName = Schema::hasColumn('family_members', 'family_product_name');
        $hasMemberAccountName = Schema::hasColumn('family_members', 'account_name');

        $payload = $this->encryptSensitiveFields([
            'family_name' => $this->resolveFamilyName($account, $data),
            'email' => $data['email'] ?? null,
            'password' => $data['password'] ?? null,
            'order_id' => $data['order_id'] ?? null,
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

        if ($hasMemberProductName) {
            $payload['family_product_name'] = $account->family_product_name ?? null;
        }
        if ($hasMemberAccountName) {
            $payload['account_name'] = $account->name ?? null;
        }
        if (Schema::hasColumn('family_members', 'family_product_id') && Schema::hasColumn('family_accounts', 'family_product_id')) {
            $payload['family_product_id'] = $account->family_product_id;
        }
        if (Schema::hasColumn('family_members', 'family_account_id')) {
            $payload['family_account_id'] = $account->id;
        }

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
                $table->string('family_product_name')->nullable();
                $table->string('name');
                $table->integer('account_index')->nullable();
                $table->integer('capacity')->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();
            });
        } else {
            if (!Schema::hasColumn('family_accounts', 'family_product_name')) {
                Schema::table('family_accounts', function (Blueprint $table) {
                    $table->string('family_product_name')->nullable()->after('family_product_id');
                });
                $this->backfillFamilyProductNames();
            }
            if (Schema::hasColumn('family_accounts', 'family_product_name')) {
                $this->backfillFamilyProductNames();
            }
            if (!Schema::hasColumn('family_accounts', 'account_index')) {
                Schema::table('family_accounts', function (Blueprint $table) {
                    $table->integer('account_index')->nullable()->after('name');
                });
            }
            if (!Schema::hasColumn('family_accounts', 'remarks')) {
                Schema::table('family_accounts', function (Blueprint $table) {
                    $table->text('remarks')->nullable()->after('capacity');
                });
            }
        }

        if (!Schema::hasTable('family_members')) {
            Schema::create('family_members', function (Blueprint $table) {
                $table->id();
                $table->foreignId('family_product_id')->constrained('family_products')->cascadeOnDelete();
                $table->foreignId('family_account_id')->constrained('family_accounts')->cascadeOnDelete();
                $table->string('family_name')->nullable();
                $table->string('order_id')->nullable();
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
        if (!Schema::hasColumn('family_members', 'order_id')) {
            Schema::table('family_members', function (Blueprint $table) {
                $table->string('order_id')->nullable()->after('family_account_id');
            });
        }
        if (!Schema::hasColumn('family_members', 'family_name')) {
            Schema::table('family_members', function (Blueprint $table) {
                $table->string('family_name')->nullable()->after('family_account_id');
            });
        }
        if (!Schema::hasColumn('family_members', 'family_name')) {
            Schema::table('family_members', function (Blueprint $table) {
                $table->string('family_name')->nullable()->after('family_account_id');
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

    private function backfillMemberProductNames(): void
    {
        if (!Schema::hasColumn('family_members', 'family_product_name')
            || !Schema::hasColumn('family_members', 'family_account_id')
            || !Schema::hasColumn('family_accounts', 'family_product_name')) {
            return;
        }

        $rows = DB::table('family_members')
            ->leftJoin('family_accounts', 'family_members.family_account_id', '=', 'family_accounts.id')
            ->whereNull('family_members.family_product_name')
            ->select('family_members.id', 'family_accounts.family_product_name')
            ->get();

        foreach ($rows as $row) {
            if ($row->family_product_name !== null) {
                DB::table('family_members')
                    ->where('id', $row->id)
                    ->update([
                        'family_product_name' => $row->family_product_name,
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    private function backfillFamilyProductNames(): void
    {
        $accounts = DB::table('family_accounts')
            ->leftJoin('family_products', 'family_accounts.family_product_id', '=', 'family_products.id')
            ->where(function ($query) {
                $query->whereNull('family_accounts.family_product_name')
                    ->orWhere('family_accounts.family_product_name', '');
            })
            ->select(['family_accounts.id', 'family_products.name as product_name'])
            ->get();

        foreach ($accounts as $account) {
            if ($account->product_name !== null) {
                DB::table('family_accounts')
                    ->where('id', $account->id)
                    ->update([
                        'family_product_name' => $account->product_name,
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    private function encryptSensitiveFields(array $payload): array
    {
        foreach (['password', 'remarks'] as $field) {
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
        foreach (['password', 'remarks'] as $field) {
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

    private function resolveFamilyName(?object $account, array $data = [], ?object $member = null): ?string
    {
        // Always prefer the linked family account's name.
        return $account->name
            ?? ($data['family_name'] ?? null)
            ?? ($member->family_name ?? null);
    }
}
