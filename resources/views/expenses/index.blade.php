<x-layouts.app>
<style>
.exp-wrap { padding: clamp(16px,4vw,34px); padding-bottom: 48px; }

.exp-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.exp-kpi {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 20px;
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
    min-width: 640px;
}

/* Mobile cards */
.exp-cards { display: none; }
.exp-card {
    background: #fff;
    border-radius: 10px;
    border: 1px solid rgba(0,0,0,0.07);
    padding: 14px 16px;
    margin-bottom: 8px;
}
.exp-card-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 8px;
    margin-bottom: 8px;
}
.exp-card-meta {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    margin-bottom: 8px;
}

.modal-inner {
    background: #fff;
    border-radius: 14px;
    padding: 28px;
    width: 100%;
    max-width: 540px;
    max-height: 90vh;
    overflow-y: auto;
}
.modal-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}

@media (max-width: 700px) {
    .exp-kpi { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 640px) {
    .tbl-scroll  { display: none; }
    .exp-cards   { display: block; }
    .modal-inner { width: calc(100vw - 24px); padding: 20px; border-radius: 12px; }
    .modal-grid  { grid-template-columns: 1fr; }
}
</style>

<div class="exp-wrap">

    <div class="exp-header">
        <div>
            <div style="font-family:'DM Serif Display',serif;font-size:clamp(20px,5vw,25px);line-height:1.1">Expenses</div>
            <div style="font-size:13px;color:#8a8880;margin-top:3px">Track property costs</div>
        </div>
        <button onclick="document.getElementById('add-expense-modal').style.display='flex'"
                style="display:inline-flex;align-items:center;gap:6px;padding:7px 15px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif;white-space:nowrap;flex-shrink:0">
            + Add expense
        </button>
    </div>

    @if(session('success'))
        <div style="background:#dcfce7;border:1px solid #86efac;border-radius:10px;padding:11px 15px;margin-bottom:16px;font-size:13px;color:#166534">
            {{ session('success') }}
        </div>
    @endif

    {{-- KPI strip --}}
    <div class="exp-kpi">
        <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:16px 20px">
            <div style="font-size:10px;color:#8a8880;text-transform:uppercase;letter-spacing:.05em;margin-bottom:7px">This month</div>
            <div style="font-family:'DM Serif Display',serif;font-size:clamp(18px,3vw,24px);color:#b91c1c">{{ currency($totalThisMonth) }}</div>
        </div>
        @foreach($byCategory->take(3) as $category => $amount)
            <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:16px 20px">
                <div style="font-size:10px;color:#8a8880;text-transform:uppercase;letter-spacing:.05em;margin-bottom:7px">
                    {{ ucfirst(str_replace('_',' ',$category)) }}
                </div>
                <div style="font-family:'DM Serif Display',serif;font-size:clamp(18px,3vw,24px)">{{ currency($amount) }}</div>
            </div>
        @endforeach
    </div>

    @if($expenses->isEmpty())
        <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:60px;text-align:center;color:#8a8880;font-size:13px">
            <div style="font-size:36px;margin-bottom:12px">💰</div>
            <div style="font-weight:500;margin-bottom:4px">No expenses recorded yet</div>
            <div>Add your first expense to start tracking costs</div>
        </div>
    @else

        @php
            $categoryColors = [
                'repairs'           => ['#dbeafe','#1e40af'],
                'utilities'         => ['#dcfce7','#166534'],
                'salaries'          => ['#fef3c7','#92400e'],
                'supplies'          => ['#f3f4f6','#4b5563'],
                'insurance'         => ['#ede9fe','#5b21b6'],
                'land_rates'        => ['#fce7f3','#9d174d'],
                'professional_fees' => ['#dbeafe','#1e40af'],
                'marketing'         => ['#dcfce7','#166534'],
                'other'             => ['#f3f4f6','#4b5563'],
            ];
        @endphp

        {{-- Desktop table --}}
        <div class="tbl-scroll">
            <table>
                <thead>
                    <tr>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Date</th>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Description</th>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Category</th>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Property</th>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Vendor</th>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Method</th>
                        <th style="font-size:10px;letter-spacing:.05em;color:#8a8880;text-transform:uppercase;padding:9px 14px;text-align:right;border-bottom:1px solid rgba(0,0,0,0.07);font-weight:500">Amount</th>
                        <th style="border-bottom:1px solid rgba(0,0,0,0.07)"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($expenses as $expense)
                        @php $cc = $categoryColors[$expense->category] ?? $categoryColors['other']; @endphp
                        <tr style="border-bottom:1px solid rgba(0,0,0,0.05)">
                            <td style="padding:11px 14px;font-size:13px;color:#8a8880;white-space:nowrap">{{ $expense->expense_date->format('d M Y') }}</td>
                            <td style="padding:11px 14px;font-size:13px;font-weight:500">
                                {{ $expense->description }}
                                @if($expense->notes)
                                    <div style="font-size:11px;color:#8a8880;font-weight:400">{{ $expense->notes }}</div>
                                @endif
                            </td>
                            <td style="padding:11px 14px">
                                <span style="display:inline-flex;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500;background:{{ $cc[0] }};color:{{ $cc[1] }}">
                                    {{ ucfirst(str_replace('_',' ',$expense->category)) }}
                                </span>
                            </td>
                            <td style="padding:11px 14px;font-size:13px;color:#8a8880">{{ $expense->property?->name ?? 'General' }}</td>
                            <td style="padding:11px 14px;font-size:13px;color:#8a8880">{{ $expense->vendor ?? '-' }}</td>
                            <td style="padding:11px 14px;font-size:12px;color:#8a8880;text-transform:uppercase">{{ $expense->payment_method }}</td>
                            <td style="padding:11px 14px;font-size:13px;font-weight:500;text-align:right;color:#b91c1c;white-space:nowrap">{{ currency($expense->amount) }}</td>
                            <td style="padding:11px 14px;text-align:right">
                                <form method="POST" action="{{ route('expenses.destroy', $expense) }}"
                                      onsubmit="return confirm('Delete this expense?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" style="display:inline-flex;padding:4px 10px;background:transparent;color:#b91c1c;border:1px solid rgba(185,28,28,0.2);border-radius:6px;font-size:12px;cursor:pointer;font-family:'DM Sans',sans-serif">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="6" style="padding:11px 14px;font-size:13px;font-weight:500;border-top:2px solid rgba(0,0,0,0.07)">Total all time</td>
                        <td style="padding:11px 14px;text-align:right;color:#b91c1c;border-top:2px solid rgba(0,0,0,0.07);font-family:'DM Serif Display',serif;font-size:16px;font-weight:600">
                            {{ currency($expenses->sum('amount')) }}
                        </td>
                        <td style="border-top:2px solid rgba(0,0,0,0.07)"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Mobile cards --}}
        <div class="exp-cards">
            @foreach($expenses as $expense)
                @php $cc = $categoryColors[$expense->category] ?? $categoryColors['other']; @endphp
                <div class="exp-card">
                    <div class="exp-card-top">
                        <div style="min-width:0">
                            <div style="font-weight:500;font-size:13px;margin-bottom:2px">{{ $expense->description }}</div>
                            @if($expense->notes)
                                <div style="font-size:11px;color:#8a8880">{{ $expense->notes }}</div>
                            @endif
                        </div>
                        <div style="text-align:right;flex-shrink:0">
                            <div style="font-size:15px;font-weight:600;color:#b91c1c">{{ currency($expense->amount) }}</div>
                            <div style="font-size:11px;color:#8a8880;margin-top:2px">{{ $expense->expense_date->format('d M Y') }}</div>
                        </div>
                    </div>
                    <div class="exp-card-meta">
                        <span style="font-size:11px;font-weight:500;padding:2px 8px;border-radius:20px;background:{{ $cc[0] }};color:{{ $cc[1] }}">
                            {{ ucfirst(str_replace('_',' ',$expense->category)) }}
                        </span>
                        @if($expense->property)
                            <span style="font-size:11px;color:#8a8880;background:#f5f4f0;padding:2px 8px;border-radius:20px">
                                {{ $expense->property->name }}
                            </span>
                        @endif
                        <span style="font-size:11px;color:#8a8880;text-transform:uppercase">{{ $expense->payment_method }}</span>
                    </div>
                    <form method="POST" action="{{ route('expenses.destroy', $expense) }}"
                          onsubmit="return confirm('Delete this expense?')">
                        @csrf @method('DELETE')
                        <button type="submit" style="width:100%;padding:7px;background:transparent;color:#b91c1c;border:1px solid rgba(185,28,28,0.2);border-radius:7px;font-size:12px;cursor:pointer;font-family:'DM Sans',sans-serif">
                            Delete
                        </button>
                    </form>
                </div>
            @endforeach

            {{-- Mobile total --}}
            <div style="background:#fff;border-radius:10px;border:1px solid rgba(0,0,0,0.07);padding:14px 16px;display:flex;justify-content:space-between;align-items:center">
                <span style="font-size:13px;font-weight:500">Total all time</span>
                <span style="font-family:'DM Serif Display',serif;font-size:18px;color:#b91c1c">{{ currency($expenses->sum('amount')) }}</span>
            </div>
        </div>
    @endif
