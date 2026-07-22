<x-layouts.portal>
@if(!$lease)
    <div style="background:#fff;border-radius:12px;border:1px solid rgba(0,0,0,0.07);padding:30px;text-align:center;color:#8a8880;font-size:13px">
        We couldn't find an active tenancy on your account. Please contact your landlord.
    </div>
@else
    {{-- Greeting --}}
    <div style="margin-bottom:14px">
        @php
            $hour = now()->hour;
            $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
        @endphp
        <div style="font-size:12px;color:#8a8880">{{ $greeting }}</div>
        <div style="font-family:'DM Serif Display',serif;font-size:23px;color:#111110;margin-top:2px;line-height:1.2">{{ $tenant->full_name }}</div>
    </div>

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

    {{-- Tenancy documents --}}
    @if($documents->isNotEmpty())
        <div style="font-size:11px;color:#8a8880;text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px">Tenancy documents</div>
        <div style="background:#fff;border-radius:12px;border:1px solid rgba(0,0,0,0.07);padding:8px;margin-bottom:14px">
            @foreach($documents as $doc)
                <a href="{{ route('portal.documents.download', $doc) }}" target="_blank"
                   style="display:flex;justify-content:space-between;align-items:center;gap:8px;padding:10px 12px;text-decoration:none;color:#111110;{{ !$loop->last ? 'border-bottom:1px solid rgba(0,0,0,0.05)' : '' }}">
                    <span style="font-size:13px;font-weight:500">{{ $doc->label }}</span>
                    <span style="font-size:11px;color:#1a6b52">Download</span>
                </a>
            @endforeach
        </div>
    @endif

    @endif
</x-layouts.portal>