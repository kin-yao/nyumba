<x-layouts.portal>
@if(!$lease)
    <div style="background:#fff;border-radius:12px;border:1px solid rgba(0,0,0,0.07);padding:30px;text-align:center;color:#8a8880;font-size:13px">
        We couldn't find an active tenancy on your account. Please contact your landlord.
    </div>
@else
    {{-- Property / unit card --}}
    <div style="background:#111110;border-radius:12px;padding:20px;margin-bottom:14px;color:#fff">
        <div style="font-size:11px;color:rgba(255,255,255,0.4);text-transform:uppercase;letter-spacing:.05em;margin-bottom:5px">Your home</div>
        <div style="font-family:'DM Serif Display',serif;font-size:20px;margin-bottom:3px">{{ $property->name }}</div>
        <div style="font-size:13px;color:rgba(255,255,255,0.6)">
            Unit {{ $unit->name }} &middot; {{ $unit->type }}
        </div>
    </div>

    {{-- Balance --}}
    <div style="background:#fff;border-radius:12px;border:1px solid rgba(0,0,0,0.07);padding:20px;margin-bottom:14px;text-align:center">
        <div style="font-size:11px;color:#8a8880;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px">Current balance</div>
        <div style="font-family:'DM Serif Display',serif;font-size:32px;color:{{ $balance > 0 ? '#b91c1c' : ($balance < 0 ? '#1a6b52' : '#111110') }}">
            {{ currency(abs($balance)) }}
        </div>
        <div style="font-size:12px;color:#8a8880;margin-top:3px">
            @if($balance > 0) Amount due
            @elseif($balance < 0) You're in credit
            @else Fully paid up
            @endif
        </div>
    </div>

    {{-- Contact --}}
    <div style="background:#fff;border-radius:12px;border:1px solid rgba(0,0,0,0.07);padding:18px 20px;margin-bottom:14px">
        <div style="font-size:11px;color:#8a8880;text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px">Property contact</div>
        @if($property->caretaker_name)
            <div style="font-size:14px;font-weight:500;margin-bottom:2px">{{ $property->caretaker_name }}</div>
            @if($property->caretaker_phone)
                <a href="tel:{{ $property->caretaker_phone }}" style="font-size:13px;color:#1a6b52;text-decoration:none">{{ $property->caretaker_phone }}</a>
            @endif
        @elseif($property->account && $property->account->phone)
            <div style="font-size:14px;font-weight:500;margin-bottom:2px">{{ $property->account->name }}</div>
            <a href="tel:{{ $property->account->phone }}" style="font-size:13px;color:#1a6b52;text-decoration:none">{{ $property->account->phone }}</a>
        @else
            <div style="font-size:13px;color:#8a8880">No contact details on file.</div>
        @endif
    </div>

    {{-- Tenancy period --}}
    <div style="background:#fff;border-radius:12px;border:1px solid rgba(0,0,0,0.07);padding:18px 20px;margin-bottom:14px">
        <div style="font-size:11px;color:#8a8880;text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px">Tenancy period</div>
        <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:7px">
            <span style="color:#8a8880">Move-in date</span>
            <span style="font-weight:500">{{ $lease->move_in_date->format('d M Y') }}</span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:7px">
            <span style="color:#8a8880">Lease end</span>
            <span style="font-weight:500">{{ $lease->lease_end_date?->format('d M Y') ?? 'Open-ended' }}</span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:13px">
            <span style="color:#8a8880">Monthly rent</span>
            <span style="font-weight:500">{{ currency($lease->monthly_rent) }}</span>
        </div>
    </div>

    {{-- Ledger, grouped by month --}}
    <div style="font-size:11px;color:#8a8880;text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px">Transaction ledger</div>
    @if($ledgerByMonth->isEmpty())
        <div style="background:#fff;border-radius:12px;border:1px solid rgba(0,0,0,0.07);padding:24px;text-align:center;color:#8a8880;font-size:13px">
            No transactions yet.
        </div>
    @else
        @foreach($ledgerByMonth as $monthLabel => $rows)
            <div style="background:#fff;border-radius:12px;border:1px solid rgba(0,0,0,0.07);margin-bottom:12px;overflow:hidden">
                <div style="padding:10px 16px;background:#faf9f7;border-bottom:1px solid rgba(0,0,0,0.06);font-size:12px;font-weight:600">
                    {{ $monthLabel }}
                </div>
                @foreach($rows as $row)
                    <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;padding:10px 16px;border-bottom:1px solid rgba(0,0,0,0.04)">
                        <div style="min-width:0">
                            <div style="font-size:12.5px">{{ $row['description'] }}</div>
                            <div style="font-size:10.5px;color:#8a8880;margin-top:1px">{{ $row['date']->format('d M') }}{{ $row['reference'] ? ' · ' . $row['reference'] : '' }}</div>
                        </div>
                        @if($row['charged'] !== null)
                            <div style="font-size:13px;font-weight:600;color:#b91c1c;flex-shrink:0">-{{ currency($row['charged']) }}</div>
                        @else
                            <div style="font-size:13px;font-weight:600;color:#15803d;flex-shrink:0">+{{ currency($row['paid']) }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endforeach
    @endif
@endif
</x-layouts.portal>