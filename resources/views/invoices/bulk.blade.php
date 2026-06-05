<x-layouts.app>
    <div style="padding:28px 34px 48px">

        <div style="margin-bottom:24px">
            <div style="font-size:12px;color:#8a8880;margin-bottom:4px">
                <a href="{{ route('invoices.index') }}" style="color:#8a8880;text-decoration:none">Invoices</a>
                &rsaquo; Bulk generate
            </div>
            <div style="font-family:'DM Serif Display',serif;font-size:25px">Bulk invoice generation</div>
        </div>

        @if(session('success'))
            <div style="background:#dcfce7;border:1px solid #86efac;border-radius:10px;padding:11px 15px;margin-bottom:16px;font-size:13px;color:#166534">
                {{ session('success') }}
            </div>
        @endif

        {{-- Step 1: Period selection --}}
        <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:24px;max-width:620px;margin-bottom:20px">
            <div style="font-size:10px;font-weight:500;letter-spacing:.06em;text-transform:uppercase;color:#8a8880;margin-bottom:14px">
                Step 1 - Select period and dates
            </div>
            <form method="POST" action="{{ route('invoices.bulk.preview') }}">
                @csrf
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px">
                    <div>
                        <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Month</label>
                        <select name="period_month" required
                                style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                            @foreach(range(1,12) as $m)
                                <option value="{{ $m }}"
                                    {{ isset($month) && $month == $m ? 'selected' : (now()->month == $m && !isset($month) ? 'selected' : '') }}>
                                    {{ \Carbon\Carbon::createFromDate(now()->year, $m, 1)->format('F') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Year</label>
                        <input name="period_year" type="number" required
                               value="{{ $year ?? now()->year }}"
                               style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                    </div>
                    <div>
                        <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Invoice date</label>
                        <input name="invoice_date" type="date" required
                               value="{{ $invoiceDate ?? date('Y-m-d') }}"
                               style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                    </div>
                    <div>
                        <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Due date</label>
                        <input name="due_date" type="date" required
                               value="{{ $dueDate ?? date('Y-m-d', strtotime('+10 days')) }}"
                               style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                    </div>
                </div>
                <button type="submit"
                        style="padding:7px 20px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif">
                    Preview invoices
                </button>
            </form>
        </div>

        {{-- Step 2: Preview --}}
        @if(isset($previews))
            @php
                $withWarnings    = collect($previews)->filter(fn($p) => !empty($p['warnings']) && !$p['already_invoiced'])->count();
                $alreadyInvoiced = collect($previews)->filter(fn($p) => $p['already_invoiced'])->count();
                $ready           = collect($previews)->filter(fn($p) => empty($p['warnings']) && !$p['already_invoiced'])->count();
            @endphp

            <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:24px;max-width:820px">
                <div style="font-size:10px;font-weight:500;letter-spacing:.06em;text-transform:uppercase;color:#8a8880;margin-bottom:6px">
                    Step 2 - Review and confirm
                </div>
                <div style="font-size:13px;color:#8a8880;margin-bottom:16px">
                    Invoices for <strong style="color:#111110">{{ $monthName }}</strong>
                    &middot; {{ count($previews) }} {{ Str::plural('tenant', count($previews)) }}
                    @if($ready > 0) &middot; <span style="color:#15803d">{{ $ready }} ready</span> @endif
                    @if($withWarnings > 0) &middot; <span style="color:#d97706">{{ $withWarnings }} with warnings</span> @endif
                    @if($alreadyInvoiced > 0) &middot; <span style="color:#8a8880">{{ $alreadyInvoiced }} already invoiced</span> @endif
                </div>

                <form method="POST" action="{{ route('invoices.bulk.store') }}">
                    @csrf
                    <input type="hidden" name="period_month" value="{{ $month }}">
                    <input type="hidden" name="period_year" value="{{ $year }}">
                    <input type="hidden" name="invoice_date" value="{{ $invoiceDate }}">
                    <input type="hidden" name="due_date" value="{{ $dueDate }}">

                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                        <div style="display:flex;gap:10px">
                            <button type="button" onclick="toggleAll(true)"
                                    style="font-size:12px;color:#1a6b52;background:none;border:none;cursor:pointer;font-family:'DM Sans',sans-serif;padding:0">
                                Select all
                            </button>
                            <button type="button" onclick="toggleAll(false)"
                                    style="font-size:12px;color:#8a8880;background:none;border:none;cursor:pointer;font-family:'DM Sans',sans-serif;padding:0">
                                Deselect all
                            </button>
                        </div>
                        <div style="font-size:12px;color:#8a8880" id="selected-count"></div>
                    </div>

                    <div style="display:grid;gap:10px;margin-bottom:20px">
                        @foreach($previews as $preview)
                            <div style="border:1px solid rgba(0,0,0,0.07);border-radius:10px;overflow:hidden;
                                opacity:{{ $preview['already_invoiced'] ? '0.6' : '1' }}">

                                <div style="padding:12px 16px;
                                    background:{{ $preview['already_invoiced'] ? '#f9f9f9' : (!empty($preview['warnings']) ? '#fffbeb' : '#faf9f7') }};
                                    border-bottom:1px solid rgba(0,0,0,0.07);
                                    display:flex;align-items:center;gap:12px">

                                    @if(!$preview['already_invoiced'])
                                        <input type="checkbox"
                                               name="lease_ids[]"
                                               value="{{ $preview['lease_id'] }}"
                                               class="tenant-cb"
                                               {{ empty($preview['warnings']) ? 'checked' : '' }}
                                               onchange="updateCount()"
                                               style="width:15px;height:15px;accent-color:#1a6b52;flex-shrink:0">
                                    @else
                                        <input type="checkbox" disabled
                                               style="width:15px;height:15px;flex-shrink:0">
                                    @endif

                                    <div style="width:28px;height:28px;border-radius:50%;background:#e6f2ed;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:600;color:#1a6b52;flex-shrink:0">
                                        {{ $preview['tenant_initials'] }}
                                    </div>

                                    <div style="flex:1">
                                        <div style="font-size:13px;font-weight:500">
                                            {{ $preview['tenant_name'] }}
                                            <span style="font-size:11px;color:#8a8880;font-weight:400;margin-left:4px">
                                                Unit {{ $preview['unit_name'] }} &middot; {{ $preview['property_name'] }}
                                            </span>
                                        </div>
                                        @if($preview['already_invoiced'])
                                            <div style="font-size:11px;color:#8a8880;margin-top:2px">
                                                Already invoiced &middot;
                                                <span style="font-family:monospace">{{ $preview['existing_ref'] }}</span>
                                                &middot; {{ ucfirst($preview['existing_status']) }}
                                            </div>
                                        @endif
                                        @if(!empty($preview['warnings']) && !$preview['already_invoiced'])
                                            @foreach($preview['warnings'] as $warning)
                                                <div style="font-size:11px;color:#d97706;margin-top:2px">
                                                    ⚠ {{ $warning }}
                                                </div>
                                            @endforeach
                                            <label style="display:inline-flex;align-items:center;gap:5px;margin-top:5px;font-size:11px;cursor:pointer;color:#92400e;background:#fef3c7;border-radius:5px;padding:3px 8px">
                                                <input type="checkbox"
                                                       style="accent-color:#d97706;width:12px;height:12px">
                                                Proceed without missing readings
                                            </label>
                                        @endif
                                    </div>

                                    <div style="text-align:right;flex-shrink:0">
                                        <div style="font-family:'DM Serif Display',serif;font-size:18px;color:{{ $preview['already_invoiced'] ? '#8a8880' : '#111110' }}">
                                            {{ currency($preview['total']) }}
                                        </div>
                                        <div style="font-size:11px;color:#8a8880">
                                            {{ count($preview['line_items']) }} {{ Str::plural('item', count($preview['line_items'])) }}
                                        </div>
                                    </div>
                                </div>

                                @if(!$preview['already_invoiced'])
                                    <div style="padding:10px 16px 10px 55px">
                                        @foreach($preview['line_items'] as $item)
                                            <div style="display:flex;justify-content:space-between;font-size:12px;padding:2px 0">
                                                <span style="color:#8a8880">{{ $item['description'] }}</span>
                                                <span style="font-weight:500">{{ currency($item['amount']) }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                    <input type="hidden"
                                           name="line_items_{{ $preview['lease_id'] }}"
                                           value="{{ json_encode($preview['line_items']) }}">
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div style="display:flex;gap:10px;align-items:center;padding-top:16px;border-top:1px solid rgba(0,0,0,0.07)">
                        <button type="submit"
                                style="padding:8px 20px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif">
                            Generate invoices
                        </button>
                        <a href="{{ route('invoices.bulk') }}"
                           style="padding:8px 15px;background:transparent;color:#8a8880;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;text-decoration:none">
                            Start over
                        </a>
                        <a href="{{ route('invoices.index') }}"
                           style="font-size:13px;color:#8a8880;text-decoration:none;margin-left:4px">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        @endif
    </div>

    <script>
        function toggleAll(checked) {
            document.querySelectorAll('.tenant-cb').forEach(function(cb) {
                cb.checked = checked;
            });
            updateCount();
        }

        function updateCount() {
            var checked = document.querySelectorAll('.tenant-cb:checked').length;
            var total   = document.querySelectorAll('.tenant-cb').length;
            var el = document.getElementById('selected-count');
            if (el) el.textContent = checked + ' of ' + total + ' selected';
        }

        document.addEventListener('DOMContentLoaded', updateCount);
    </script>
</x-layouts.app>