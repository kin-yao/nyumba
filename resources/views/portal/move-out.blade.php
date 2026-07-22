<x-layouts.portal>
@if($hasPendingMoveOut)
    <div style="background:#fef3c7;border:1px solid #fde68a;border-radius:10px;padding:14px 16px;margin-bottom:16px;font-size:13px;color:#92400e">
        You already have a move-out request being processed. Your landlord will be in touch.
    </div>
@else
    <div style="background:#fff;border-radius:12px;border:1px solid rgba(0,0,0,0.07);padding:20px;margin-bottom:16px">
        <div style="font-size:14px;font-weight:600;margin-bottom:4px">Request to move out</div>
        <div style="font-size:12px;color:#8a8880;margin-bottom:14px">Your landlord will be notified right away.</div>
        <form method="POST" action="{{ route('portal.move-out.store') }}">
            @csrf
            <div style="margin-bottom:12px">
                <label style="display:block;font-size:11px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:6px">Planned move-out date</label>
                <input type="date" name="requested_move_out_date" required min="{{ now()->format('Y-m-d') }}"
                       style="width:100%;height:42px;padding:0 12px;border:1px solid rgba(0,0,0,0.12);border-radius:8px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;box-sizing:border-box">
            </div>
            <div style="margin-bottom:16px">
                <label style="display:block;font-size:11px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:6px">Reason (optional)</label>
                <textarea name="reason" rows="2"
                          style="width:100%;padding:10px 12px;border:1px solid rgba(0,0,0,0.12);border-radius:8px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;resize:vertical;box-sizing:border-box"></textarea>
            </div>

            <div style="border-top:1px solid rgba(0,0,0,0.07);padding-top:14px;margin-bottom:16px">
                <div style="font-size:12.5px;font-weight:600;margin-bottom:3px">Know someone who wants your room?</div>
                <div style="font-size:11.5px;color:#8a8880;margin-bottom:12px">Optional — your landlord will decide whether to accept.</div>
                <div style="margin-bottom:10px">
                    <label style="display:block;font-size:11px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:6px">Their name</label>
                    <input type="text" name="referral_name"
                           style="width:100%;height:42px;padding:0 12px;border:1px solid rgba(0,0,0,0.12);border-radius:8px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;box-sizing:border-box">
                </div>
                <div>
                    <label style="display:block;font-size:11px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:6px">Their phone number</label>
                    <input type="text" name="referral_phone" placeholder="07XXXXXXXX"
                           style="width:100%;height:42px;padding:0 12px;border:1px solid rgba(0,0,0,0.12);border-radius:8px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;box-sizing:border-box">
                </div>
            </div>

            <button type="submit" onclick="return confirm('Submit this move-out request to your landlord?')"
                    style="width:100%;padding:11px;background:#b91c1c;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:'DM Sans',sans-serif">
                Submit move-out request
            </button>
        </form>
    </div>
@endif

@if($moveOutRequests->isNotEmpty())
    <div style="font-size:11px;color:#8a8880;text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px">Your move-out requests</div>
    @php
        $moStatus = ['pending' => ['#fef3c7','#92400e','Pending'], 'acknowledged' => ['#fef3c7','#92400e','Pending'], 'accepted' => ['#e6f2ed','#166534','Approved'], 'completed' => ['#dcfce7','#166534','Completed'], 'cancelled' => ['#f3f4f6','#4b5563','Cancelled']];
    @endphp
    @foreach($moveOutRequests as $req)
        @php $sc = $moStatus[$req->status] ?? $moStatus['pending']; @endphp
        <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:14px 16px;margin-bottom:8px">
            <div style="display:flex;justify-content:space-between;gap:8px;margin-bottom:5px">
                <span style="font-size:12.5px;font-weight:500">Move out {{ $req->requested_move_out_date->format('d M Y') }}</span>
                <span style="font-size:10px;font-weight:600;padding:2px 8px;border-radius:20px;background:{{ $sc[0] }};color:{{ $sc[1] }}">{{ $sc[2] }}</span>
            </div>
            @if($req->status === 'accepted')
                <div style="font-size:11.5px;color:#166534;margin-bottom:8px">Your landlord has approved this date.</div>
            @endif
            @if($req->hasReferral())
                <div style="font-size:11.5px;color:#8a8880;margin-bottom:8px">
                    Booking for {{ $req->referral_name }}:
                    <strong style="color:{{ $req->referral_status === 'accepted' ? '#15803d' : ($req->referral_status === 'declined' ? '#b91c1c' : '#92400e') }}">
                        {{ ucfirst($req->referral_status) }}
                    </strong>
                </div>
            @endif
            @if(in_array($req->status, ['pending', 'acknowledged', 'accepted']))
                <form method="POST" action="{{ route('portal.move-out.cancel', $req) }}"
                      onsubmit="return confirm('Cancel this move-out request?')">
                    @csrf
                    <button type="submit"
                            style="padding:5px 12px;background:transparent;color:#b91c1c;border:1px solid rgba(185,28,28,0.25);border-radius:6px;font-size:11.5px;cursor:pointer;font-family:'DM Sans',sans-serif">
                        Cancel request
                    </button>
                </form>
            @endif
        </div>
    @endforeach
@endif
</x-layouts.portal>