<x-layouts.app>
<style>
.mor-wrap { padding: clamp(16px,4vw,34px); padding-bottom: 48px; }
.mor-card {
    background: #fff;
    border-radius: 10px;
    border: 1px solid rgba(0,0,0,0.07);
    padding: 16px 18px;
    margin-bottom: 10px;
    text-decoration: none;
    color: inherit;
    display: block;
}
.mor-card.unread { border-left: 3px solid #b91c1c; }
</style>

<div class="mor-wrap">
    <div style="margin-bottom:22px">
        <div style="font-family:'DM Serif Display',serif;font-size:clamp(20px,5vw,25px)">Move-out requests</div>
        <div style="font-size:13px;color:#8a8880;margin-top:3px">
            {{ $pendingCount }} pending {{ Str::plural('request', $pendingCount) }}
        </div>
    </div>

    @if(session('success'))
        <div style="background:#dcfce7;border:1px solid #86efac;border-radius:10px;padding:11px 15px;margin-bottom:16px;font-size:13px;color:#166534">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:10px;padding:11px 15px;margin-bottom:16px;font-size:13px;color:#991b1b">
            {{ session('error') }}
        </div>
    @endif

    @if($requests->isEmpty())
        <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:48px;text-align:center;color:#8a8880;font-size:13px">
            <div style="font-size:32px;margin-bottom:10px">📭</div>
            No move-out requests yet.
        </div>
    @else
        @php
            $statusColors = [
                'pending'      => ['bg' => '#fef3c7', 'text' => '#92400e', 'label' => 'Pending'],
                'acknowledged' => ['bg' => '#dbeafe', 'text' => '#1e40af', 'label' => 'Acknowledged'],
                'completed'    => ['bg' => '#dcfce7', 'text' => '#166534', 'label' => 'Completed'],
                'cancelled'    => ['bg' => '#f3f4f6', 'text' => '#4b5563', 'label' => 'Cancelled'],
            ];
        @endphp
        @foreach($requests as $req)
            @php $sc = $statusColors[$req->status] ?? $statusColors['pending']; @endphp
            <a href="{{ route('move-out-requests.show', $req) }}" class="mor-card {{ !$req->read_at ? 'unread' : '' }}">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:10px;margin-bottom:6px">
                    <div>
                        <div style="font-size:14px;font-weight:500">{{ $req->tenant->full_name }}</div>
                        <div style="font-size:12px;color:#8a8880;margin-top:2px">
                            {{ $req->unit->property->name }} &middot; Unit {{ $req->unit->name }}
                        </div>
                    </div>
                    <span style="display:inline-flex;flex-shrink:0;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:500;background:{{ $sc['bg'] }};color:{{ $sc['text'] }}">
                        {{ $sc['label'] }}
                    </span>
                </div>
                <div style="font-size:12.5px;color:#8a8880">
                    Move-out date: <strong style="color:#111110">{{ $req->requested_move_out_date->format('d M Y') }}</strong>
                    @if($req->hasReferral())
                        &middot; Booking for <strong style="color:#111110">{{ $req->referral_name }}</strong>
                        (<span style="color:{{ $req->referral_status === 'accepted' ? '#15803d' : ($req->referral_status === 'declined' ? '#b91c1c' : '#92400e') }}">{{ ucfirst($req->referral_status) }}</span>)
                    @endif
                </div>
            </a>
        @endforeach
    @endif
</div>
</x-layouts.app>