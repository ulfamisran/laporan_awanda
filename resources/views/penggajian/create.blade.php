@extends('layouts.app')

@section('title', 'Generate Penggajian')

@section('content')
    <div class="mb-6">
        <h2 class="inst-page-title">Generate penggajian</h2>
        <p class="inst-page-desc">Pilih periode, pratinjau daftar relawan aktif, lalu generate bulk (satu record per relawan per bulan).</p>
    </div>

    <div class="inst-panel mb-6 p-6">
        <h3 class="mb-4 text-sm font-bold uppercase tracking-wide" style="color:#1a4a6b;">Filter &amp; pratinjau</h3>
        <form method="get" action="{{ route('penggajian.create') }}" class="space-y-4">
            <div class="flex flex-wrap gap-4">
                <div>
                    <label for="c-bulan" class="inst-label">Bulan</label>
                    <select id="c-bulan" name="bulan" class="inst-select mt-1 w-44" required>
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" @selected((int) $bulan === $m)>
                                {{ ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][$m] }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label for="c-tahun" class="inst-label">Tahun</label>
                    <input id="c-tahun" type="number" name="tahun" value="{{ $tahun }}" min="2000" max="2100" class="inst-input mt-1 w-32" required>
                </div>
            </div>
            <input type="hidden" name="preview" value="1">
            <button type="submit" class="inst-btn-secondary">Tampilkan pratinjau</button>
        </form>
    </div>

    @if ($previewRelawans->isNotEmpty())
        @php
            $adaExisting = $existingRelawanIds->isNotEmpty();
        @endphp
        @if ($adaExisting)
            <div class="mb-4 rounded-xl border px-4 py-3 text-sm" style="border-color:#fcd34d;background:#fffbeb;color:#92400e;">
                <strong>Peringatan:</strong> {{ $existingRelawanIds->count() }} relawan sudah memiliki penggajian untuk periode ini dan akan dilewati saat generate.
            </div>
        @endif

        <div class="inst-panel mb-6 overflow-hidden p-6">
            <h3 class="mb-4 text-sm font-bold uppercase tracking-wide" style="color:#1a4a6b;">Pratinjau relawan aktif ({{ $previewRelawans->count() }})</h3>
            <div class="overflow-x-auto">
                <table class="inst-table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Posisi</th>
                            <th class="text-right">Gaji pokok</th>
                            <th>Status periode</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($previewRelawans as $r)
                            @php $sudah = $existingRelawanIds->contains($r->getKey()); @endphp
                            <tr>
                                <td>{{ $r->nama_lengkap }}</td>
                                <td class="inst-td-muted">{{ $r->posisiRelawan?->nama_posisi ?? '—' }}</td>
                                <td class="text-right font-mono">{{ formatRupiah($r->gaji_pokok) }}</td>
                                <td>
                                    @if ($sudah)
                                        <span class="text-xs font-semibold" style="color:#c0392b;">Sudah ada</span>
                                    @else
                                        <span class="text-xs font-semibold" style="color:#2d7a60;">Akan dibuat (draft)</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <form method="post" action="{{ route('penggajian.generate-bulk') }}" class="inst-panel p-6">
            @csrf
            <input type="hidden" name="periode_bulan" value="{{ $bulan }}">
            <input type="hidden" name="periode_tahun" value="{{ $tahun }}">
            <button type="submit" class="inst-btn-primary">
                Generate penggajian bulk
            </button>
            <p class="mt-2 text-xs inst-td-muted">Record duplikat (relawan + bulan + tahun) tidak akan dibuat ulang.</p>
        </form>
    @elseif(request('preview'))
        <p class="inst-td-muted text-sm">Tidak ada relawan aktif untuk cabang ini.</p>
    @endif

    <div class="inst-panel mt-8 p-6">
        <h3 class="mb-4 text-sm font-bold uppercase tracking-wide" style="color:#1a4a6b;">Tambah satu relawan (draft)</h3>
        <form method="post" action="{{ route('penggajian.store') }}" class="grid gap-4 sm:max-w-xl">
            @csrf
            <input type="hidden" name="periode_bulan" value="{{ $bulan }}">
            <input type="hidden" name="periode_tahun" value="{{ $tahun }}">
            <div>
                <label for="relawan_id" class="inst-label">Relawan</label>
                <select id="relawan_id" name="relawan_id" class="inst-select mt-1 w-full" required>
                    <option value="">— Pilih —</option>
                    @foreach (\App\Models\Relawan::query()->aktif()->byDapur($profilId)->orderBy('nama_lengkap')->get() as $r)
                        <option value="{{ $r->id }}">{{ $r->nama_lengkap }} — {{ $r->posisiRelawan?->nama_posisi }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="inst-btn-secondary w-fit">Simpan satu penggajian</button>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        if (window.lucide) lucide.createIcons();
    </script>
@endpush
