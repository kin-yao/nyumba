<x-layouts.app>
<style>
.notif-wrap { padding: clamp(16px,4vw,34px); padding-bottom: 48px; }

.notif-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.notif-list {
    display: grid;
    gap: 8px;
    max-width: 700px;
}

.notif-item {
    background: #fff;
    border-radius: 10px;
    border: 1px solid rgba(0,0,0,0.07);
    padding: 16px 18px;
    display: flex;
    gap: 14px;
    align-items: flex-start;
}

.notif-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}

.notif-body { flex: 1; min-width: 0; }

.notif-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 4px;
    flex-wrap: wrap;
}
</style>

<div class="notif-wrap">

    <div class="notif-header">
        <div>
            <div style="font-family:'DM Serif Display',serif;font-size:clamp(20px,5vw,25px);line-height:1.1">Notifications</div>
            <div style="font-size:13px;color:#8a8880;margin-top:3px">System alerts and updates</div>
        </div>
        @if($notifications->whereNull('read_at')->count() > 0)
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button type="submit"
                        style="padding:7px 15px;background:transparent;color:#8a8880;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;cursor:pointer;font-family:'DM Sans',sans-serif;white-space:nowrap">
                    Mark all as read
                </button>
            </form>
        @endif
    </div>

    @if(session('success'))
        <div style="background:#dcfce7;border:1px solid #86efac;border-radius:10px;padding:11px 15px;margin-bottom:16px;font-size:13px;color:#166534">
            {{ session('success') }}
        </div>
    @endif

    @if($notifications->isEmpty())
        <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:60px;text-align:center;color:#8a8880;font-size:13px">
            <div style="font-size:36px;margin-bottom:12px">🔔</div>
            <div style="font-weight:500;margin-bottom:4px">No notifications yet</div>
            <div>System alerts will appear here</div>
        </div>
    @else
        <div class="notif-list">
            @foreach($notifications as $notification)
                @php
                    $typeConfig = [
                        'invoice_generated' => ['#e6f2ed', '📄'],
                        'invoice_skipped'   => ['#fef3c7', '⚠'],
                        'payment_received'  => ['#dcfce7', '💰'],
                        'welcome'           => ['#e6f2ed', '🎉'],
                        'subscription_updated' => ['#dbeafe', '✅'],
                        'sms_credits_topped_up'=> ['#dcfce7', '💬'],
                    ];
                    $tc = $typeConfig[$notification->type] ?? ['#f3f4f6', '🔔'];
                @endphp
                <div class="notif-item" style="opacity:{{ $notification->read_at ? '0.7' : '1' }}">
                    <div class="notif-icon" style="background:{{ $tc[0] }}">{{ $tc[1] }}</div>
                    <div class="notif-body">
                        <div class="notif-top">
                            <div style="font-size:13px;font-weight:500">
                                {{ $notification->title }}
                                @if(!$notification->read_at)
                                    <span style="display:inline-block;width:7px;height:7px;background:#1a6b52;border-radius:50%;margin-left:6px;vertical-align:middle"></span>
                                @endif
                            </div>
                            <div style="font-size:11px;color:#8a8880;white-space:nowrap;flex-shrink:0">
                                {{ $notification->created_at->diffForHumans() }}
                            </div>
                        </div>
                        <div style="font-size:12px;color:#8a8880;line-height:1.6">
                            {{ $notification->body }}
                        </div>
                        @if(!$notification->read_at)
                            <form method="POST" action="{{ route('notifications.read', $notification) }}" style="margin-top:8px">
                                @csrf
                                <button type="submit"
                                        style="font-size:11px;color:#1a6b52;background:none;border:none;cursor:pointer;font-family:'DM Sans',sans-serif;padding:0">
                                    Mark as read
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
</x-layouts.app>