<div class="flex flex-wrap gap-1">
    @forelse ($user->roles as $r)
        @php
            $cls = match ($r->name) {
                'super_admin' => 'bg-emerald-100 text-emerald-800 ring-emerald-600/20',
                'admin_pusat' => 'bg-blue-100 text-blue-800 ring-blue-600/20',
                default => 'bg-slate-100 text-slate-700 ring-slate-500/20',
            };
        @endphp
        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $cls }}">
            {{ str_replace('_', ' ', $r->name) }}
        </span>
    @empty
        <span class="text-xs text-slate-400">—</span>
    @endforelse
</div>
