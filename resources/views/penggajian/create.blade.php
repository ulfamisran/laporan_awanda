@extends('layouts.app')

@section('title', 'Generate Penggajian')

@section('content')
    <div class="mb-6">
        <h2 class="inst-page-title">Generate penggajian</h2>
        <p class="inst-page-desc">Tentukan periode tanggal, lalu lakukan penggajian untuk satu atau banyak relawan.</p>
    </div>
    @include('components.periode-aktif-badge')

    <div class="inst-panel mb-6 p-6">
        <h3 class="mb-4 text-sm font-bold uppercase tracking-wide" style="color:#1a4a6b;">Filter &amp; pratinjau</h3>
        <form method="get" action="{{ route('penggajian.create') }}" class="space-y-4">
            <div class="flex flex-wrap gap-4">
                <div>
                    <label for="c-mulai" class="inst-label">Periode mulai</label>
                    <input id="c-mulai" type="date" name="mulai" value="{{ $mulai }}" class="inst-input mt-1 w-52" required>
                </div>
                <div>
                    <label for="c-selesai" class="inst-label">Periode selesai</label>
                    <input id="c-selesai" type="date" name="selesai" value="{{ $selesai }}" class="inst-input mt-1 w-52" required>
                </div>
                <div>
                    <label for="c-metode" class="inst-label">Metode penggajian</label>
                    <select id="c-metode" name="metode_penggajian" class="inst-select mt-1 w-56" required>
                        <option value="gaji_pokok" @selected($metode === 'gaji_pokok')>Berdasarkan gaji pokok</option>
                        <option value="kehadiran" @selected($metode === 'kehadiran')>Berdasarkan kehadiran</option>
                    </select>
                </div>
                <div>
                    <label for="c-status-create" class="inst-label">Status saat dibuat</label>
                    <select id="c-status-create" name="status_create" class="inst-select mt-1 w-56">
                        <option value="draft" @selected($statusCreate === 'draft')>Draft</option>
                        <option value="approved" @selected($statusCreate === 'approved')>Disetujui</option>
                        <option value="dibayar" @selected($statusCreate === 'dibayar')>Dibayar</option>
                    </select>
                </div>
                <div id="wrap-c-tgl-bayar" @class(['hidden' => $statusCreate !== 'dibayar'])>
                    <label for="c-tanggal-bayar-create" class="inst-label">Tanggal bayar</label>
                    <input id="c-tanggal-bayar-create" type="date" name="tanggal_bayar_create" value="{{ $tanggalBayarCreate }}" class="inst-input mt-1 w-52">
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

        <form method="post" action="{{ route('penggajian.generate-bulk') }}" class="inst-panel mb-6 overflow-hidden p-6">
            @csrf
            <input type="hidden" name="periode_mulai" value="{{ $mulai }}">
            <input type="hidden" name="periode_selesai" value="{{ $selesai }}">
            <input type="hidden" name="metode_penggajian" value="{{ $metode }}">
            <input type="hidden" name="status_create" value="{{ $statusCreate }}">
            <input type="hidden" name="tanggal_bayar_create" value="{{ $tanggalBayarCreate }}">
            <h3 class="mb-4 text-sm font-bold uppercase tracking-wide" style="color:#1a4a6b;">Pratinjau relawan aktif ({{ $previewRelawans->count() }})</h3>
            <div class="overflow-x-auto">
                <table class="inst-table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Posisi</th>
                            @if ($metode === 'kehadiran')
                                <th class="text-right">Gaji per hari</th>
                                <th class="text-right">Jumlah hadir</th>
                            @else
                                <th class="text-right">Gaji pokok</th>
                            @endif
                            <th>Status periode</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($previewRelawans as $r)
                            @php $sudah = $existingRelawanIds->contains($r->getKey()); @endphp
                            <tr>
                                <td>{{ $r->nama_lengkap }}</td>
                                <td class="inst-td-muted">{{ $r->posisiRelawan?->nama_posisi ?? '—' }}</td>
                                @if ($metode === 'kehadiran')
                                    <td class="text-right font-mono">{{ formatRupiah($r->gaji_per_hari) }}</td>
                                    <td class="text-right">
                                        <input type="number" min="0" max="31" name="jumlah_hadir[{{ $r->id }}]" value="{{ old('jumlah_hadir.'.$r->id, $defaultJumlahHadir) }}"
                                            class="inst-input inline-block w-24 text-right" @disabled($sudah)>
                                    </td>
                                @else
                                    <td class="text-right font-mono">{{ formatRupiah($r->gaji_pokok) }}</td>
                                @endif
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
            <div class="mt-5">
                <button type="submit" class="inst-btn-primary">
                Generate penggajian bulk
                </button>
                <p class="mt-2 text-xs inst-td-muted">Isi jumlah hadir per relawan. Record duplikat (relawan + rentang tanggal) akan dilewati.</p>
            </div>
        </form>
    @elseif(request('preview'))
        <p class="inst-td-muted text-sm">Tidak ada relawan aktif untuk cabang ini.</p>
    @endif

    <div class="inst-panel mt-8 p-6">
        <h3 class="mb-4 text-sm font-bold uppercase tracking-wide" style="color:#1a4a6b;">Tambah satu relawan (draft)</h3>
        <form method="post" action="{{ route('penggajian.store') }}" class="grid gap-4 sm:max-w-xl">
            @csrf
            <input type="hidden" name="periode_mulai" value="{{ $mulai }}">
            <input type="hidden" name="periode_selesai" value="{{ $selesai }}">
            <input type="hidden" name="metode_penggajian" value="{{ $metode }}">
            <input type="hidden" name="status_create" value="{{ $statusCreate }}">
            <input type="hidden" name="tanggal_bayar_create" value="{{ $tanggalBayarCreate }}">
            <div>
                <label for="relawan_id" class="inst-label">Relawan</label>
                <select id="relawan_id" name="relawan_id" class="inst-select mt-1 w-full" required>
                    <option value="">— Pilih —</option>
                    @foreach (\App\Models\Relawan::query()->aktif()->byDapur($profilId)->orderBy('nama_lengkap')->get() as $r)
                        <option value="{{ $r->id }}">{{ $r->nama_lengkap }} — {{ $r->posisiRelawan?->nama_posisi }}</option>
                    @endforeach
                </select>
            </div>
            <div @class(['hidden' => $metode !== 'kehadiran'])>
                <label for="jumlah_hadir_single" class="inst-label">Jumlah hadir</label>
                <input type="number" min="0" max="31" step="1" name="jumlah_hadir" id="jumlah_hadir_single" value="{{ old('jumlah_hadir', $defaultJumlahHadir) }}" class="inst-input mt-1 w-full" @required($metode === 'kehadiran')>
            </div>
            <button type="submit" class="inst-btn-secondary w-fit">Simpan satu penggajian</button>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const statusEl = document.getElementById('c-status-create');
            const wrapTanggal = document.getElementById('wrap-c-tgl-bayar');
            const tglEl = document.getElementById('c-tanggal-bayar-create');
            function syncStatusCreate() {
                const isDibayar = statusEl && statusEl.value === 'dibayar';
                if (wrapTanggal) wrapTanggal.classList.toggle('hidden', !isDibayar);
                if (tglEl) tglEl.required = !!isDibayar;
            }
            if (statusEl) statusEl.addEventListener('change', syncStatusCreate);
            syncStatusCreate();
        })();
        if (window.lucide) lucide.createIcons();
    </script>
@endpush