</div>

{{-- Add Expense Modal --}}
<div id="add-expense-modal"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:50;align-items:center;justify-content:center;padding:16px">
    <div class="modal-inner">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid rgba(0,0,0,0.07)">
            <div style="font-size:15px;font-weight:500">Add an expense</div>
            <button onclick="document.getElementById('add-expense-modal').style.display='none'"
                    style="background:none;border:none;font-size:22px;cursor:pointer;color:#8a8880;line-height:1">&times;</button>
        </div>
        <form method="POST" action="{{ route('expenses.store') }}">
            @csrf
            <div class="modal-grid" style="margin-bottom:13px">
                <div>
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Date</label>
                    <input name="expense_date" type="date" required value="{{ date('Y-m-d') }}"
                           style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                </div>
                <div>
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Amount ({{ currency_symbol() }})</label>
                    <input name="amount" type="number" required min="1"
                           style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                </div>
            </div>
            <div style="margin-bottom:13px">
                <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Description</label>
                <input name="description" type="text" required placeholder="e.g. Plumbing repair, Unit C1"
                       style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
            </div>
            <div class="modal-grid" style="margin-bottom:13px">
                <div>
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Category</label>
                    <select name="category" required style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                        <option value="" disabled selected>Select category</option>
                        <option value="repairs">Repairs and Maintenance</option>
                        <option value="utilities">Utilities</option>
                        <option value="salaries">Salaries and Wages</option>
                        <option value="supplies">Supplies</option>
                        <option value="insurance">Insurance</option>
                        <option value="land_rates">Land Rates</option>
                        <option value="professional_fees">Professional Fees</option>
                        <option value="marketing">Marketing</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Property</label>
                    <select name="property_id" style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                        <option value="">General</option>
                        @foreach(\App\Models\Property::all() as $property)
                            <option value="{{ $property->id }}">{{ $property->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-grid" style="margin-bottom:13px">
                <div>
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Paid to (vendor)</label>
                    <input name="vendor" type="text" placeholder="Optional"
                           style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                </div>
                <div>
                    <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Payment method</label>
                    <select name="payment_method" required style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
                        <option value="" disabled selected>Select method</option>
                        <option value="cash">Cash</option>
                        <option value="mpesa">M-Pesa</option>
                        <option value="bank">Bank transfer</option>
                        <option value="cheque">Cheque</option>
                    </select>
                </div>
            </div>
            <div style="margin-bottom:13px">
                <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Reference number</label>
                <input name="reference" type="text" placeholder="Optional - M-Pesa ref or receipt number"
                       style="width:100%;height:36px;padding:0 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
            </div>
            <div style="margin-bottom:18px">
                <label style="display:block;font-size:10px;font-weight:500;color:#8a8880;letter-spacing:.04em;text-transform:uppercase;margin-bottom:5px">Notes</label>
                <textarea name="notes" rows="2" placeholder="Optional..."
                          style="width:100%;padding:9px 11px;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;resize:vertical"></textarea>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <button type="submit" style="padding:7px 15px;background:#1a6b52;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif">
                    Save expense
                </button>
                <button type="button" onclick="document.getElementById('add-expense-modal').style.display='none'"
                        style="padding:7px 15px;background:transparent;color:#8a8880;border:1px solid rgba(0,0,0,0.1);border-radius:7px;font-size:13px;cursor:pointer;font-family:'DM Sans',sans-serif">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>
</x-layouts.app>