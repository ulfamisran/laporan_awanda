@php
    use App\Support\SidebarMenu;
    $menu = SidebarMenu::items();
@endphp

<nav class="mt-2 space-y-1 px-1 text-sm font-medium">
    @foreach ($menu as $item)
        @if (! empty($item['roles']) && ! auth()->user()->hasAnyRole($item['roles']))
            @continue
        @endif

        @if (($item['type'] ?? null) === 'link')
            @php
                $active = request()->routeIs(...(array) $item['match']);
            @endphp
            <a
                href="{{ route($item['route']) }}"
                class="sidebar-item flex w-full items-center gap-3 rounded-lg px-4 py-2.5 text-left {{ $active ? 'active' : '' }}"
                style="{{ $active ? 'color:#4a9b7a;' : 'color:#b8d4e8;' }}"
            >
                @include('layouts.partials.lucide-icon', ['icon' => $item['icon']])
                <span>{{ $item['label'] }}</span>
            </a>
        @elseif (($item['type'] ?? null) === 'group')
            @php
                $visibleChildren = collect($item['children'] ?? [])->filter(function ($child) {
                    if (! empty($child['roles']) && ! auth()->user()->hasAnyRole($child['roles'])) {
                        return false;
                    }

                    return true;
                });
            @endphp
            @if ($visibleChildren->isEmpty())
                @continue
            @endif
            @php
                $groupActive = $visibleChildren->contains(fn ($c) => request()->routeIs(...(array) $c['match']));
            @endphp
            <div x-data="{ open: {{ $groupActive ? 'true' : 'false' }} }" class="rounded-lg">
                <button
                    type="button"
                    @click="open = !open"
                    class="sidebar-item flex w-full items-center justify-between gap-3 rounded-lg px-4 py-2.5 text-left {{ $groupActive ? 'active' : '' }}"
                    style="{{ $groupActive ? 'color:#4a9b7a;' : 'color:#b8d4e8;' }}"
                >
                    <span class="flex items-center gap-3">
                        @include('layouts.partials.lucide-icon', ['icon' => $item['icon']])
                        {{ $item['label'] }}
                    </span>
                    <svg class="h-4 w-4 shrink-0 transition" :class="open ? 'rotate-180' : ''" style="color:#7fa8c9;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div
                    x-show="open"
                    x-transition
                    x-cloak
                    class="mt-1 space-y-0.5 border-l border-white/10 pl-3 ml-4"
                >
                    @foreach ($visibleChildren as $child)
                        @php $cActive = request()->routeIs(...(array) $child['match']); @endphp
                        <a
                            href="{{ route($child['route']) }}"
                            class="sidebar-item flex w-full items-center gap-2 rounded-md px-3 py-2 text-[13px] {{ $cActive ? 'active' : '' }}"
                            style="{{ $cActive ? 'color:#4a9b7a;' : 'color:#9ec0dc;' }}"
                        >
                            <span class="h-1 w-1 shrink-0 rounded-full" style="background: currentColor; opacity: 0.6;"></span>
                            {{ $child['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach
</nav>
