<x-layouts.app>
<style>
.tshow-wrap {
    padding: clamp(16px,4vw,34px);
    padding-bottom: 48px;
}

.tshow-layout {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 16px;
    margin-top: 16px;
}

.tbl-scroll {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    max-width: 100%;
}
.tbl-scroll table {
    width: 100%;
    border-collapse: collapse;
    min-width: 380px;
}

.modal-inner {
    background: #fff;
    border-radius: 14px;
    padding: 28px;
    width: 100%;
    max-width: 480px;
    max-height: 90vh;
    overflow-y: auto;
}

.moveout-summary {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

@media (max-width: 700px) {
    .tshow-layout { grid-template-columns: 1fr; }
}

@media (max-width: 500px) {
    .modal-inner {
        width: calc(100vw - 24px);
        padding: 20px;
        border-radius: 12px;
    }
    .moveout-summary {
        grid-template-columns: 1fr;
    }
    .tbl-scroll table {
        min-width: 300px;
    }
    .tbl-scroll th,
    .tbl-scroll td {
        padding: 8px 10px !important;
        font-size: 11px !important;
    }
}
</style>

<div class="tshow-wrap">

    <div style="font-size:12px;color:#8a8880;margin-bottom:4px">
        <a href="{{ route('tenants.index') }}" style="color:#8a8880;text-decoration:none">Tenants</a>
        &rsaquo; {{ $tenant->full_name }}
    </div>

    <div class="tshow-layout">

        {{-- Left: tenant info card --}}
        <div>
            <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:20px">

                {{-- Avatar + name --}}
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;padding-bottom:14px;border-bottom:1px solid rgba(0,0,0,0.07)">
                    <div style="width:42px;height:42px;border-radius:50%;background:#e6f2ed;display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:600;color:#1a6b52;flex-shrink:0">
                        {{ strtoupper(substr($tenant->first_name,0,1).substr($tenant->last_name,0,1)) }}
                    </div>
                    <div style="min-width:0">
                        <div style="font-size:15px;font-weight:500;word-break:break-word">{{ $tenant->full_name }}</div>
                        <div style="font-size:12px;color:#8a8880">
                            {{ $activeLease?->unit?->name }} &middot; {{ $activeLease?->unit?->property?->name }}
                        </div>
                    </div>
                </div>

                {{-- Details --}}
                <div style="display:grid;gap:9px;font-size:12px;margin-bottom:16px;padding-bottom:14px;border-bottom:1px solid rgba(0,0,0,0.07)">
                    <div style="display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap">
                        <span style="color:#8a8880;flex-shrink:0">Phone</span>
                        <span style="font-family:monospace;text-align:right;word-break:break-all">{{ $tenant->phone }}</span>
                    </div>
                    @if($tenant->alt_phone)
                        <div style="display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap">
                            <span style="color:#8a8880;flex-shrink:0">Alt phone</span>
                            <span style="font-family:monospace;text-align:right;word-break:break-all">{{ $tenant->alt_phone }}</span>
                        </div>
                    @endif
                    @if($tenant->id_number)
                        <div style="display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap">
                            <span style="color:#8a8880;flex-shrink:0">ID number</span>
                            <span style="text-align:right;word-break:break-all">{{ $tenant->id_number }}</span>
                        </div>
                    @endif
                    @if($tenant->email)
                        <div style="display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap">
                            <span style="color:#8a8880;flex-shrink:0">Email</span>
                            <span style="text-align:right;word-break:break-all">{{ $tenant->email }}</span>
                        </div>
                    @endif
                    @if($activeLease)
                        <div style="display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap">
                            <span style="color:#8a8880;flex-shrink:0">Move-in</span>
                            <span>{{ $activeLease->move_in_date->format('d M Y') }}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap">
                            <span style="color:#8a8880;flex-shrink:0">Lease end</span>
                            <span>{{ $activeLease->lease_end_date?->format('d M Y') ?? 'Open-ended' }}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap">
                            <span style="color:#8a8880;flex-shrink:0">Monthly rent</span>
                            <span style="font-weight:500">{{ currency($activeLease->monthly_rent) }}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap">
                            <span style="color:#8a8880;flex-shrink:0">Deposit held</span>
                            <span style="font-weight:500">{{ currency($activeLease->deposit_paid) }}</span>
                        </div>
                    @endif
                </div>

                {{-- Balance + credit --}}
                <div style="margin-bottom:16px;padding-bottom:14px;border-bottom:1px solid rgba(0,0,0,0.07)">
                    <div style="font-size:10px;color:#8a8880;text-transform:uppercase;letter-spacing:.04em;margin-bottom:5px">Current balance</div>
                    <div style="font-family:'DM Serif Display',serif;font-size:28px;color:{{ $balance > 0 ? '#b91c1c' : ($balance < 0 ? '#1a6b52' : '#111110') }}">
                        {{ currency(abs($balance)) }}
                    </div>
                    <div style="font-size:11px;color:#8a8880;margin-top:2px">
                        @if($balance > 0) Owes this amount
                        @elseif($balance < 0) In credit — {{ currency(abs($balance)) }} will apply to next invoice
                        @else Fully paid up
                        @endif
                    </div>

                    @if($balance < 0)
                        <div style="margin-top:10px;background:#e6f2ed;border:1px solid #a7d7c5;border-radius:8px;padding:10px 12px">
                            <div style="font-size:11px;font-weight:500;color:#1a6b52;margin-bottom:2px">Credit balance</div>
                            <div style="font-size:13px;color:#166534">
                                This tenant has overpaid by <strong>{{ currency(abs($balance)) }}</strong>.
                                This credit will be applied automatically when the next invoice is generated.
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Actions --}}
                <div style="display:grid;gap:8px">
                    @if($activeLease && $vacantUnits->isNotEmpty())
                        <button onclick="document.getElementById('transfer-modal').style.display='flex'"
                                style="width:100%;padding:8px 15px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif">
                            Transfer unit
                        </button>
                    @elseif($activeLease && $vacantUnits->isEmpty())
                        <div style="font-size:12px;color:#8a8880;text-align:center;padding:6px 0">
                            No vacant units available for transfer
                        </div>
                    @endif

                    <button onclick="document.getElementById('move-out-modal').style.display='flex'"
                            style="width:100%;padding:8px 15px;background:transparent;color:#b91c1c;border:1px solid rgba(185,28,28,0.25);border-radius:7px;font-size:13px;cursor:pointer;font-family:'DM Sans',sans-serif">
                        Move out
                    </button>
                </div>
            </div>
        </div>

        {{-- Right: ledger --}}
        <div>
            <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);overflow:hidden">
                <div style="padding:13px 16px 9px;font-size:10px;font-weight:500;letter-spacing:.05em;text-transform:uppercase;color:#8a8880">
                    Transaction ledger
                </div>
                @if($ledger->isEmpty())
                    <div style="padding:40px;text-align:center;color:#8a8880;font-size:13px">
                        No transactions yet.
                    </div>
                @else
                    <div class="tbl-scroll">
                        <table>
                            <thead>
                                <tr>
                                    <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500;white-space:nowrap">Date</th>
                                    <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Description</th>
                                    <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:right;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500;white-space:nowrap">Charged</th>
                                    <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:right;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500;white-space:nowrap">Paid</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ledger as $entry)
                                    <tr style="border-bottom:1px solid rgba(0,0,0,0.05);{{ $entry['type'] === 'deposit' ? 'background:#f9fafb;' : '' }}">
                                        <td style="padding:10px 14px;font-size:12px;color:#8a8880;white-space:nowrap">
                                            {{ \Carbon\Carbon::parse($entry['date'])->format('d M Y') }}
                                        </td>
                                        <td style="padding:10px 14px;font-size:13px;color:{{ $entry['type'] === 'deposit' ? '#8a8880' : '#111110' }};font-style:{{ $entry['type'] === 'deposit' ? 'italic' : 'normal' }}">
                                            {{ $entry['description'] }}
                                        </td>
                                        <td style="padding:10px 14px;font-size:13px;text-align:right;font-weight:500;white-space:nowrap">
                                            @if($entry['charged']) {{ currency($entry['charged']) }} @endif
                                        </td>
                                        <td style="padding:10px 14px;font-size:13px;text-align:right;font-weight:500;color:#15803d;white-space:nowrap">
                                            @if($entry['paid']) {{ currency($entry['paid']) }} @endif
                                            @if($entry['type'] === 'deposit')
                                                <span style="font-size:11px;color:#8a8880;font-style:italic">deposit</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Transfer Unit Modal --}}
