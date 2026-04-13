@extends('layouts.app')

@section('title', 'Profil Relawan')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="inst-page-title">Profil relawan</h2>
            <p class="inst-page-desc">Ringkasan data personal dan kepegawaian.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('master.relawan.cetak-kartu', $relawan) }}" target="_blank" class="inst-btn-primary shrink-0">
                <i data-lucide="credit-card" class="h-[18px] w-[18px]"></i>
                Cetak kartu (PDF)
            </a>
            <a href="{{ route('master.relawan.edit', $relawan) }}" class="inst-btn-outline shrink-0">Ubah data</a>
            <a href="{{ route('master.relawan.index') }}" class="inst-btn-outline shrink-0">Kembali</a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="inst-panel overflow-hidden p-6 lg:col-span-1">
            <div class="flex flex-col items-center text-center">
                @if ($relawan->foto_url)
                    <img src="{{ $relawan->foto_url }}" alt="" class="h-40 w-40 rounded-2xl border object-cover shadow-sm" style="border-color:#d4e8f4;">
                @else
                    <div class="flex h-40 w-40 items-center justify-center rounded-2xl text-3xl font-bold text-white" style="background:#4a9b7a;">R</div>
                @endif
                <h3 class="mt-4 text-lg font-bold" style="color:#1a4a6b;">{{ $relawan->nama_lengkap }}</h3>
                <p class="mt-1 font-mono text-sm" style="color:#4a6b7f;">{{ $relawan->nik }}</p>
                @php
                    $badge =
                        match ($relawan->status) {
                            'aktif' => ['bg' => '#d4f0e8', 'fg' => '#2d7a60', 'teks' => 'Aktif'],
                            'cuti' => ['bg' => '#fff3cd', 'fg' => '#856404', 'teks' => 'Cuti'],
                            default => ['bg' => '#fde8e8', 'fg' => '#c0392b', 'teks' => 'Nonaktif'],
                        };
                @endphp
                <span class="mt-3 inline-flex rounded-full px-3 py-1 text-xs font-semibold" style="background:{{ $badge['bg'] }};color:{{ $badge['fg'] }};">{{ $badge['teks'] }}</span>
            </div>
        </div>

        <div class="inst-panel overflow-hidden p-6 lg:col-span-2">
            <h4 class="mb-4 text-sm font-bold uppercase tracking-wide" style="color:#1a4a6b;">Data personal</h4>
            <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-semibold uppercase" style="color:#7fa8c9;">Jenis kelamin</dt>
                    <dd class="text-sm">{{ $relawan->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase" style="color:#7fa8c9;">Umur</dt>
                    <dd class="text-sm">{{ $relawan->umur !== null ? $relawan->umur.' tahun' : '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase" style="color:#7fa8c9;">Tanggal lahir</dt>
                    <dd class="text-sm">{{ formatTanggal($relawan->tanggal_lahir) }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase" style="color:#7fa8c9;">No. HP</dt>
                    <dd class="text-sm">{{ $relawan->no_hp }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase" style="color:#7fa8c9;">Email</dt>
                    <dd class="text-sm">{{ $relawan->email ?? '—' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase" style="color:#7fa8c9;">Alamat</dt>
                    <dd class="text-sm">{{ $relawan->alamat }}</dd>
                </div>
            </dl>

            <h4 class="mb-4 mt-8 text-sm font-bold uppercase tracking-wide" style="color:#1a4a6b;">Kepegawaian</h4>
            <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-semibold uppercase" style="color:#7fa8c9;">Posisi</dt>
                    <dd class="text-sm">{{ $relawan->posisiRelawan?->nama_posisi ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase" style="color:#7fa8c9;">Dapur</dt>
                    <dd class="text-sm">{{ $relawan->profilMbg?->nama_dapur ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase" style="color:#7fa8c9;">Tanggal bergabung</dt>
                    <dd class="text-sm">{{ formatTanggal($relawan->tanggal_bergabung) }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase" style="color:#7fa8c9;">Gaji pokok</dt>
                    <dd class="text-sm font-semibold">{{ formatRupiah($relawan->gaji_pokok) }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase" style="color:#7fa8c9;">Gaji per hari</dt>
                    <dd class="text-sm font-semibold">{{ formatRupiah($relawan->gaji_per_hari) }}</dd>
                </div>
                @if ($relawan->keterangan)
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-semibold uppercase" style="color:#7fa8c9;">Keterangan</dt>
                        <dd class="text-sm">{{ $relawan->keterangan }}</dd>
                    </div>
                @endif
            </dl>
        </div>
    </div>

    <div class="inst-panel mt-6 overflow-hidden p-6">
        <h4 class="mb-4 text-sm font-bold uppercase tracking-wide" style="color:#1a4a6b;">Riwayat penggajian (6 bulan terakhir)</h4>
        <div class="overflow-x-auto">
            <table class="inst-table">
                <thead>
                    <tr>
                        <th>Periode</th>
                        <th class="text-right">Total gaji</th>
                        <th>Status</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($riwayatGaji as $g)
                        @php
                            $gst = $g->status instanceof \App\Enums\StatusPenggajian ? $g->status : \App\Enums\StatusPenggajian::from((string) $g->status);
                        @endphp
                        <tr>
                            <td>{{ $g->periode_label }}</td>
                            <td class="text-right font-mono">{{ formatRupiah($g->total_gaji) }}</td>
                            <td class="text-xs">{{ $gst->label() }}</td>
                            <td class="inst-td-muted">{{ $g->catatan ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="inst-td-muted py-6 text-center">Belum ada data penggajian untuk periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        if (window.lucide) lucide.createIcons();
    </script>
@endpush
