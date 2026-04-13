@php
    $periodeAktif = \App\Support\PeriodeTenant::model();
@endphp

<div class="mb-4 rounded-lg border px-3 py-2 text-sm" style="border-color:#d4e8f4;background:#f8fbfd;color:#1a4a6b;">
    <span class="font-semibold">Periode aktif:</span>
    {{ $periodeAktif?->labelRingkas() ?? 'Belum ada periode aktif' }}
</div>