@if($activeLease && $vacantUnits->isNotEmpty())
<div id="transfer-modal"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:50;align-items:center;justify-content:center;padding:16px">
    <div class="modal-inner">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid rgba(0,0,0,0.07)">
            <div style="font-size:15px;font-weight:500">Transfer unit &ndash; {{ $tenant->full_name }}</div>
            <button onclick="document.getElementById('transfer-modal').style.display='none'"
                    style="background:none;border:none;font-size:22px;cursor:pointer;color:#8a8880;line-height:1">&times;</button>
        </div>

        <div style="background:#f5f4f0;border-radius:8px;padding:12px 14px;margin-bottom:18px;font-size:12px">
            <div style="color:#8a8880;margin-bottom:4px">Currently in</div>
            <div style="font-weight:500;font-size:14px">Unit {{ $activeLease->unit->name }}</div>
            <div style="color:#8a8880;margin-top:2px;word-break:break-word">
                Rent: {{ currency($activeLease->monthly_rent) }} &middot;
                Deposit held: {{ currency($activeLease->deposit_paid) }}
                @if($balance > 0)
                    &middot; <span style="color:#b91c1c">Owes {{ currency($balance) }}</span>
                @elseif($balance < 0)
                    &middot; <span style="color:#1a6b52">Credit {{ currency(abs($balance)) }}</span>
                @endif
            </div>
        </div>

        <form method="POST" action="{{ route('tenants.transfer', $tenant) }}">
            @csrf
            <div style="display:grid;gap:13px;margin-bottom:18px">

                <div>
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">New unit</label>
                    <select name="new_unit_id" required
                            style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none"
                            onchange="updateNewUnitRent(this)">
                        <option value="">Select vacant unit...</option>
                        @foreach($vacantUnits as $unit)
                            <option value="{{ $unit->id }}"
                                    data-rent="{{ $unit->rent_amount }}"
                                    data-deposit="{{ $unit->deposit_amount }}">
                                Unit {{ $unit->name }}
                                @if($unit->type) — {{ $unit->type }} @endif
                                — {{ currency($unit->rent_amount) }}/mo
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Transfer date</label>
                    <input name="transfer_date" type="date" required value="{{ date('Y-m-d') }}"
                           style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                </div>

                <div>
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">New monthly rent ({{ currency_symbol() }})</label>
                    <input name="new_monthly_rent" id="new_monthly_rent" type="number" step="0.01" min="0" required
                           value="{{ $activeLease->monthly_rent }}"
                           style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                    <div style="font-size:11px;color:#8a8880;margin-top:3px">Pre-filled from the new unit's configured rent. Adjust if needed.</div>
                </div>

                <div>
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Deposit handling</label>
                    <select name="deposit_action" required id="deposit_action"
                            style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none"
                            onchange="toggleNewDeposit(this.value)">
                        <option value="carry_forward">Carry forward to new unit ({{ currency($activeLease->deposit_paid) }})</option>
                        <option value="keep">Keep deposit for old unit (landlord retains)</option>
                        <option value="refund">Refund deposit to tenant</option>
                    </select>
                </div>

                <div id="new_deposit_wrap" style="display:none">
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">New deposit amount ({{ currency_symbol() }})</label>
                    <input name="new_deposit" id="new_deposit" type="number" step="0.01" min="0"
                           value="{{ $activeLease->deposit_paid }}"
                           style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                    <div style="font-size:11px;color:#8a8880;margin-top:3px">Override the carried deposit amount if needed.</div>
                </div>

                <div>
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Notes (optional)</label>
                    <textarea name="notes" rows="2" placeholder="Reason for transfer, any special conditions..."
                              style="width:100%;padding:9px 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;resize:vertical"></textarea>
                </div>
            </div>

            <div style="background:#fef3c7;border:1px solid #fcd34d;border-radius:8px;padding:10px 12px;margin-bottom:16px;font-size:12px;color:#92400e">
                ⚠ Outstanding invoices on the current lease will remain on the old lease history. Any balance owed ({{ currency(max(0,$balance)) }}) should be settled separately.
            </div>

            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <button type="submit"
                        style="padding:7px 15px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif">
                    Confirm transfer
                </button>
                <button type="button" onclick="document.getElementById('transfer-modal').style.display='none'"
                        style="padding:7px 15px;background:transparent;color:#8a8880;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;cursor:pointer;font-family:'DM Sans',sans-serif">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- Move Out Modal --}}
