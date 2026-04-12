@php
    $lucideMap = [
        'home' => 'layout-dashboard',
        'database' => 'database',
        'boxes' => 'package',
        'wallet' => 'wallet',
        'cash' => 'banknote',
        'trash' => 'recycle',
        'chart' => 'bar-chart-3',
        'cog' => 'settings',
    ];
    $lucide = $lucideMap[$icon ?? 'home'] ?? 'circle';
    $class = $class ?? 'w-[18px] h-[18px] shrink-0';
@endphp
<i data-lucide="{{ $lucide }}" class="{{ $class }}" aria-hidden="true"></i>
