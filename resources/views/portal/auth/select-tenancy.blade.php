<x-layouts.portal>
<div style="padding-top:24px;text-align:center;margin-bottom:22px">
    <div style="font-family:'DM Serif Display',serif;font-size:20px">Choose an account</div>
    <div style="font-size:13px;color:#8a8880;margin-top:4px">This number is linked to more than one tenancy</div>
</div>

<div style="display:grid;gap:10px">
    @foreach($tenants as $t)
        @php
            $lease    = $t->activeLease;
            $unit     = $lease?->unit;
            $property = $unit?->property;
        @endphp
        <form method="POST" action="{{ route('portal.select-tenancy.submit') }}">
            @csrf
            <input type="hidden" name="tenant_id" value="{{ $t->id }}">
            <button type="submit" style="width:100%;text-align:left;background:#fff;border:1px solid rgba(0,0,0,0.08);border-radius:10px;padding:14px 16px;cursor:pointer;font-family:'DM Sans',sans-serif">
                <div style="font-size:14px;font-weight:600;margin-bottom:2px">{{ $property->name ?? 'Property' }}</div>
                <div style="font-size:12.5px;color:#8a8880">Unit {{ $unit->name ?? '-' }} &middot; {{ $property?->account?->name ?? 'Landlord' }}</div>
            </button>
        </form>
    @endforeach
</div>

<div style="text-align:center;margin-top:18px">
    <a href="{{ route('portal.login') }}" style="font-size:12px;color:#8a8880">Use a different number</a>
</div>
</x-layouts.portal>