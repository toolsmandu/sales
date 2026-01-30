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
use App\Models\FamilySheetPreference;
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
        $dataAccounts = collect();
        $membersByAccount = collect();
        $accountsById = collect();
        $dataAccountsById = collect();
        $hasFamilyProductId = Schema::hasColumn('family_accounts', 'family_product_id');
        $hasFamilyProductName = Schema::hasColumn('family_accounts', 'family_product_name');
        $hasMemberAccountId = Schema::hasColumn('family_members', 'family_account_id');
        $hasMemberProductId = Schema::hasColumn('family_members', 'family_product_id');
        $hasMemberProductName = Schema::hasColumn('family_members', 'family_product_name');
        $initialPageSize = 7;
        $pageSize = 5;
        $accountPage = max(1, (int) $request->query('account_page', 1));
        $dataAccountsHasMore = false;

        if ($selectedProduct) {
            if ($hasMemberProductName && $hasFamilyProductName) {
                $this->backfillMemberProductNames();
            }

            $accountQuery = DB::table('family_accounts')
                ->orderByDesc('family_accounts.account_index')
                ->orderBy('family_accounts.name');
            $accountSelect = ['family_accounts.*'];

            if ($hasFamilyProductId) {
                $accountQuery->join('family_products', 'family_accounts.family_product_id', '=', 'family_products.id')
                    ->where('family_accounts.family_product_id', $selectedProduct->id)
                    ->addSelect([
                        'family_products.name as product_name',
                        'family_accounts.family_product_name',
                    ]);
                $accountSelect = [
                    'family_accounts.*',
                    'family_products.name as product_name',
                    'family_accounts.family_product_name',
                ];
            } elseif ($hasFamilyProductName) {
                $accountQuery->where('family_accounts.family_product_name', $selectedProduct->name)
                    ->addSelect([
                        'family_accounts.family_product_name',
                    ]);
                $accountSelect = [
                    'family_accounts.*',
                    'family_accounts.family_product_name',
                ];
            }

            $accounts = (clone $accountQuery)
                ->select($accountSelect)
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

            $totalAccounts = (clone $accountQuery)
                ->distinct()
                ->count('family_accounts.id');
            $offset = $accountPage === 1
                ? 0
                : $initialPageSize + (($accountPage - 2) * $pageSize);
            $limit = $accountPage === 1 ? $initialPageSize : $pageSize;
            $dataAccountsHasMore = ($offset + $limit) < $totalAccounts;

            $dataAccounts = (clone $accountQuery)
                ->select($accountSelect)
                ->offset($offset)
                ->limit($limit)
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
            $dataAccountsById = $dataAccounts->keyBy('id');
            $accountIds = $dataAccounts->pluck('id')->filter()->values();

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
                $membersQuery->whereIn('product', $allowedProductNames)
                    ->orWhereNull('product')
                    ->orWhere('product', '');
            }

            $membersByAccount = $membersQuery
                ->get()
                ->map(function ($member) use ($dataAccountsById) {
                    $member = $this->decryptSensitiveFields($member);
                    $member->remaining_days = $this->computeRemainingDays($member->purchase_date ?? null, $member->expiry ?? null);
                    $account = $dataAccountsById->get($member->family_account_id);
                    $member->family_name = $this->resolveFamilyName($account ?? null, [], $member);
                    return $member;
                })
                ->groupBy('family_account_id');
        }

        if ($request->boolean('accounts_partial')) {
            $familyColumns = [
                ['id' => 'account', 'label' => 'Main Account'],
                ['id' => 'family_name', 'label' => 'Family Name'],
                ['id' => 'order', 'label' => 'Order ID'],
                ['id' => 'email', 'label' => 'Email'],
                ['id' => 'phone', 'label' => 'Phone'],
                ['id' => 'purchase', 'label' => 'Purchase Date'],
                ['id' => 'period', 'label' => 'Period'],
                ['id' => 'remaining', 'label' => 'Remaining Days'],
                ['id' => 'remarks', 'label' => 'Remarks'],
            ];

            return response()->json([
                'html' => view('family-sheet.partials.data-rows', [
                    'dataAccounts' => $dataAccounts,
                    'membersByAccount' => $membersByAccount,
                    'familyColumns' => $familyColumns,
                ])->render(),
                'has_more' => $dataAccountsHasMore,
                'next_page' => $dataAccountsHasMore ? $accountPage + 1 : null,
            ]);
        }

        return view('family-sheet.index', [
            'products' => $products,
            'selectedProduct' => $selectedProduct,
            'accounts' => $accounts,
            'dataAccounts' => $dataAccounts,
            'membersByAccount' => $membersByAccount,
            'nextAccountIndex' => $nextAccountIndex,
            'siteProducts' => $siteProducts,
            'variations' => $variations,
            'dataAccountsHasMore' => $dataAccountsHasMore,
            'dataAccountsPage' => $accountPage,
            'familyTablePreferences' => $this->loadFamilyPreferences($selectedProductId ?: null, 'table_columns'),
            'familyMemberPreferences' => $this->loadFamilyPreferences($selectedProductId ?: null, 'member_fields'),
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

    public function updateTablePreferences(Request $request, $familyProduct): Response
    {
        $productId = (int) $familyProduct;
        $validated = $request->validate([
            'columnOrder' => ['present', 'array'],
            'hiddenColumns' => ['present', 'array'],
            'columnWidths' => ['present', 'array'],
        ]);

        $preference = FamilySheetPreference::query()->updateOrCreate(
            [
                'context' => 'table_columns',
                'family_product_id' => $productId ?: null,
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

    public function updateMemberPreferences(Request $request, $familyProduct): Response
    {
        $productId = (int) $familyProduct;
        $validated = $request->validate([
            'hiddenFields' => ['present', 'array'],
        ]);

        $preference = FamilySheetPreference::query()->updateOrCreate(
            [
                'context' => 'member_fields',
                'family_product_id' => $productId ?: null,
            ],
            [
                'preferences' => [
                    'hiddenFields' => array_values($validated['hiddenFields']),
                ],
            ]
        );

        return response()->json([
            'preferences' => $preference->preferences,
        ]);
    }

    public function importCsv(Request $request)
    {
        $data = $request->validate([
            'family_product_id' => ['required', 'integer', 'exists:family_products,id'],
            'csv_file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $product = DB::table('family_products')->where('id', $data['family_product_id'])->first();
        if (!$product) {
            return redirect()->back()->withErrors(['family_product_id' => 'Family product not found.']);
        }

        $file = $request->file('csv_file');
        $lines = file($file->getRealPath(), FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            return redirect()->back()->withErrors(['csv_file' => 'Unable to read CSV file.']);
        }

        $headerIndex = null;
        $headerLine = null;
        foreach ($lines as $index => $line) {
            if (trim((string) $line) === '') {
                continue;
            }
            $headerIndex = $index;
            $headerLine = $line;
            break;
        }

        if ($headerLine === null) {
            return redirect()->back()->withErrors(['csv_file' => 'CSV file has no header row.']);
        }

        $delimiter = $this->detectCsvDelimiter($headerLine);
        $headers = str_getcsv($headerLine, $delimiter);
        if (empty($headers)) {
            return redirect()->back()->withErrors(['csv_file' => 'CSV file has no header row.']);
        }

        $normalizedHeaders = array_map([$this, 'normalizeCsvHeader'], $headers);
        $headerMap = [];
        foreach ($normalizedHeaders as $index => $header) {
            $field = $this->mapCsvHeaderToField($header);
            if ($field !== null) {
                $headerMap[$index] = $field;
            }
        }
        $usePositionalMap = empty($headerMap);
        if ($usePositionalMap) {
            $headerMap = [
                0 => 'email',
                1 => 'phone',
                2 => 'purchase_date',
                3 => 'expiry',
            ];
        }

        $rows = [];
        foreach ($lines as $index => $line) {
            if ($headerIndex !== null && $index <= $headerIndex && !$usePositionalMap) {
                continue;
            }
            if (trim((string) $line) === '') {
                continue;
            }
            $row = str_getcsv($line, $delimiter);
            if ($this->rowIsEmpty($row)) {
                continue;
            }
            $rows[] = $row;
        }

        if (empty($rows)) {
            return redirect()->back()->withErrors(['csv_file' => 'CSV file has no data rows.']);
        }

        $hasFamilyProductId = Schema::hasColumn('family_accounts', 'family_product_id');
        $hasMemberProductName = Schema::hasColumn('family_members', 'family_product_name');
        $hasMemberAccountName = Schema::hasColumn('family_members', 'account_name');
        $hasMemberProductId = Schema::hasColumn('family_members', 'family_product_id');
        $hasMemberAccountId = Schema::hasColumn('family_members', 'family_account_id');
        $linkedProductName = null;
        if (!empty($product->linked_product_id)) {
            $linkedProductName = DB::table('products')
                ->where('id', $product->linked_product_id)
                ->value('name');
            $linkedProductName = $linkedProductName !== null ? trim((string) $linkedProductName) : null;
        }

        $maxIndexQuery = DB::table('family_accounts');
        if ($hasFamilyProductId) {
            $maxIndexQuery->where('family_product_id', $product->id);
        }
        $nextAccountIndex = (int) ($maxIndexQuery->max('account_index') ?? 0) + 1;
        $defaultCapacity = $product->default_capacity ?? 5;
        $groupSize = 6;

        DB::transaction(function () use (
            $rows,
            $headerMap,
            $product,
            $defaultCapacity,
            $groupSize,
            $hasFamilyProductId,
            $hasMemberProductName,
            $hasMemberAccountName,
            $hasMemberProductId,
            $hasMemberAccountId,
            $linkedProductName,
            &$nextAccountIndex
        ) {
            $groups = array_chunk($rows, $groupSize);
            foreach ($groups as $group) {
                $accountRow = $group[0] ?? null;
                if (!$accountRow) {
                    continue;
                }

                $accountData = $this->extractRowData($accountRow, $headerMap);
                $accountName = trim((string) ($accountData['email']
                    ?? $accountData['account_name']
                    ?? $accountData['family_name']
                    ?? ''));
                $accountProduct = $accountData['product'] ?? null;

                if ($accountName === '') {
                    continue;
                }

                $accountIndex = $this->parseInteger($accountData['account_index'] ?? null) ?? $nextAccountIndex;
                $memberRows = array_slice($group, 1);
                $memberCount = count(array_filter($memberRows, fn ($row) => !$this->rowIsEmpty($row)));
                $capacity = $this->parseInteger($accountData['capacity'] ?? null) ?? $defaultCapacity;
                if ($capacity !== null && $memberCount > $capacity) {
                    $capacity = $memberCount;
                }

                $accountPayload = [
                    'family_product_name' => $product->name,
                    'name' => $accountName,
                    'capacity' => $capacity,
                    'account_index' => $accountIndex,
                    'remarks' => $accountData['remarks'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if ($hasFamilyProductId) {
                    $accountPayload['family_product_id'] = $product->id;
                }

                $accountId = DB::table('family_accounts')->insertGetId($accountPayload);
                $nextAccountIndex = max($nextAccountIndex, $accountIndex + 1);

                $account = (object) [
                    'id' => $accountId,
                    'name' => $accountName,
                    'family_product_id' => $product->id,
                    'family_product_name' => $product->name,
                    'capacity' => $capacity,
                ];

                foreach ($memberRows as $memberRow) {
                    if ($this->rowIsEmpty($memberRow)) {
                        continue;
                    }

                    $memberData = $this->extractRowData($memberRow, $headerMap);
                    if ($this->rowIsEmpty($memberData)) {
                        continue;
                    }
                    if (empty($memberData['product'])) {
                        $memberData['product'] = $accountProduct ?: $linkedProductName;
                    }

                    $resolvedExpiry = $this->parseInteger($memberData['expiry'] ?? null)
                        ?? $this->inferExpiryDays($memberData['product'] ?? null);
                    $purchaseDate = $this->parseDate($memberData['purchase_date'] ?? null);

                    $payload = $this->encryptSensitiveFields([
                        'family_name' => $this->resolveFamilyName($account, $memberData),
                        'email' => $memberData['email'] ?? null,
                        'password' => $memberData['password'] ?? null,
                        'order_id' => $memberData['order_id'] ?? null,
                        'phone' => $memberData['phone'] ?? null,
                        'product' => $memberData['product'] ?? '',
                        'sales_amount' => $this->parseInteger($memberData['sales_amount'] ?? null),
                        'purchase_date' => $purchaseDate,
                        'expiry' => $resolvedExpiry,
                        'remaining_days' => $this->parseInteger($memberData['remaining_days'] ?? null)
                            ?? $this->computeRemainingDays($purchaseDate, $resolvedExpiry),
                        'remarks' => $memberData['remarks'] ?? null,
                        'two_factor' => $memberData['two_factor'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    if ($hasMemberProductName) {
                        $payload['family_product_name'] = $account->family_product_name ?? null;
                    }
                    if ($hasMemberAccountName) {
                        $payload['account_name'] = $account->name ?? null;
                    }
                    if ($hasMemberProductId) {
                        $payload['family_product_id'] = $account->family_product_id;
                    }
                    if ($hasMemberAccountId) {
                        $payload['family_account_id'] = $account->id;
                    }

                    DB::table('family_members')->insert($payload);
                }
            }
        });

        return redirect()
            ->route('family-sheet.index', ['product_id' => $product->id])
            ->with('status', 'CSV imported.');
    }

    public function exportCsv(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:family_products,id'],
        ]);

        $product = DB::table('family_products')->where('id', $data['product_id'])->first();
        if (!$product) {
            return redirect()->back()->withErrors(['product_id' => 'Family product not found.']);
        }

        $hasFamilyProductId = Schema::hasColumn('family_accounts', 'family_product_id');
        $hasFamilyProductName = Schema::hasColumn('family_accounts', 'family_product_name');
        $hasMemberAccountId = Schema::hasColumn('family_members', 'family_account_id');
        $hasMemberProductId = Schema::hasColumn('family_members', 'family_product_id');
        $hasMemberProductName = Schema::hasColumn('family_members', 'family_product_name');

        $accountQuery = DB::table('family_accounts')
            ->orderByDesc('family_accounts.account_index')
            ->orderBy('family_accounts.name');

        if ($hasFamilyProductId) {
            $accountQuery->join('family_products', 'family_accounts.family_product_id', '=', 'family_products.id')
                ->where('family_accounts.family_product_id', $product->id)
                ->select([
                    'family_accounts.*',
                    'family_products.name as product_name',
                    'family_accounts.family_product_name',
                ]);
        } elseif ($hasFamilyProductName) {
            $accountQuery->where('family_accounts.family_product_name', $product->name)
                ->select([
                    'family_accounts.*',
                    'family_accounts.family_product_name',
                ]);
        } else {
            $accountQuery->select(['family_accounts.*']);
        }

        $accounts = $accountQuery->get();
        $accountIds = $accounts->pluck('id')->filter()->values();
        $accountsById = $accounts->keyBy('id');

        $membersQuery = DB::table('family_members')
            ->orderBy('family_account_id')
            ->orderBy('id');

        if ($hasMemberAccountId && $accountIds->isNotEmpty()) {
            $membersQuery->whereIn('family_account_id', $accountIds);
        } elseif ($hasMemberProductId) {
            $membersQuery->where('family_product_id', $product->id);
        } elseif ($hasMemberProductName) {
            $membersQuery->where('family_product_name', $product->name);
        }

        $membersByAccount = $membersQuery
            ->get()
            ->map(fn ($member) => $this->decryptSensitiveFields($member))
            ->groupBy('family_account_id');

        $headers = [
            'account_name',
            'account_index',
            'capacity',
            'account_remarks',
            'account_product',
            'order_id',
            'email',
            'phone',
            'product',
            'sales_amount',
            'purchase_date',
            'expiry',
            'remaining_days',
            'remarks',
            'two_factor',
        ];

        $filename = sprintf('family-sheet-%s-%s.csv', $product->id, Carbon::now()->format('Ymd_His'));

        return response()->streamDownload(function () use ($headers, $accounts, $accountsById, $membersByAccount) {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            fputcsv($handle, $headers);

            foreach ($accounts as $account) {
                $members = $membersByAccount->get($account->id) ?? collect();
                $accountProduct = $account->product_name ?? $account->family_product_name ?? null;

                if ($members->isEmpty()) {
                    fputcsv($handle, [
                        $account->name,
                        $account->account_index,
                        $account->capacity,
                        $account->remarks,
                        $accountProduct,
                        null,
                        null,
                        null,
                        null,
                        null,
                        null,
                        null,
                        null,
                        null,
                        null,
                    ]);
                    continue;
                }

                foreach ($members as $member) {
                    $accountForMember = $accountsById->get($member->family_account_id) ?? $account;
                    $familyName = $this->resolveFamilyName($accountForMember, [], $member);
                    $remainingDays = $member->remaining_days ?? $this->computeRemainingDays($member->purchase_date ?? null, $member->expiry ?? null);

                    fputcsv($handle, [
                        $accountForMember->name ?? null,
                        $accountForMember->account_index ?? null,
                        $accountForMember->capacity ?? null,
                        $accountForMember->remarks ?? null,
                        $accountProduct,
                        $member->order_id ?? null,
                        $member->email ?? null,
                        $member->phone ?? null,
                        $member->product ?? null,
                        $member->sales_amount ?? null,
                        $member->purchase_date ?? null,
                        $member->expiry ?? null,
                        $remainingDays,
                        $member->remarks ?? null,
                        $member->two_factor ?? null,
                    ]);
                }
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
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
            'period' => ['nullable', 'integer', 'min:0'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $hasFamilyProductId = Schema::hasColumn('family_accounts', 'family_product_id');
        $hasAccountPeriod = Schema::hasColumn('family_accounts', 'period');

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
        if ($hasAccountPeriod) {
            $payload['period'] = $data['period'] ?? null;
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
            'period' => ['nullable', 'integer', 'min:0'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $accountIndex = $data['account_index'] ?? null;
        $hasAccountPeriod = Schema::hasColumn('family_accounts', 'period');

        $memberCount = DB::table('family_members')
            ->when(Schema::hasColumn('family_members', 'family_account_id'), fn ($q) => $q->where('family_account_id', $account->id))
            ->count();

        if ($memberCount > $data['capacity']) {
            return back()
                ->withInput()
                ->withErrors(['capacity' => "Capacity cannot be less than the current member count ({$memberCount})."]);
        }

        $payload = [
            'name' => trim($data['name']),
            'capacity' => (int) $data['capacity'],
            'account_index' => $accountIndex,
            'remarks' => $data['remarks'] ?? null,
            'updated_at' => now(),
        ];
        if ($hasAccountPeriod) {
            $payload['period'] = $data['period'] ?? null;
        }

        $changesForLog = $this->buildFamilyChanges($account, $payload);

        DB::table('family_accounts')
            ->where('id', $accountId)
            ->update($payload);

        $this->logFamilySheetChanges(
            $request,
            'family-sheet',
            $account->family_product_name ?? 'Family Sheet',
            null,
            $changesForLog
        );

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
        $memberProduct = trim((string) ($data['product'] ?? ''));
        if ($memberProduct === '' && Schema::hasColumn('family_accounts', 'family_product_id') && !empty($account->family_product_id)) {
            $familyProduct = DB::table('family_products')->where('id', $account->family_product_id)->first();
            if ($familyProduct && !empty($familyProduct->linked_product_id)) {
                $linkedProduct = DB::table('products')->where('id', $familyProduct->linked_product_id)->first();
                $memberProduct = $linkedProduct ? trim((string) $linkedProduct->name) : '';
            }
        }
        if ($memberProduct === '') {
            $memberProduct = trim((string) ($account->family_product_name ?? ''));
        }
        $accountPeriod = Schema::hasColumn('family_accounts', 'period') ? ($account->period ?? null) : null;
        $resolvedExpiry = $data['expiry'] ?? $accountPeriod ?? $this->inferExpiryDays($memberProduct ?: null);
        $hasMemberProductName = Schema::hasColumn('family_members', 'family_product_name');
        $hasMemberAccountName = Schema::hasColumn('family_members', 'account_name');

        $payload = $this->encryptSensitiveFields([
            'family_name' => $this->resolveFamilyName($account, $data),
            'email' => $data['email'] ?? null,
            'password' => $data['password'] ?? null,
            'order_id' => $data['order_id'] ?? null,
            'phone' => $data['phone'] ?? null,
            'product' => $memberProduct,
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
        $member = $this->decryptSensitiveFields($member);

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

        $comparePayload = [
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
        ];

        $changesForLog = $this->buildFamilyChanges($member, $comparePayload);

        $payload = $this->encryptSensitiveFields($comparePayload + [
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

        $orderId = $comparePayload['order_id'] ?? ($member->order_id ?? null);
        $this->logFamilySheetChanges(
            $request,
            'family-sheet',
            $account->family_product_name ?? 'Family Sheet',
            $orderId,
            $changesForLog
        );

        return redirect()
            ->route('family-sheet.index', ['product_id' => $account->family_product_id])
            ->with('status', 'Member updated.');
    }

    private function buildFamilyChanges(object $existing, array $payload): array
    {
        $changes = [];
        foreach ($payload as $field => $newValue) {
            if ($field === 'updated_at') {
                continue;
            }
            $oldValue = $existing->{$field} ?? null;
            $oldValue = is_scalar($oldValue) ? (string) $oldValue : ($oldValue ?? '');
            $newValue = is_scalar($newValue) ? (string) $newValue : ($newValue ?? '');
            if ((string) $oldValue !== (string) $newValue) {
                $changes[] = [
                    'field' => $field,
                    'old' => (string) $oldValue,
                    'new' => (string) $newValue,
                ];
            }
        }
        return $changes;
    }

    private function logFamilySheetChanges(
        Request $request,
        string $context,
        string $stockName,
        $indexValue,
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
        $indexLabel = $indexValue === null || $indexValue === '' ? 'N/A' : $indexValue;
        $indexTitle = $context === 'family-sheet' ? 'Order ID' : 'Index Value';

        foreach ($changes as $change) {
            $oldValue = trim((string) ($change['old'] ?? ''));
            $newValue = trim((string) ($change['new'] ?? ''));
            $message = $actorName . " changed following information.\n\n"
                . "Stock Name: {$stockName}\n"
                . "{$indexTitle}: {$indexLabel}\n"
                . "Old Data: " . ($oldValue !== '' ? $oldValue : 'N/A') . "\n"
                . "New Data: " . ($newValue !== '' ? $newValue : 'N/A');

            \App\Models\StockAccountEditLog::create([
                'actor_id' => $actorId,
                'context' => $context,
                'message' => $message,
            ]);
        }
    }

    public function destroyMember(int $memberId)
    {
        $member = DB::table('family_members')->where('id', $memberId)->first();
        if (!$member) {
            return redirect()->back()->withErrors(['member' => 'Member not found.']);
        }

        $account = DB::table('family_accounts')->where('id', $member->family_account_id)->first();
        DB::table('family_members')->where('id', $memberId)->delete();

        return redirect()
            ->route('family-sheet.index', ['product_id' => $account->family_product_id ?? null])
            ->with('status', 'Member deleted.');
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

    private function normalizeCsvHeader(string $header): string
    {
        $header = strtolower(trim($header));
        $header = preg_replace('/[^a-z0-9]+/', '_', $header);
        return trim((string) $header, '_');
    }

    private function mapCsvHeaderToField(string $header): ?string
    {
        $map = [
            'main_account' => 'account_name',
            'account' => 'account_name',
            'account_name' => 'account_name',
            'family_account' => 'account_name',
            'family_email' => 'account_name',
            'main_email' => 'account_name',
            'account_email' => 'account_name',
            'email' => 'email',
            'email_address' => 'email',
            'emailid' => 'email',
            'member_email' => 'email',
            'memberemail' => 'email',
            'email2' => 'email',
            'name' => 'family_name',
            'family_name' => 'family_name',
            'order' => 'order_id',
            'order_id' => 'order_id',
            'orderid' => 'order_id',
            'phone' => 'phone',
            'phone_number' => 'phone',
            'phonenumber' => 'phone',
            'mobile' => 'phone',
            'mobile_number' => 'phone',
            'product' => 'product',
            'product_name' => 'product',
            'amount' => 'sales_amount',
            'sales_amount' => 'sales_amount',
            'price' => 'sales_amount',
            'total' => 'sales_amount',
            'purchase' => 'purchase_date',
            'purchase_date' => 'purchase_date',
            'purchasedate' => 'purchase_date',
            'date_of_purchase' => 'purchase_date',
            'expiry' => 'expiry',
            'expiry_days' => 'expiry',
            'period_days' => 'expiry',
            'period' => 'expiry',
            'remaining' => 'remaining_days',
            'remaining_days' => 'remaining_days',
            'remarks' => 'remarks',
            'note' => 'remarks',
            'two_factor' => 'two_factor',
            'password' => 'password',
            'password2' => 'password',
            'capacity' => 'capacity',
            'max_members' => 'capacity',
            'account_index' => 'account_index',
            'index' => 'account_index',
        ];

        if (isset($map[$header])) {
            return $map[$header];
        }

        if (str_contains($header, 'email')) {
            return 'email';
        }
        if (str_contains($header, 'phone') || str_contains($header, 'mobile')) {
            return 'phone';
        }
        if (str_contains($header, 'purchase')) {
            return 'purchase_date';
        }
        if (str_contains($header, 'period') || str_contains($header, 'expiry')) {
            return 'expiry';
        }
        if (str_contains($header, 'remaining')) {
            return 'remaining_days';
        }
        if (str_contains($header, 'amount') || str_contains($header, 'sales')) {
            return 'sales_amount';
        }
        if (str_contains($header, 'order')) {
            return 'order_id';
        }
        if (str_contains($header, 'account') && str_contains($header, 'name')) {
            return 'account_name';
        }
        if (str_contains($header, 'family') && str_contains($header, 'name')) {
            return 'family_name';
        }

        return null;
    }

    private function extractRowData(array $row, array $headerMap): array
    {
        $data = [];
        foreach ($headerMap as $index => $field) {
            if (!array_key_exists($index, $row)) {
                continue;
            }
            $value = trim((string) $row[$index]);
            if ($value === '') {
                continue;
            }
            if (!array_key_exists($field, $data)) {
                $data[$field] = $value;
            }
        }

        return $data;
    }

    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function parseInteger(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }
        $value = trim((string) $value);
        if ($value === '' || !is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }

    private function parseDate(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function detectCsvDelimiter(string $line): string
    {
        $delimiters = [',', "\t", ';', '|'];
        $bestDelimiter = ',';
        $bestCount = -1;

        foreach ($delimiters as $delimiter) {
            $count = substr_count($line, $delimiter);
            if ($count > $bestCount) {
                $bestCount = $count;
                $bestDelimiter = $delimiter;
            }
        }

        return $bestDelimiter;
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
                $table->integer('period')->nullable();
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
            if (!Schema::hasColumn('family_accounts', 'period')) {
                Schema::table('family_accounts', function (Blueprint $table) {
                    $table->integer('period')->nullable()->after('capacity');
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

    private function loadFamilyPreferences(?int $productId, string $context): ?array
    {
        $specific = FamilySheetPreference::query()
            ->where('context', $context)
            ->where('family_product_id', $productId)
            ->first();

        if ($specific?->preferences) {
            return $specific->preferences;
        }

        $global = FamilySheetPreference::query()
            ->where('context', $context)
            ->whereNull('family_product_id')
            ->first();

        return $global?->preferences;
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
