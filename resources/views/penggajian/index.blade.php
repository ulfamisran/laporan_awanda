@extends('layouts.app')

@section('title', 'Penggajian Relawan')

@section('content')
    @php
        $u = auth()->user();
        $isAdminDapur = $u->hasRole('admin') && ! $u->hasAnyRole(['super_admin', 'admin_pusat']);
    @endphp

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="inst-page-title">Penggajian relawan</h2>
            <p class="inst-page-desc">Tabel batch penggajian per periode dan metode.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('penggajian.create') }}" class="inst-btn-primary shrink-0">Generate penggajian</a>
        </div>
    </div>

    <form method="get" action="{{ route('penggajian.index') }}" class="inst-filter-panel mb-4 space-y-3">
        <div class="flex flex-wrap gap-4">
            @if (! $isAdminDapur)
                <div>
                    <label for="f-status" class="inst-label-filter">Status</label>
                    <select id="f-status" name="status" class="inst-select mt-2 w-44">
                        <option value="" @selected($statusFilter === '')>Semua</option>
                        <option value="draft" @selected($statusFilter === 'draft')>Draft</option>
                        <option value="approved" @selected($statusFilter === 'approved')>Disetujui</option>
                        <option value="dibayar" @selected($statusFilter === 'dibayar')>Dibayar</option>
                    </select>
                </div>
            @endif
        </div>
        <button type="submit" class="inst-btn-secondary text-sm">Terapkan filter</button>
    </form>

    <div class="inst-panel overflow-hidden p-4 sm:p-6">
        <div class="overflow-x-auto">
            <table class="inst-table">
                <thead>
                    <tr>
                        <th>Periode penggajian</th>
                        <th>Metode penggajian</th>
                        <th>Status</th>
                        <th class="text-right">Total karyawan</th>
                        <th class="text-right">Total pembayaran</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($batches as $batch)
                        <tr>
                            <td class="font-medium">{{ $batch['periode_label'] }}</td>
                            <td>{{ $batch['metode_penggajian'] === 'kehadiran' ? 'Berdasarkan kehadiran' : 'Berdasarkan gaji pokok' }}</td>
                            <td>
                                @if ($batch['status'] === 'draft')
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold" style="background:#e8ecef;color:#4a5568;">Draft</span>
                                @elseif ($batch['status'] === 'approved')
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold" style="background:#dbeafe;color:#1e40af;">Disetujui</span>
                                @elseif ($batch['status'] === 'dibayar')
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold" style="background:#d1fae5;color:#065f46;">Dibayar</span>
                                @else
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold" style="background:#fef3c7;color:#92400e;">Campuran</span>
                                @endif
                            </td>
                            <td class="text-right font-semibold">{{ $batch['total_karyawan'] }}</td>
                            <td class="text-right font-mono font-semibold">{{ formatRupiah($batch['total_pembayaran']) }}</td>
                            <td class="text-right">
                                <div class="flex flex-col items-end gap-2">
                                    <a href="{{ route('penggajian.batch-detail', ['mulai' => $batch['periode_mulai'], 'selesai' => $batch['periode_selesai'], 'metode' => $batch['metode_penggajian']]) }}"
                                        class="inst-btn-secondary !h-8 !px-3 !py-1 text-xs inline-flex items-center gap-1">
                                        <i data-lucide="list" class="h-3.5 w-3.5"></i>
                                        Lihat detail
                                    </a>
                                    @if ($batch['status'] === 'draft' && $u->hasAnyRole(['super_admin', 'admin_pusat']))
                                        <form method="post" action="{{ route('penggajian.batch-status') }}" class="w-full">
                                            @csrf
                                            <input type="hidden" name="mulai" value="{{ $batch['periode_mulai'] }}">
                                            <input type="hidden" name="selesai" value="{{ $batch['periode_selesai'] }}">
                                            <input type="hidden" name="metode" value="{{ $batch['metode_penggajian'] }}">
                                            <input type="hidden" name="aksi" value="approve">
                                            <button type="submit" class="inst-btn-secondary !h-8 !px-3 !py-1 text-xs inline-flex items-center gap-1">
                                                <i data-lucide="check-circle-2" class="h-3.5 w-3.5"></i>
                                                Setujui batch
                                            </button>
                                        </form>
                                    @elseif ($batch['status'] === 'approved' && $u->hasRole('super_admin'))
                                        <form method="post" action="{{ route('penggajian.batch-status') }}" class="flex flex-col items-end gap-1">
                                            @csrf
                                            <input type="hidden" name="mulai" value="{{ $batch['periode_mulai'] }}">
                                            <input type="hidden" name="selesai" value="{{ $batch['periode_selesai'] }}">
                                            <input type="hidden" name="metode" value="{{ $batch['metode_penggajian'] }}">
                                            <input type="hidden" name="aksi" value="bayar">
                                            <input type="date" name="tanggal_bayar" value="{{ now()->toDateString() }}" class="inst-input !h-8 !px-2 !py-1 text-xs">
                                            <button type="submit" class="inst-btn-secondary !h-8 !px-3 !py-1 text-xs inline-flex items-center gap-1">
                                                <i data-lucide="wallet" class="h-3.5 w-3.5"></i>
                                                Bayar batch
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('penggajian.cetak-kwitansi-batch', ['mulai' => $batch['periode_mulai'], 'selesai' => $batch['periode_selesai'], 'metode' => $batch['metode_penggajian']]) }}"
                                        target="_blank" class="inst-btn-secondary !h-8 !px-3 !py-1 text-xs inline-flex items-center gap-1">
                                        <i data-lucide="printer" class="h-3.5 w-3.5"></i>
                                        Print kwitansi
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="inst-td-muted py-8 text-center">Belum ada batch penggajian.</td>
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