<div id="move-out-modal"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:50;align-items:center;justify-content:center;padding:16px">
    <div class="modal-inner">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid rgba(0,0,0,0.07)">
            <div style="font-size:15px;font-weight:500">Move out &ndash; {{ $tenant->full_name }}</div>
            <button onclick="document.getElementById('move-out-modal').style.display='none'"
                    style="background:none;border:none;font-size:22px;cursor:pointer;color:#8a8880;line-height:1">&times;</button>
        </div>

        @if($activeLease)
            <div class="moveout-summary" style="background:#f5f4f0;border-radius:8px;padding:14px;margin-bottom:18px;font-size:12px">
                <div>
                    <div style="color:#8a8880;margin-bottom:3px">Balance owed</div>
                    <div style="font-family:'DM Serif Display',serif;font-size:20px;color:{{ $balance > 0 ? '#b91c1c' : '#15803d' }}">
                        {{ currency(abs($balance)) }}
                    </div>
                </div>
                <div>
                    <div style="color:#8a8880;margin-bottom:3px">Deposit held</div>
                    <div style="font-family:'DM Serif Display',serif;font-size:20px">{{ currency($activeLease->deposit_paid) }}</div>
                </div>
            </div>

            <form method="POST" action="{{ route('tenants.move-out', $tenant) }}">
                @csrf
                <div style="display:grid;gap:13px;margin-bottom:18px">
                    <div>
                        <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Move-out date</label>
                        <input name="move_out_date" type="date" required value="{{ date('Y-m-d') }}"
                               style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                    </div>
                    <div>
                        <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Final charges ({{ currency_symbol() }})</label>
                        <input name="final_charges" type="number" value="0" min="0" placeholder="Damages, cleaning, etc."
                               style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                    </div>
                    <div>
                        <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Deposit handling</label>
                        <select name="deposit_action" required style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                            <option value="apply_and_refund">Apply deposit to balance, refund remainder</option>
                            <option value="full_refund">Full refund to tenant</option>
                            <option value="forfeit">Forfeit full deposit</option>
                            <option value="partial_refund">Partial refund</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Notes</label>
                        <textarea name="notes" rows="2"
                                  style="width:100%;padding:9px 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;resize:vertical"></textarea>
                    </div>
                </div>
                <div style="display:flex;gap:8px;flex-wrap:wrap">
                    <button type="submit" style="padding:7px 15px;background:#b91c1c;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif">
                        Confirm move-out
                    </button>
                    <button type="button" onclick="document.getElementById('move-out-modal').style.display='none'"
                            style="padding:7px 15px;background:transparent;color:#8a8880;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;cursor:pointer;font-family:'DM Sans',sans-serif">
                        Cancel
                    </button>
                </div>
            </form>
        @else
            <div style="text-align:center;color:#8a8880;font-size:13px;padding:20px">This tenant has no active lease.</div>
        @endif
    </div>
</div>

<script>
function updateNewUnitRent(select) {
    const opt = select.options[select.selectedIndex];
    const rent = opt.dataset.rent;
    if (rent) {
        document.getElementById('new_monthly_rent').value = rent;
    }
}

function toggleNewDeposit(value) {
    document.getElementById('new_deposit_wrap').style.display =
        value === 'carry_forward' ? 'block' : 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    toggleNewDeposit('carry_forward');
});
</script>
</x-layouts.app>