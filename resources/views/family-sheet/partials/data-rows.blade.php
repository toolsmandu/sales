@forelse ($dataAccounts as $account)
    @php
        $members = $membersByAccount->get($account->id) ?? collect();
    @endphp
    <tr style="background: #f8fafc; font-weight: 700;" class="family-account-row">
        <td colspan="{{ count($familyColumns) }}" style="text-align: left; padding: 0.75rem 1rem;">
            <strong>{{ $account->name }}</strong>
            @if(!empty($account->account_index))
                <strong>(Index: {{ $account->account_index }})</strong>
            @endif
            <strong>({{ $account->member_count }}/{{ $account->capacity ?? '∞' }} used)</strong>
            @if(!empty($account->remarks))
                — <strong>{{ $account->remarks }}</strong>
            @endif
        </td>
    </tr>
    @forelse ($members as $member)
        @php
            $purchaseAt = $member->purchase_date ? \Illuminate\Support\Carbon::parse($member->purchase_date) : null;
        @endphp
        <tr class="family-member-row">
            <td data-col-id="account" style="text-align: left;">
                @if ($loop->first)
                    <strong>{{ $account->name }}</strong>
                    @if(!empty($account->account_index))
                        <strong>(Index: {{ $account->account_index }})</strong>
                    @endif
                    <strong>({{ $account->member_count }}/{{ $account->capacity ?? '∞' }} used)</strong>
                    @if(!empty($account->remarks))
                        — <strong>{{ $account->remarks }}</strong>
                    @endif
                @endif
            </td>
            <td data-col-id="family_name">
                <input type="text" name="family_name" form="family-member-{{ $member->id }}" value="{{ $member->family_name ?? $account->name }}" style="width: 100%;">
            </td>
            <td data-col-id="order">
                <input type="text" name="order_id" form="family-member-{{ $member->id }}" value="{{ $member->order_id }}" style="width: 100%;">
            </td>
            <td data-col-id="email">
                <form method="POST" action="{{ route('family-sheet.members.update', $member->id) }}" id="family-member-{{ $member->id }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="family_account_id" value="{{ $member->family_account_id }}">
                </form>
                <input type="email" name="email" form="family-member-{{ $member->id }}" value="{{ $member->email }}" style="width: 100%;">
            </td>
            <td data-col-id="phone">
                <input type="text" name="phone" form="family-member-{{ $member->id }}" value="{{ $member->phone }}" style="width: 100%;">
            </td>
            <td data-col-id="purchase">
                <input type="date" name="purchase_date" form="family-member-{{ $member->id }}" value="{{ $purchaseAt ? $purchaseAt->format('Y-m-d') : '' }}" style="width: 100%;">
            </td>
            <td data-col-id="period">
                <input type="number" name="expiry" form="family-member-{{ $member->id }}" value="{{ $member->expiry }}" style="width: 100%;">
            </td>
            <td data-col-id="remaining">
                <input type="number" name="remaining_days" form="family-member-{{ $member->id }}" value="{{ $member->remaining_days }}" style="width: 100%;">
            </td>
            <td data-col-id="remarks">
                <div style="display: flex; gap: 0.35rem; align-items: center;">
                    <input type="text" name="remarks" form="family-member-{{ $member->id }}" value="{{ $member->remarks }}" style="width: 100%;">
                    <button type="submit" form="family-member-{{ $member->id }}" class="ghost-button ghost-button--compact" aria-label="Save">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M5 4h11l3 3v13a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V4z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                            <path d="M7 4v6h10V4" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                            <path d="M8 20v-6h8v6" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <form id="family-member-delete-{{ $member->id }}" method="POST" action="{{ route('family-sheet.members.destroy', $member->id) }}">
                        @csrf
                        @method('DELETE')
                    </form>
                    <button type="submit" form="family-member-delete-{{ $member->id }}" class="ghost-button ghost-button--compact" style="color: #b91c1c;" aria-label="Delete" onclick="return confirm('Delete this member?');">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M6 7h12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                            <path d="M10 11v6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                            <path d="M14 11v6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                            <path d="M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                            <path d="M19 7l-.6 10.2A2 2 0 0116.41 19H7.59a2 2 0 01-1.99-1.8L5 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                        </svg>
                    </button>
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td data-col-id="account"></td>
            <td data-col-id="email" colspan="{{ count($familyColumns) - 1 }}"><p class="helper-text">No members in this main account.</p></td>
        </tr>
    @endforelse
@empty
    <tr>
        <td colspan="{{ count($familyColumns) }}"><p class="helper-text">No main accounts yet.</p></td>
    </tr>
@endforelse
