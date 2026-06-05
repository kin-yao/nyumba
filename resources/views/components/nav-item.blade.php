@props(['href', 'active' => false])

<a href="{{ $href }}"
   style="display:flex;align-items:center;gap:9px;padding:8px 18px;font-size:13px;
          color:{{ $active ? '#fff' : 'rgba(255,255,255,0.48)' }};
          border-left:2px solid {{ $active ? '#1a6b52' : 'transparent' }};
          background:{{ $active ? 'rgba(255,255,255,0.06)' : 'transparent' }};
          text-decoration:none;transition:all 0.12s;white-space:nowrap">
    <span style="flex-shrink:0;opacity:{{ $active ? '1' : '0.6' }}">
        {{ $icon }}
    </span>
    {{ $slot }}
</a>