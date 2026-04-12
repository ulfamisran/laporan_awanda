@extends('layouts.app')

@section('title', 'Tambah Dana Masuk')

@section('content')
    <div class="inst-form-page" style="max-width:48rem;">
        <a href="{{ route('keuangan.masuk.index') }}" class="inst-back">← Kembali</a>
        <h2 class="inst-form-title">Tambah dana masuk</h2>
        <p class="inst-form-lead text-sm" style="color:#4a6b7f;">Kode: <span class="font-mono font-semibold">{{ $previewKode }}</span> (otomatis)</p>
        <div class="inst-form-card">
            <form method="POST" action="{{ route('keuangan.masuk.store') }}" enctype="multipart/form-data" class="space-y-5" id="form-dana-masuk">
                @csrf
                <input type="hidden" name="profil_mbg_id" id="profil_mbg_hidden" value="{{ $profilId }}">

                <div>
                    <label for="akun_jenis_dana_id" class="inst-label">Jenis Buku Pembantu <span class="inst-required">*</span></label>
                    <p class="mb-1 text-xs" style="color:#7fa8c9;">Buku Pembantu Jenis Dana</p>
                    <select name="akun_jenis_dana_id" id="akun_jenis_dana_id" class="inst-select select2-keuangan" required>
                        <option value="">Pilih…</option>
                        @foreach ($akunJenisDana as $a)
                            <option value="{{ $a->id }}" @selected(old('akun_jenis_dana_id') == $a->id)>{{ $a->kode }} — {{ $a->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="akun_kas_id" class="inst-label">Jenis Buku Kas <span class="inst-required">*</span></label>
                    <p class="mb-1 text-xs" style="color:#7fa8c9;">Buku Pembantu Kas</p>
                    <select name="akun_kas_id" id="akun_kas_id" class="inst-select select2-keuangan" required>
                        <option value="">Pilih…</option>
                        @foreach ($akunKas as $a)
                            <option value="{{ $a->id }}" @selected(old('akun_kas_id') == $a->id)>{{ $a->kode }} — {{ $a->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="tanggal" class="inst-label">Tanggal <span class="inst-required">*</span></label>
                    <input type="date" name="tanggal" id="tanggal" class="inst-input" required value="{{ old('tanggal', now()->toDateString()) }}">
                </div>

                <div>
                    <label for="jumlah_display" class="inst-label">Jumlah <span class="inst-required">*</span></label>
                    <input type="text" id="jumlah_display" class="inst-input font-mono" inputmode="numeric" autocomplete="off" placeholder="Rp 0">
                    <input type="hidden" name="jumlah_angka" id="jumlah_angka" value="{{ old('jumlah_angka') }}">
                </div>

                <div>
                    <label for="nomor_bukti" class="inst-label">Nomor bukti <span class="inst-required">*</span></label>
                    <input type="text" name="nomor_bukti" id="nomor_bukti" class="inst-input font-mono" required value="{{ old('nomor_bukti') }}" maxlength="64" placeholder="No. kwitansi / nota">
                </div>

                <div>
                    <label for="uraian_transaksi" class="inst-label">Uraian transaksi <span class="inst-required">*</span></label>
                    <textarea name="uraian_transaksi" id="uraian_transaksi" rows="3" class="inst-textarea" required maxlength="10000" placeholder="Ringkasan transaksi untuk laporan">{{ old('uraian_transaksi') }}</textarea>
                </div>

                <div>
                    <label for="sumber" class="inst-label">Sumber / donatur <span class="inst-required">*</span></label>
                    <input type="text" name="sumber" id="sumber" class="inst-input" required value="{{ old('sumber') }}" maxlength="255">
                </div>

                <div>
                    <label for="keterangan" class="inst-label">Keterangan</label>
                    <textarea name="keterangan" id="keterangan" rows="2" class="inst-input" maxlength="5000">{{ old('keterangan') }}</textarea>
                </div>

                <div>
                    <label for="gambar_nota" class="inst-label">Foto nota (jpg/png/webp, maks. 5MB per file, boleh banyak)</label>
                    <input type="file" name="gambar_nota[]" id="gambar_nota" accept="image/jpeg,image/png,image/webp" class="inst-input" multiple>
                    <div id="preview-nota" class="mt-2 flex flex-wrap gap-2"></div>
                </div>

                <div class="rounded-lg border p-3 text-sm" style="border-color:#d4e8f4;background:#f8fbfd;">
                    <span class="inst-label text-xs">Saldo saat ini</span>
                    <p id="saldo-sekarang" class="mt-1 font-mono font-semibold" style="color:#1a4a6b;">{{ formatRupiah($saldoSaatIni) }}</p>
                    <span class="inst-label mt-2 block text-xs">Estimasi saldo setelah transaksi</span>
                    <p id="saldo-setelah" class="mt-1 font-mono font-semibold" style="color:#2d7a60;">{{ formatRupiah($saldoSaatIni) }}</p>
                </div>

                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit" class="inst-btn-primary" id="btn-simpan-masuk">Simpan</button>
                    <a href="{{ route('keuangan.masuk.index') }}" class="inst-btn-outline">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            let saldoBase = {{ (float) $saldoSaatIni }};
            const apiSaldo = @json(route('keuangan.api.saldo'));
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            function digitsOnly(v) {
                return String(v || '').replace(/\D/g, '');
            }
            function formatRpDisplay(digits) {
                if (!digits) return '';
                return 'Rp ' + digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }
            function parseJumlah() {
                return parseFloat(document.getElementById('jumlah_angka')?.value || '0') || 0;
            }
            function refreshSaldoSetelah() {
                const j = parseJumlah();
                const el = document.getElementById('saldo-setelah');
                if (el) el.textContent = 'Rp ' + Math.round(saldoBase + j).toLocaleString('id-ID');
            }
            function profilParam() {
                return '';
            }
            function fetchSaldo() {
                fetch(apiSaldo + profilParam(), { headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf } })
                    .then((r) => r.json())
                    .then((d) => {
                        saldoBase = parseFloat(d.saldo_saat_ini) || 0;
                        const el = document.getElementById('saldo-sekarang');
                        if (el && d.saldo_format) el.textContent = d.saldo_format;
                        refreshSaldoSetelah();
                    })
                    .catch(() => {});
            }

            document.addEventListener('DOMContentLoaded', function () {
                if (window.jQuery && jQuery.fn.select2) {
                    jQuery('.select2-keuangan').select2({ width: '100%', language: { noResults: () => 'Tidak ada hasil' } });
                }
                const disp = document.getElementById('jumlah_display');
                const hid = document.getElementById('jumlah_angka');
                disp?.addEventListener('input', function () {
                    const d = digitsOnly(disp.value);
                    disp.value = formatRpDisplay(d);
                    if (hid) hid.value = d ? (parseInt(d, 10) || 0) : '';
                    refreshSaldoSetelah();
                });
                const inp = document.getElementById('gambar_nota');
                const prev = document.getElementById('preview-nota');
                inp?.addEventListener('change', function () {
                    if (!prev) return;
                    prev.innerHTML = '';
                    Array.from(inp.files || []).forEach((f) => {
                        if (!f.type.startsWith('image/')) return;
                        const r = new FileReader();
                        r.onload = () => {
                            const img = document.createElement('img');
                            img.src = r.result;
                            img.className = 'h-16 w-16 rounded border object-cover';
                            img.style.borderColor = '#d4e8f4';
                            prev.appendChild(img);
                        };
                        r.readAsDataURL(f);
                    });
                });
                document.getElementById('form-dana-masuk')?.addEventListener('submit', function () {
                    const d = digitsOnly(document.getElementById('jumlah_display')?.value || '');
                    if (document.getElementById('jumlah_angka')) document.getElementById('jumlah_angka').value = d || '0';
                });
                fetchSaldo();
                refreshSaldoSetelah();
            });
        })();
    </script>
@endpush
