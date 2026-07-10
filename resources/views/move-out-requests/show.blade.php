<x-layouts.app>
<style>
.mors-wrap { padding: clamp(16px,4vw,34px); padding-bottom: 48px; max-width: 640px; }
</style>

<div class="mors-wrap">
    <div style="font-size:12px;color:#8a8880;margin-bottom:14px">
        <a href="{{ route('move-out-requests.index') }}" style="color:#8a8880;text-decoration:none">Move-out requests</a>
        &rsaquo; {{ $moveOutRequest->tenant->full_name }}
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

    <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:22px;margin-bottom:16px">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;padding-bottom:14px;border-bottom:1px solid rgba(0,0,0,0.07)">
            <div style="width:40px;height:40px;border-radius:50%;background:#e6f2ed;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:600;color:#1a6b52;flex-shrink:0">
                {{ strtoupper(substr($moveOutRequest->tenant->first_name,0,1).substr($moveOutRequest->tenant->last_name,0,1)) }}
            </div>
            <div>
                <div style="font-size:15px;font-weight:500">{{ $moveOutRequest->tenant->full_name }}</div>
                <div style="font-size:12px;color:#8a8880">{{ $moveOutRequest->unit->property->name }} &middot; Unit {{ $moveOutRequest->unit->name }}</div>
            </div>
        </div>

        <div style="display:grid;gap:9px;font-size:13px;margin-bottom:16px">
            <div style="display:flex;justify-content:space-between">
                <span style="color:#8a8880">Requested move-out date</span>
                <span style="font-weight:500">{{ $moveOutRequest->requested_move_out_date->format('d M Y') }}</span>
            </div>
            <div style="display:flex;justify-content:space-between">
                <span style="color:#8a8880">Submitted</span>
                <span>{{ $moveOutRequest->created_at->format('d M Y, g:i A') }}</span>
            </div>
            <div style="display:flex;justify-content:space-between">
                <span style="color:#8a8880">Status</span>
                <span style="font-weight:500">
                    @if(in_array($moveOutRequest->status, ['pending', 'acknowledged'])) Pending
                    @elseif($moveOutRequest->status === 'accepted') Approved
                    @else {{ ucfirst($moveOutRequest->status) }}
                    @endif
                </span>
            </div>
        </div>

        @if($moveOutRequest->reason)
            <div style="background:#f5f4f0;border-radius:8px;padding:12px 14px;margin-bottom:16px">
                <div style="font-size:10px;color:#8a8880;text-transform:uppercase;letter-spacing:.04em;margin-bottom:5px">Reason given</div>
                <div style="font-size:13px">{{ $moveOutRequest->reason }}</div>
            </div>
        @endif

        @if(in_array($moveOutRequest->status, ['pending', 'acknowledged']))
            <div style="background:#fef3c7;border:1px solid #fde68a;border-radius:8px;padding:12px 14px;margin-bottom:16px;font-size:13px;color:#92400e">
                Accepting will automatically move this tenant out on {{ $moveOutRequest->requested_move_out_date->format('d M Y') }} — no manual action needed on that day.
            </div>
            <form method="POST" action="{{ route('move-out-requests.accept', $moveOutRequest) }}" style="margin-bottom:12px">
                @csrf
                <button type="submit" onclick="return confirm('Approve this move-out date? The tenant will be moved out automatically on {{ $moveOutRequest->requested_move_out_date->format('d M Y') }}.')"
                        style="padding:8px 18px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif">
                    Approve move-out
                </button>
            </form>
        @elseif($moveOutRequest->status === 'accepted')
            <div style="background:#e6f2ed;border:1px solid #a7d7c5;border-radius:8px;padding:12px 14px;margin-bottom:16px;font-size:13px;color:#166534">
                ✓ Approved. This tenant will be automatically moved out on {{ $moveOutRequest->requested_move_out_date->format('d M Y') }}.
            </div>
        @elseif($moveOutRequest->status === 'completed')
            <div style="background:#f3f4f6;border:1px solid #e5e7eb;border-radius:8px;padding:12px 14px;margin-bottom:16px;font-size:13px;color:#4b5563">
                This tenant has moved out.
            </div>
        @elseif($moveOutRequest->status === 'cancelled')
            <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px 14px;margin-bottom:16px;font-size:13px;color:#7f1d1d">
                Cancelled by the tenant.
            </div>
        @endif

        <a href="{{ route('tenants.show', $moveOutRequest->tenant) }}"
           style="display:inline-flex;align-items:center;padding:7px 14px;background:transparent;color:#1a6b52;border:1px solid #1a6b52;border-radius:7px;font-size:13px;font-weight:500;text-decoration:none">
            View tenant profile &amp; move them out
        </a>
    </div>

    @if($moveOutRequest->hasReferral())
        <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:22px;margin-bottom:16px">
            <div style="font-size:14px;font-weight:600;margin-bottom:4px">Referral booking</div>
            <div style="font-size:12px;color:#8a8880;margin-bottom:16px">
                {{ $moveOutRequest->tenant->first_name }} would like to hand the room to someone they know.
            </div>

            <div style="display:grid;gap:9px;font-size:13px;margin-bottom:18px">
                <div style="display:flex;justify-content:space-between">
                    <span style="color:#8a8880">Name</span>
                    <span style="font-weight:500">{{ $moveOutRequest->referral_name }}</span>
                </div>
                <div style="display:flex;justify-content:space-between">
                    <span style="color:#8a8880">Phone</span>
                    <a href="tel:{{ $moveOutRequest->referral_phone }}" style="font-weight:500;color:#1a6b52;text-decoration:none">{{ $moveOutRequest->referral_phone }}</a>
                </div>
            </div>

            @if($moveOutRequest->referral_status === 'pending')
                <div style="display:flex;gap:8px;flex-wrap:wrap">
                    <form method="POST" action="{{ route('move-out-requests.accept-booking', $moveOutRequest) }}">
                        @csrf
                        <button type="submit"
                                style="padding:8px 18px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif">
                            Accept booking
                        </button>
                    </form>
                    <form method="POST" action="{{ route('move-out-requests.decline-booking', $moveOutRequest) }}"
                          onsubmit="return confirm('Decline this referral booking?')">
                        @csrf
                        <button type="submit"
                                style="padding:8px 18px;background:transparent;color:#b91c1c;border:1px solid rgba(185,28,28,0.25);border-radius:7px;font-size:13px;cursor:pointer;font-family:'DM Sans',sans-serif">
                            Decline
                        </button>
                    </form>
                </div>
            @elseif($moveOutRequest->referral_status === 'accepted')
                <div style="background:#e6f2ed;border:1px solid #a7d7c5;border-radius:8px;padding:12px 14px;font-size:13px;color:#166534">
                    ✓ Accepted. Once {{ $moveOutRequest->tenant->first_name }} moves out, Unit {{ $moveOutRequest->unit->name }} will be held as
                    <strong>reserved</strong> for {{ $moveOutRequest->referral_name }} until you complete their move-in from the Tenants page.
                </div>
            @else
                <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px 14px;font-size:13px;color:#7f1d1d">
                    Declined. The unit will become vacant as normal once {{ $moveOutRequest->tenant->first_name }} moves out.
                </div>
            @endif
        </div>
    @endif

    <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:22px">
        <div style="font-size:14px;font-weight:600;margin-bottom:12px">Internal notes</div>
        <form method="POST" action="{{ route('move-out-requests.notes', $moveOutRequest) }}">
            @csrf
            <textarea name="landlord_notes" rows="3" placeholder="Notes only you can see..."
                      style="width:100%;padding:10px 12px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;resize:vertical;box-sizing:border-box;margin-bottom:12px">{{ $moveOutRequest->landlord_notes }}</textarea>
            <button type="submit"
                    style="padding:7px 18px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif">
                Save notes
            </button>
        </form>
    </div>
</div>
</x-layouts.app>