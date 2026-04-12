@extends('layouts.app')

@section('title', 'Saldo Dana Awal')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="inst-page-title">Saldo dana awal</h2>
            <p class="inst-page-desc">Input saldo awal per akun (kolom kuning pada contoh buku kas). Saldo akhir baris <span class="font-mono">1000</span> mengikuti total saldo kas setelah transaksi; grup lain menjumlahkan anak-anaknya.</p>
        </div>
        @if (auth()->user()->hasAnyRole(['super_admin', 'admin_pusat']))
            <a href="{{ route('master.akun-dana.index') }}" class="inst-btn-outline shrink-0 text-sm">Master akun dana</a>
        @endif
    </div>

    @if ($akunRows->isEmpty())
        <div class="inst-panel p-8 text-center text-sm" style="color:#7fa8c9;">
            Belum ada master akun dana. Super Admin / Admin Pusat dapat menambahnya di menu <strong>Data Master → Akun Dana</strong>, atau jalankan <span class="font-mono">php artisan db:seed --class=AkunDanaSeeder</span>.
        </div>
    @else
    <div class="inst-panel mb-6 overflow-hidden p-4 sm:p-6">
        <form method="POST" action="{{ $stok ? route('keuangan.stok-dana-awal.update', $stok) : route('keuangan.stok-dana-awal.store') }}" class="space-y-5">
            @csrf
            @if ($stok)
                @method('PUT')
            @endif
            <input type="hidden" name="profil_mbg_id" value="{{ $profilId }}">

            <div class="grid max-w-2xl grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="tanggal" class="inst-label">Tanggal <span class="inst-required">*</span></label>
                    <input type="date" name="tanggal" id="tanggal" class="inst-input" required value="{{ old('tanggal', $stok?->tanggal?->format('Y-m-d') ?? now()->toDateString()) }}">
                    @error('tanggal')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="keterangan" class="inst-label">Keterangan</label>
                    <input type="text" name="keterangan" id="keterangan" class="inst-input" maxlength="5000" value="{{ old('keterangan', $stok?->keterangan) }}" placeholder="Opsional">
                </div>
            </div>

            @error('saldo')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror

            <div class="overflow-x-auto rounded-lg border" style="border-color:#d4e8f4;">
                <table class="inst-table min-w-[52rem] text-sm">
                    <thead>
                        <tr style="background:#4a5568;color:#fff;">
                            <th class="font-semibold">Kode</th>
                            <th class="font-semibold">Nama akun</th>
                            <th class="text-right font-semibold">Saldo awal</th>
                            <th class="text-right font-semibold">Saldo akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($akunRows as $row)
                            @php($ak = $row['akun'])
                            @php($depth = (int) $row['depth'])
                            @php($sAwal = \App\Http\Controllers\StokDanaAwalController::agregatSaldoAwal($ak, $allAkun, $saldoByAkunId))
                            @php($sAkhir = $saldoAkhirById[$ak->id] ?? 0)
                            <tr class="{{ $ak->is_grup ? 'font-bold' : '' }}" style="{{ $ak->is_grup ? 'background:#eef2f7;' : '' }}">
                                <td class="font-mono">{{ $ak->kode }}</td>
                                <td style="padding-left:{{ 8 + $depth * 18 }}px;">{{ $ak->nama }}</td>
                                <td class="text-right">
                                    @if ($ak->is_grup)
                                        <span class="font-mono">{{ formatRupiah($sAwal) }}</span>
                                    @else
                                        <input type="text" inputmode="numeric" name="saldo[{{ $ak->id }}]" class="inst-input w-full max-w-[11rem] text-right font-mono saldo-rupiah-input" style="background:#fffbea;border-color:#f5e6a3;" value="{{ old('saldo.'.$ak->id, isset($saldoByAkunId[$ak->id]) ? number_format((float) $saldoByAkunId[$ak->id], 0, ',', '.') : '') }}" placeholder="0" autocomplete="off">
                                        @error('saldo.'.$ak->id)
                                            <span class="mt-1 block text-xs text-red-600">{{ $message }}</span>
                                        @enderror
                                    @endif
                                </td>
                                <td class="text-right font-mono">{{ formatRupiah($sAkhir) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p class="text-xs" style="color:#7fa8c9;">Total saldo kas (untuk validasi dana masuk/keluar): <strong class="font-mono">{{ formatRupiah($saldoGlobal) }}</strong></p>

            <div class="flex flex-wrap gap-2">
                <button type="submit" class="inst-btn-primary">{{ $stok ? 'Simpan perubahan' : 'Simpan saldo awal' }}</button>
            </div>

            @if ($stok)
                <p class="text-xs" style="color:#7fa8c9;">Terakhir diperbarui: {{ $stok->updated_at->translatedFormat('d M Y H:i') }} — {{ $stok->creator?->name ?? '—' }}</p>
            @endif
        </form>
    </div>
    @endif

    <div class="inst-panel overflow-hidden p-4 sm:p-6">
        <h3 class="mb-3 text-sm font-bold uppercase tracking-wide" style="color:#7fa8c9;">Aktivitas keuangan terbaru</h3>
        <div class="overflow-x-auto">
            <table class="inst-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jenis</th>
                        <th>Kode</th>
                        <th>Label</th>
                        <th>Uraian transaksi</th>
                        <th class="text-right">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($aktivitasTerbaru as $a)
                        <tr>
                            <td>{{ $a['tanggal']->format('d/m/Y') }}</td>
                            <td>
                                @if ($a['jenis'] === 'masuk')
                                    <span class="text-xs font-semibold text-emerald-700">Masuk</span>
                                @else
                                    <span class="text-xs font-semibold text-rose-700">Keluar</span>
                                @endif
                            </td>
                            <td class="font-mono text-xs">{{ $a['kode'] }}</td>
                            <td>{{ $a['label'] }}</td>
                            <td class="max-w-xs text-xs" style="color:#4a6b7f;">{{ ($a['uraian'] ?? '') !== '' ? \Illuminate\Support\Str::limit($a['uraian'], 160) : '—' }}</td>
                            <td class="text-right font-mono">{{ formatRupiah($a['jumlah']) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-sm" style="color:#7fa8c9;">Belum ada transaksi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            function digitsOnly(v) {
                return String(v || '').replace(/\D+/g, '');
            }
            function formatRupiahDigits(raw) {
                const d = digitsOnly(raw);
                if (!d) return '';
                return d.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }
            document.querySelectorAll('.saldo-rupiah-input').forEach(function (inp) {
                inp.addEventListener('input', function () {
                    const c = inp.selectionStart;
                    const before = inp.value.length;
                    inp.value = formatRupiahDigits(inp.value);
                    const after = inp.value.length;
                    if (c != null) inp.setSelectionRange(c + (after - before), c + (after - before));
                });
            });
        })();
    </script>
@endpush
