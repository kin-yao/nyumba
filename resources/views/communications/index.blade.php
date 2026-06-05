<x-layouts.app>
<style>
.comm-wrap { padding: clamp(16px,4vw,34px); padding-bottom: 48px; }

.comm-layout {
    display: grid;
    grid-template-columns: 1fr 280px;
    gap: 14px;
    max-width: 960px;
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

.tbl-scroll {
    background: #fff;
    border-radius: 10px;
    border: 1px solid rgba(0,0,0,0.07);
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}
.tbl-scroll table {
    width: 100%;
    border-collapse: collapse;
    min-width: 480px;
}

@media (max-width: 800px) {
    .comm-layout {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 500px) {
    .modal-inner { width: calc(100vw - 24px); padding: 20px; border-radius: 12px; }
}
</style>

<div class="comm-wrap">

    <div style="margin-bottom:24px">
        <div style="font-family:'DM Serif Display',serif;font-size:clamp(20px,5vw,25px);line-height:1.1">Communications</div>
        <div style="font-size:13px;color:#8a8880;margin-top:3px">Send SMS to tenants</div>
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

    {{-- Tabs --}}
    <div style="display:flex;border-bottom:1px solid rgba(0,0,0,0.07);margin-bottom:20px;overflow-x:auto;-webkit-overflow-scrolling:touch">
        @foreach([['compose','Compose'],['templates','Templates'],['logs','Message logs']] as [$id,$label])
            <div class="tab {{ $id==='compose'?'on':'' }}" onclick="showTab('{{ $id }}',this)"
                 style="padding:9px 16px;font-size:13px;{{ $id==='compose'?'color:#1a6b52;border-bottom:2px solid #1a6b52;font-weight:500;':'color:#8a8880;border-bottom:2px solid transparent;' }}cursor:pointer;margin-bottom:-1px;white-space:nowrap">
                {{ $label }}
            </div>
        @endforeach
    </div>

    {{-- ── Compose tab ── --}}
    <div id="tab-compose">
        <div class="comm-layout">
            <form method="POST" action="{{ route('communications.send') }}">
                @csrf

                {{-- Recipients --}}
                <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:20px;margin-bottom:14px">
                    <div style="font-size:10px;font-weight:500;letter-spacing:.06em;text-transform:uppercase;color:#8a8880;margin-bottom:14px">Recipients</div>
                    <div style="margin-bottom:13px">
                        <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Send to</label>
                        <select name="recipient_type" required id="recipient-type" onchange="toggleRecipientFields(this.value)"
                                style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                            <option value="" disabled selected>Select recipients</option>
                            <option value="all">All tenants ({{ $tenants->count() }})</option>
                            <option value="overdue">Tenants with overdue invoices</option>
                            <option value="property">All tenants in a property</option>
                            <option value="individual">Individual tenant</option>
                        </select>
                    </div>
                    <div id="field-property" style="display:none;margin-bottom:13px">
                        <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Property</label>
                        <select name="property_id" style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                            <option value="" disabled selected>Select property</option>
                            @foreach($properties as $property)
                                <option value="{{ $property->id }}">{{ $property->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="field-tenant" style="display:none">
                        <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Tenant</label>
                        <select name="tenant_id" style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                            <option value="" disabled selected>Select tenant</option>
                            @foreach($tenants as $tenant)
                                <option value="{{ $tenant->id }}">{{ $tenant->full_name }} &ndash; {{ $tenant->activeLease?->unit?->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Message --}}
                <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:20px">
                    <div style="font-size:10px;font-weight:500;letter-spacing:.06em;text-transform:uppercase;color:#8a8880;margin-bottom:14px">Message</div>
                    <div style="margin-bottom:10px">
                        <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Use template</label>
                        <select onchange="loadTemplate(this)" style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                            <option value="">Select a template (optional)</option>
                            @foreach($templates as $template)
                                <option value="{{ $template->body }}">{{ $template->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <textarea name="message" id="message-body" required rows="4" oninput="updateCharCount(this)"
                              placeholder="Type your message here..."
                              style="width:100%;padding:9px 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;resize:vertical;min-height:90px;margin-bottom:8px"></textarea>
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:8px">
                        <div style="display:flex;gap:5px;flex-wrap:wrap">
                            @foreach(['{first_name}','{balance}','{unit_number}','{property_name}','{phone}'] as $ph)
                                <span onclick="insertPlaceholder('{{ $ph }}')"
                                      style="font-size:11px;background:#f5f4f0;border:1px solid rgba(0,0,0,0.08);border-radius:4px;padding:2px 7px;cursor:pointer;color:#1a6b52">
                                    {{ $ph }}
                                </span>
                            @endforeach
                        </div>
                        <span id="char-count" style="font-size:11px;color:#8a8880;white-space:nowrap">0 / 160</span>
                    </div>
                    <button type="submit" style="padding:7px 16px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif">
                        Send SMS
                    </button>
                </div>
            </form>

            {{-- Right sidebar --}}
            <div>
                {{-- SMS Credits --}}
                @php $credits = auth()->user()->account->sms_credits; @endphp
                <div style="background:#fff;border-radius:10px;border:1px solid {{ $credits<=20?'#fca5a5':'rgba(0,0,0,0.07)' }};padding:20px;margin-bottom:12px">
                    <div style="font-size:10px;font-weight:500;letter-spacing:.06em;text-transform:uppercase;color:#8a8880;margin-bottom:10px">SMS Credits</div>
                    <div style="font-family:'DM Serif Display',serif;font-size:36px;color:{{ $credits<=20?'#b91c1c':($credits<=50?'#d97706':'#15803d') }}">
                        {{ number_format($credits) }}
                    </div>
                    <div style="font-size:12px;color:#8a8880;margin-top:3px">credits remaining</div>
                    @if($credits == 0)
                        <div style="margin-top:10px;background:#fee2e2;border-radius:7px;padding:9px 11px;font-size:12px;color:#991b1b;font-weight:500">
                            No credits. SMS sending is blocked.
                        </div>
                    @elseif($credits <= 20)
                        <div style="margin-top:10px;background:#fef3c7;border-radius:7px;padding:9px 11px;font-size:12px;color:#92400e">
                            Credits running low. Contact support to top up.
                        </div>
                    @endif
                    <div style="margin-top:12px;padding-top:12px;border-top:1px solid rgba(0,0,0,0.07);font-size:12px;color:#8a8880">
                        Each SMS costs 1 credit.
                    </div>
                </div>

                {{-- Stats --}}
                <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:20px;margin-bottom:12px">
                    <div style="font-size:10px;font-weight:500;letter-spacing:.06em;text-transform:uppercase;color:#8a8880;margin-bottom:12px">Stats</div>
                    <div style="display:grid;gap:10px;font-size:13px">
                        <div style="display:flex;justify-content:space-between">
                            <span style="color:#8a8880">Total sent</span>
                            <span style="font-weight:500;color:#15803d">{{ $totalSent }}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between">
                            <span style="color:#8a8880">Failed</span>
                            <span style="font-weight:500;color:{{ $totalFailed>0?'#b91c1c':'#111110' }}">{{ $totalFailed }}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between">
                            <span style="color:#8a8880">Active tenants</span>
                            <span style="font-weight:500">{{ $tenants->count() }}</span>
                        </div>
                    </div>
                </div>

                {{-- Quick messages --}}
                <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:20px">
                    <div style="font-size:10px;font-weight:500;letter-spacing:.06em;text-transform:uppercase;color:#8a8880;margin-bottom:10px">Quick messages</div>
                    <div style="display:flex;flex-direction:column;gap:6px">
                        @foreach([
                            ['Invoice notification', 'Dear {first_name}, your rent invoice is ready. Please pay KES {balance} to our M-Pesa Paybill. Thank you.'],
                            ['Overdue reminder',     'Dear {first_name}, your balance of KES {balance} is overdue. Please pay immediately to avoid disruption. Thank you.'],
                            ['Payment thank you',    'Dear {first_name}, we have received your payment. Thank you for paying on time. {property_name} management.'],
                        ] as [$label, $msg])
                            <button type="button"
                                    onclick="document.getElementById('message-body').value='{{ addslashes($msg) }}';updateCharCount(document.getElementById('message-body'))"
                                    style="text-align:left;padding:8px 10px;background:#f5f4f0;border:none;border-radius:7px;font-size:12px;cursor:pointer;font-family:'DM Sans',sans-serif;color:#111110">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Templates tab ── --}}
    <div id="tab-templates" style="display:none">
        <div style="display:flex;justify-content:flex-end;margin-bottom:14px">
            <button onclick="document.getElementById('template-modal').style.display='flex'"
                    style="padding:7px 15px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif">
                + New template
            </button>
        </div>
        @if($templates->isEmpty())
            <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:48px;text-align:center;color:#8a8880;font-size:13px">
                No templates yet.
            </div>
        @else
            <div style="display:grid;gap:10px;max-width:700px">
                @foreach($templates as $template)
                    <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:18px 20px">
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px;gap:10px;flex-wrap:wrap">
                            <div>
                                <div style="font-size:13px;font-weight:500">{{ $template->name }}</div>
                                <div style="font-size:11px;color:#8a8880;margin-top:2px">{{ ucfirst($template->channel) }} &middot; {{ $template->created_at->format('d M Y') }}</div>
                            </div>
                            <form method="POST" action="{{ route('communications.templates.destroy', $template) }}"
                                  onsubmit="return confirm('Delete this template?')">
                                @csrf @method('DELETE')
                                <button type="submit" style="padding:4px 10px;background:transparent;color:#b91c1c;border:1px solid rgba(185,28,28,0.2);border-radius:6px;font-size:12px;cursor:pointer;font-family:'DM Sans',sans-serif;white-space:nowrap">
                                    Delete
                                </button>
                            </form>
                        </div>
                        <div style="font-size:12px;color:#8a8880;font-style:italic;background:#f5f4f0;padding:10px 12px;border-radius:7px;margin-bottom:10px">
                            "{{ $template->body }}"
                        </div>
                        <button type="button"
                                onclick="document.getElementById('message-body').value='{{ addslashes($template->body) }}';showTab('compose',document.querySelector('.tab'));updateCharCount(document.getElementById('message-body'))"
                                style="padding:4px 10px;background:#e6f2ed;color:#1a6b52;border:none;border-radius:6px;font-size:12px;cursor:pointer;font-family:'DM Sans',sans-serif">
                            Use this template
                        </button>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ── Message logs tab ── --}}
    <div id="tab-logs" style="display:none">
        @if($recentMessages->isEmpty())
            <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:48px;text-align:center;color:#8a8880;font-size:13px">
                No messages sent yet.
            </div>
        @else
            <div class="tbl-scroll">
                <table>
                    <thead>
                        <tr>
                            <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Date</th>
                            <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Recipient</th>
                            <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Message</th>
                            <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentMessages as $msg)
                            @php
                                $sc = ['sent'=>['#dcfce7','#166534'],'delivered'=>['#dcfce7','#166534'],'failed'=>['#fee2e2','#991b1b'],'pending'=>['#fef3c7','#92400e']];
                                $c  = $sc[$msg->status] ?? $sc['pending'];
                            @endphp
                            <tr style="border-bottom:1px solid rgba(0,0,0,0.05)">
                                <td style="padding:10px 14px;font-size:12px;color:#8a8880;white-space:nowrap">{{ $msg->created_at->format('d M, H:i') }}</td>
                                <td style="padding:10px 14px;font-size:13px">
                                    {{ $msg->tenant?->full_name ?? 'Unknown' }}
                                    <div style="font-size:11px;color:#8a8880;font-family:monospace">{{ $msg->phone }}</div>
                                </td>
                                <td style="padding:10px 14px;font-size:12px;color:#8a8880;max-width:280px">{{ Str::limit($msg->body, 80) }}</td>
                                <td style="padding:10px 14px">
                                    <span style="display:inline-flex;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500;background:{{ $c[0] }};color:{{ $c[1] }}">
                                        {{ ucfirst($msg->status) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- Template Modal --}}
<div id="template-modal"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:50;align-items:center;justify-content:center;padding:16px">
    <div class="modal-inner">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid rgba(0,0,0,0.07)">
            <div style="font-size:15px;font-weight:500">New template</div>
            <button onclick="document.getElementById('template-modal').style.display='none'"
                    style="background:none;border:none;font-size:22px;cursor:pointer;color:#8a8880;line-height:1">&times;</button>
        </div>
        <form method="POST" action="{{ route('communications.templates.store') }}">
            @csrf
            <div style="display:grid;gap:13px;margin-bottom:18px">
                <div>
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Template name</label>
                    <input name="name" type="text" required placeholder="e.g. Payment reminder"
                           style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                </div>
                <div>
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Channel</label>
                    <select name="channel" required style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                        <option value="sms">SMS</option>
                        <option value="whatsapp">WhatsApp</option>
                        <option value="both">Both</option>
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Message body</label>
                    <textarea name="body" required rows="4" placeholder="Use {first_name}, {balance}, {unit_number} as placeholders"
                              style="width:100%;padding:9px 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;resize:vertical"></textarea>
                </div>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <button type="submit" style="padding:7px 15px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif">
                    Save template
                </button>
                <button type="button" onclick="document.getElementById('template-modal').style.display='none'"
                        style="padding:7px 15px;background:transparent;color:#8a8880;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;cursor:pointer;font-family:'DM Sans',sans-serif">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showTab(id, el) {
    document.querySelectorAll('[id^="tab-"]').forEach(t => t.style.display = 'none');
    document.getElementById('tab-' + id).style.display = 'block';
    document.querySelectorAll('.tab').forEach(t => {
        t.style.color = '#8a8880';
        t.style.borderBottomColor = 'transparent';
        t.style.fontWeight = '400';
    });
    el.style.color = '#1a6b52';
    el.style.borderBottomColor = '#1a6b52';
    el.style.fontWeight = '500';
}
function toggleRecipientFields(value) {
    document.getElementById('field-property').style.display = value === 'property'   ? 'block' : 'none';
    document.getElementById('field-tenant').style.display   = value === 'individual' ? 'block' : 'none';
}
function loadTemplate(select) {
    if (select.value) {
        document.getElementById('message-body').value = select.value;
        updateCharCount(document.getElementById('message-body'));
    }
}
function insertPlaceholder(text) {
    var ta = document.getElementById('message-body');
    var s  = ta.selectionStart, e = ta.selectionEnd;
    ta.value = ta.value.substring(0,s) + text + ta.value.substring(e);
    ta.selectionStart = ta.selectionEnd = s + text.length;
    ta.focus();
    updateCharCount(ta);
}
function updateCharCount(ta) {
    var el = document.getElementById('char-count');
    if (el) el.textContent = ta.value.length + ' / 160';
}
</script>
</x-layouts.app>