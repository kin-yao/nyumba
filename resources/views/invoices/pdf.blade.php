<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->reference }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 13px;
            color: #111110;
            background: #ffffff;
            padding: 40px;
        }

        .header {
            display: table;
            width: 100%;
            margin-bottom: 36px;
            border-bottom: 2px solid #1a6b52;
            padding-bottom: 20px;
        }

        .header-left {
            display: table-cell;
            vertical-align: top;
            width: 50%;
        }

        .header-right {
            display: table-cell;
            vertical-align: top;
            text-align: right;
            width: 50%;
        }

        .business-name {
            font-size: 14px;
            font-weight: bold;
            color: #111110;
            margin-top: 4px;
        }

        .business-detail {
            font-size: 11px;
            color: #8a8880;
            margin-top: 2px;
        }

        .invoice-label {
            font-size: 28px;
            font-weight: bold;
            color: #111110;
            letter-spacing: -1px;
        }

        .invoice-ref {
            font-size: 13px;
            color: #1a6b52;
            font-weight: bold;
            margin-top: 4px;
        }

        .invoice-meta {
            font-size: 11px;
            color: #8a8880;
            margin-top: 3px;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            margin-top: 6px;
        }

        .status-paid {
            background: #dcfce7;
            color: #166534;
        }

        .status-unpaid {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-partial {
            background: #fef3c7;
            color: #92400e;
        }

        .parties {
            display: table;
            width: 100%;
            margin-bottom: 28px;
        }

        .party-box {
            display: table-cell;
            width: 48%;
            vertical-align: top;
            background: #f5f4f0;
            padding: 14px 16px;
            border-radius: 8px;
        }

        .party-gap {
            display: table-cell;
            width: 4%;
        }

        .party-label {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #8a8880;
            margin-bottom: 6px;
        }

        .party-name {
            font-size: 14px;
            font-weight: bold;
            color: #111110;
            margin-bottom: 3px;
        }

        .party-detail {
            font-size: 11px;
            color: #8a8880;
            margin-top: 2px;
        }

        .line-items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .line-items thead tr {
            background: #111110;
            color: #ffffff;
        }

        .line-items thead th {
            padding: 9px 12px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            text-align: left;
        }

        .line-items thead th.right {
            text-align: right;
        }

        .line-items tbody tr {
            border-bottom: 1px solid #ece9e2;
        }

        .line-items tbody tr:nth-child(even) {
            background: #faf9f7;
        }

        .line-items tbody td {
            padding: 10px 12px;
            font-size: 12px;
        }

        .line-items tbody td.right {
            text-align: right;
            font-weight: 500;
        }

        .line-items tbody td.type {
            color: #8a8880;
            font-size: 11px;
            text-transform: capitalize;
        }

        .totals {
            width: 260px;
            margin-left: auto;
            margin-bottom: 28px;
        }

        .totals-row {
            display: table;
            width: 100%;
            padding: 6px 0;
            border-bottom: 1px solid #ece9e2;
        }

        .totals-label {
            display: table-cell;
            font-size: 12px;
            color: #8a8880;
        }

        .totals-value {
            display: table-cell;
            font-size: 12px;
            font-weight: 500;
            text-align: right;
        }

        .totals-final {
            display: table;
            width: 100%;
            background: #1a6b52;
            border-radius: 6px;
            margin-top: 6px;
            padding: 10px 12px;
        }

        .totals-final-label {
            display: table-cell;
            font-size: 13px;
            font-weight: bold;
            color: #ffffff;
        }

        .totals-final-value {
            display: table-cell;
            font-size: 16px;
            font-weight: bold;
            color: #ffffff;
            text-align: right;
        }

        .payment-history {
            margin-bottom: 28px;
        }

        .section-title {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #8a8880;
            margin-bottom: 8px;
        }

        .payment-row {
            display: table;
            width: 100%;
            padding: 7px 10px;
            border-bottom: 1px solid #ece9e2;
            font-size: 12px;
        }

        .payment-row-date {
            display: table-cell;
            color: #8a8880;
            width: 30%;
        }

        .payment-row-ref {
            display: table-cell;
            color: #8a8880;
            width: 40%;
        }

        .payment-row-amount {
            display: table-cell;
            text-align: right;
            font-weight: 500;
            color: #15803d;
            width: 30%;
        }

        .footer {
            border-top: 1px solid #ece9e2;
            padding-top: 16px;
            text-align: center;
            font-size: 11px;
            color: #8a8880;
        }

        .due-box {
            background: #fff8e1;
            border: 1px solid #fcd34d;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 12px;
            color: #92400e;
        }

        .paid-box {
            background: #dcfce7;
            border: 1px solid #86efac;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 12px;
            color: #166534;
        }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="header">
        <div class="header-left">
            @if($account->logo_path)
                <img src="{{ storage_path('app/public/' . $account->logo_path) }}"
                     alt="{{ $account->name }}"
                     style="height:50px;object-fit:contain;margin-bottom:6px;display:block">
            @else
                <img src="{{ public_path('images/logo.png') }}"
                     alt="Nyumba"
                     style="height:44px;object-fit:contain;margin-bottom:6px;display:block">
            @endif
            <div class="business-name">{{ $account->name }}</div>
            @if($account->phone)
                <div class="business-detail">{{ $account->phone }}</div>
            @endif
            @if($account->email)
                <div class="business-detail">{{ $account->email }}</div>
            @endif
            @if($account->county)
                <div class="business-detail">{{ $account->county }}, Kenya</div>
            @endif
        </div>
        <div class="header-right">
            <div class="invoice-label">INVOICE</div>
            <div class="invoice-ref">{{ $invoice->reference }}</div>
            <div class="invoice-meta">
                Period: {{ \Carbon\Carbon::createFromDate($invoice->period_year, $invoice->period_month, 1)->format('F Y') }}
            </div>
            <div class="invoice-meta">
                Invoice date: {{ $invoice->invoice_date->format('d M Y') }}
            </div>
            <div class="invoice-meta">
                Due date: {{ $invoice->due_date->format('d M Y') }}
            </div>
            @php
                if ($amountDue <= 0) {
                    $statusClass = 'status-paid';
                    $statusLabel = 'PAID';
                } elseif ($amountPaid > 0) {
                    $statusClass = 'status-partial';
                    $statusLabel = 'PARTIALLY PAID';
                } else {
                    $statusClass = 'status-unpaid';
                    $statusLabel = 'UNPAID';
                }
            @endphp
            <div class="status-badge {{ $statusClass }}">{{ $statusLabel }}</div>
        </div>
    </div>

    {{-- Parties --}}
    <div class="parties">
        <div class="party-box">
            <div class="party-label">From</div>
            <div class="party-name">{{ $account->name }}</div>
            @if($account->phone)
                <div class="party-detail">{{ $account->phone }}</div>
            @endif
            @if($account->county)
                <div class="party-detail">{{ $account->county }}, Kenya</div>
            @endif
        </div>
        <div class="party-gap"></div>
        <div class="party-box">
            <div class="party-label">Bill to</div>
            <div class="party-name">{{ $invoice->lease->tenant->full_name }}</div>
            <div class="party-detail">{{ $invoice->lease->tenant->phone }}</div>
            <div class="party-detail">
                Unit {{ $invoice->lease->unit->name }},
                {{ $invoice->lease->unit->property->name }}
            </div>
            @if($invoice->lease->unit->property->county)
                <div class="party-detail">{{ $invoice->lease->unit->property->county }}, Kenya</div>
            @endif
        </div>
    </div>

    {{-- Status notice --}}
    @if($amountDue <= 0)
        <div class="paid-box">
            This invoice has been fully paid. Thank you.
        </div>
    @else
        <div class="due-box">
            <strong>Amount due: {{ currency($amountDue) }}</strong>
            &nbsp;&nbsp;&middot;&nbsp;&nbsp;
            Please pay by {{ $invoice->due_date->format('d M Y') }}
        </div>
    @endif

    {{-- Line items --}}
    <table class="line-items">
        <thead>
            <tr>
                <th style="width:50%">Description</th>
                <th style="width:20%">Type</th>
                <th style="width:15%" class="right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->lineItems as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="type">{{ ucfirst(str_replace('_', ' ', $item->type)) }}</td>
                    <td class="right">{{ currency($item->amount) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals --}}
    <div class="totals">
        <div class="totals-row">
            <div class="totals-label">Subtotal</div>
            <div class="totals-value">{{ currency($invoice->total_amount) }}</div>
        </div>
        @if($amountPaid > 0)
            <div class="totals-row">
                <div class="totals-label">Amount paid</div>
                <div class="totals-value" style="color:#15803d">- {{ currency($amountPaid) }}</div>
            </div>
        @endif
        <div class="totals-final">
            <div class="totals-final-label">{{ $amountDue <= 0 ? 'Total paid' : 'Amount due' }}</div>
            <div class="totals-final-value">{{ currency(max($amountDue, 0)) }}</div>
        </div>
    </div>

    {{-- Payment history --}}
    @if($invoice->allocations->isNotEmpty())
        <div class="payment-history">
            <div class="section-title">Payment history</div>
            @foreach($invoice->allocations as $allocation)
                <div class="payment-row">
                    <div class="payment-row-date">
                        {{ $allocation->payment->payment_date->format('d M Y') }}
                    </div>
                    <div class="payment-row-ref">
                        {{ strtoupper($allocation->payment->method) }}
                        @if($allocation->payment->reference)
                            &middot; {{ $allocation->payment->reference }}
                        @endif
                    </div>
                    <div class="payment-row-amount">
                        {{ currency($allocation->amount) }}
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <p>Thank you for your tenancy. For any queries please contact {{ $account->name }}.</p>
        @if($account->phone)
            <p style="margin-top:4px">{{ $account->phone }}
                @if($account->email) &nbsp;&middot;&nbsp; {{ $account->email }} @endif
            </p>
        @endif
        <p style="margin-top:8px;color:#d1d5db">
            Generated by Nyumba &middot; {{ now()->format('d M Y H:i') }}
        </p>
    </div>

</body>
</html>