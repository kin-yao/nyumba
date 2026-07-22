<x-layouts.portal>
<div style="background:#fff;border-radius:12px;border:1px solid rgba(0,0,0,0.07);padding:20px;margin-bottom:16px">
    <div style="font-size:14px;font-weight:600;margin-bottom:14px">Submit a maintenance request</div>
    <form method="POST" action="{{ route('portal.maintenance.store') }}">
        @csrf
        <div style="margin-bottom:12px">
            <label style="display:block;font-size:11px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:6px">What's the issue?</label>
            <textarea name="description" required rows="3" placeholder="e.g. Kitchen tap is leaking"
                      style="width:100%;padding:10px 12px;border:1px solid rgba(0,0,0,0.12);border-radius:8px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;resize:vertical;box-sizing:border-box">{{ old('description') }}</textarea>
        </div>
        <div style="margin-bottom:16px">
            <label style="display:block;font-size:11px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:6px">How urgent?</label>
            <select name="priority" required
                    style="width:100%;height:42px;padding:0 12px;border:1px solid rgba(0,0,0,0.12);border-radius:8px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                <option value="normal">Normal</option>
                <option value="urgent">Urgent</option>
                <option value="low">Low priority</option>
            </select>
        </div>
        <button type="submit"
                style="width:100%;padding:11px;background:#1a6b52;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:'DM Sans',sans-serif">
            Submit request
        </button>
    </form>
</div>

<div style="font-size:11px;color:#8a8880;text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px">Your past requests</div>
@if($maintenanceRequests->isEmpty())
    <div style="background:#fff;border-radius:12px;border:1px solid rgba(0,0,0,0.07);padding:20px;text-align:center;color:#8a8880;font-size:13px">
        No maintenance requests yet.
    </div>
@else
    @php
        $mStatus = ['open' => ['#fee2e2','#991b1b','Open'], 'in_progress' => ['#fef3c7','#92400e','In progress'], 'resolved' => ['#dcfce7','#166534','Resolved']];
    @endphp
    @foreach($maintenanceRequests as $req)
        @php $sc = $mStatus[$req->status] ?? $mStatus['open']; @endphp
        <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:14px 16px;margin-bottom:8px">
            <div style="display:flex;justify-content:space-between;gap:8px;margin-bottom:5px">
                <span style="font-size:10px;color:#8a8880">{{ $req->created_at->format('d M Y') }}</span>
                <span style="font-size:10px;font-weight:600;padding:2px 8px;border-radius:20px;background:{{ $sc[0] }};color:{{ $sc[1] }}">{{ $sc[2] }}</span>
            </div>
            <div style="font-size:12.5px">{{ $req->description }}</div>
        </div>
    @endforeach
@endif
</x-layouts.portal>