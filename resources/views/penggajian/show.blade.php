@extends('layouts.app')

@section('title', 'Detail Penggajian')

@section('content')
    @php
        $p = $penggajian;
        $u = auth()->user();
        $canApprove = $u->hasAnyRole(['admin_pusat', 'super_admin']);
        $canBayar = $u->hasRole('super_admin');
        $canEditDraft = $p->status === \App\Enums\StatusPenggajian::Draft && $u->hasAnyRole(['super_admin', 'admin_pusat', 'admin']);
        $st = $p->status instanceof \App\Enums\StatusPenggajian ? $p->status : \App\Enums\StatusPenggajian::from((string) $p->status);
    @endphp

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="inst-page-title">Detail penggajian</h2>
            <p class="inst-page-desc">{{ $p->relawan?->nama_lengkap }} — {{ $p->periode_label }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('penggajian.slip-pdf', $p) }}" target="_blank" class="inst-btn-secondary text-sm">Cetak slip (PDF)</a>
            <a href="{{ route('penggajian.index', ['mulai' => optional($p->periode_mulai)->toDateString(), 'selesai' => optional($p->periode_selesai)->toDateString()]) }}" class="inst-btn-secondary text-sm">Kembali ke rekap</a>
            @if ($canEditDraft)
                <a href="{{ route('penggajian.edit', $p) }}" class="inst-btn-primary text-sm">Ubah komponen</a>
            @endif
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="inst-panel space-y-3 p-6">
            <h3 class="text-sm font-bold uppercase tracking-wide" style="color:#1a4a6b;">Ringkasan</h3>
            <dl class="grid gap-2 text-sm">
                <div class="flex justify-between gap-4"><dt class="inst-td-muted">Status</dt>
                    <dd>
                        @if ($st === \App\Enums\StatusPenggajian::Draft)
                            <span class="rounded-full px-2 py-0.5 text-xs font-semibold" style="background:#e8ecef;color:#4a5568;">Draft</span>
                        @elseif ($st === \App\Enums\StatusPenggajian::Approved)
                            <span class="rounded-full px-2 py-0.5 text-xs font-semibold" style="background:#dbeafe;color:#1e40af;">Disetujui</span>
                        @else
                            <span class="rounded-full px-2 py-0.5 text-xs font-semibold" style="background:#d1fae5;color:#065f46;">Dibayar</span>
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between gap-4"><dt class="inst-td-muted">Dapur</dt><dd class="font-medium">{{ $p->profilMbg?->nama_dapur }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="inst-td-muted">Metode</dt><dd class="font-semibold">{{ $p->metode_penggajian === 'kehadiran' ? 'Berdasarkan kehadiran' : 'Berdasarkan gaji pokok' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="inst-td-muted">Jumlah hadir</dt><dd class="font-semibold">{{ $p->jumlah_hadir }} hari</dd></div>
                <div class="flex justify-between gap-4"><dt class="inst-td-muted">Gaji per hari</dt><dd class="font-mono">{{ formatRupiah($p->relawan?->gaji_per_hari ?? 0) }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="inst-td-muted">Gaji pokok periode</dt><dd class="font-mono">{{ formatRupiah($p->gaji_pokok) }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="inst-td-muted">Tunj. transport</dt><dd class="font-mono">{{ formatRupiah($p->tunjangan_transport) }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="inst-td-muted">Tunj. makan</dt><dd class="font-mono">{{ formatRupiah($p->tunjangan_makan) }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="inst-td-muted">Tunj. lainnya</dt><dd class="font-mono">{{ formatRupiah($p->tunjangan_lainnya) }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="inst-td-muted">Potongan</dt><dd class="font-mono">{{ formatRupiah($p->potongan) }}</dd></div>
                @if ($p->keterangan_potongan)
                    <div class="flex justify-between gap-4"><dt class="inst-td-muted">Ket. potongan</dt><dd>{{ $p->keterangan_potongan }}</dd></div>
                @endif
                <div class="flex justify-between gap-4 border-t pt-2" style="border-color:#d4e8f4;">
                    <dt class="font-semibold" style="color:#1a4a6b;">Total gaji</dt>
                    <dd class="font-mono text-lg font-bold" style="color:#2d7a60;">{{ formatRupiah($p->total_gaji) }}</dd>
                </div>
                <div class="flex justify-between gap-4"><dt class="inst-td-muted">Tanggal bayar</dt><dd>{{ $p->tanggal_bayar?->format('d/m/Y') ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="inst-td-muted">Catatan</dt><dd class="inst-td-muted">{{ $p->catatan ?? '—' }}</dd></div>
            </dl>
        </div>

        <div class="inst-panel space-y-4 p-6">
            <h3 class="text-sm font-bold uppercase tracking-wide" style="color:#1a4a6b;">Alur &amp; aksi</h3>

            @if ($st === \App\Enums\StatusPenggajian::Draft && $canApprove)
                <form method="post" action="{{ route('penggajian.approve', $p) }}" class="inline">
                    @csrf
                    <button type="submit" class="inst-btn-primary text-sm">Setujui (draft → disetujui)</button>
                </form>
            @endif

            @if ($st === \App\Enums\StatusPenggajian::Approved && $canBayar)
                <form method="post" action="{{ route('penggajian.bayar', $p) }}" class="space-y-2">
                    @csrf
                    <div>
                        <label for="tanggal_bayar" class="inst-label">Tanggal pembayaran <span class="text-red-600">*</span></label>
                        <input type="date" name="tanggal_bayar" id="tanggal_bayar" class="inst-input mt-1 w-full" required value="{{ old('tanggal_bayar', now()->toDateString()) }}">
                    </div>
                    <button type="submit" class="inst-btn-primary text-sm">Tandai dibayar</button>
                </form>
            @endif

            @if ($st === \App\Enums\StatusPenggajian::Draft && $u->hasAnyRole(['super_admin', 'admin_pusat', 'admin']))
                <form method="post" action="{{ route('penggajian.destroy', $p) }}" class="form-hapus-penggajian inline" onsubmit="return confirm('Hapus penggajian draft ini?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm font-semibold" style="color:#c0392b;">Hapus draft</button>
                </form>
            @endif

            <div class="border-t pt-4 text-xs inst-td-muted" style="border-color:#d4e8f4;">
                <p>Dibuat oleh: {{ $p->creator?->name ?? '—' }}</p>
                @if ($p->approver)
                    <p>Disetujui oleh: {{ $p->approver->name }}</p>
                @endif
            </div>
        </div>
    </div>

    <div class="inst-panel mt-6 p-6">
        <h3 class="mb-2 text-sm font-bold uppercase tracking-wide" style="color:#1a4a6b;">Pratinjau slip</h3>
        <p class="text-sm inst-td-muted mb-4">Tampilan ringkas; PDF lengkap gunakan tombol cetak slip.</p>
        <iframe src="{{ route('penggajian.slip-pdf', $p) }}" class="h-[520px] w-full rounded-lg border" style="border-color:#d4e8f4;"></iframe>
    </div>
@endsection

@push('scripts')
    <script>
        if (window.lucide) lucide.createIcons();
    </script>
@endpush
