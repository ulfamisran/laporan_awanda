@extends('layouts.app')

@section('title', 'Tambah Barang Keluar')

@section('content')
    <div class="inst-form-page" style="max-width:48rem;">
        <a href="{{ route('stok.keluar.index') }}" class="inst-back">← Kembali</a>
        <h2 class="inst-form-title">Tambah barang keluar</h2>
        <p class="inst-form-lead text-sm" style="color:#4a6b7f;">Kode: <span class="font-mono font-semibold">{{ $previewKode }}</span> (otomatis)</p>
        <div class="inst-form-card">
            <form method="POST" action="{{ route('stok.keluar.store') }}" class="space-y-5" id="form-keluar">
                @csrf
                <input type="hidden" name="profil_mbg_id" id="profil_mbg_hidden" value="{{ $profilId }}">

                <div>
                    <label for="barang_id" class="inst-label">Barang <span class="inst-required">*</span></label>
                    <select name="barang_id" id="barang_id" class="inst-select select2-stok" required>
                        <option value="">Pilih barang…</option>
                        @foreach ($barangs as $br)
                            @php($st = $br->getStokSaatIni($profilId, $periodeId))
                            <option value="{{ $br->id }}" data-stok="{{ $st }}">{{ $br->kode_barang }} — {{ $br->nama_barang }} (tersedia: {{ number_format($st, 2, ',', '.') }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="rounded-lg border p-3 text-sm" style="border-color:#d4e8f4;background:#f8fbfd;">
                    <span class="inst-label text-xs">Stok tersedia</span>
                    <p id="info-stok" class="mt-1 font-mono font-semibold" style="color:#1a4a6b;">—</p>
                </div>

                <div>
                    <label for="tanggal" class="inst-label">Tanggal <span class="inst-required">*</span></label>
                    <input type="date" name="tanggal" id="tanggal" class="inst-input" required value="{{ old('tanggal', now()->toDateString()) }}">
                </div>

                <div>
                    <label for="jumlah" class="inst-label">Jumlah keluar <span class="inst-required">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="jumlah" id="jumlah" class="inst-input font-mono" required value="{{ old('jumlah') }}">
                    <p id="jumlah-error" class="mt-1 hidden text-xs font-semibold" style="color:#c0392b;">Jumlah melebihi stok tersedia.</p>
                </div>

                <div>
                    <label for="satuan" class="inst-label">Satuan <span class="inst-required">*</span></label>
                    <input type="text" name="satuan" id="satuan" class="inst-input" required readonly value="{{ old('satuan') }}">
                </div>

                <div>
                    <label for="tujuan_penggunaan" class="inst-label">Tujuan penggunaan <span class="inst-required">*</span></label>
                    <select name="tujuan_penggunaan" id="tujuan_penggunaan" class="inst-select" required>
                        @foreach (\App\Enums\BarangKeluarTujuan::cases() as $t)
                            <option value="{{ $t->value }}" @selected(old('tujuan_penggunaan') === $t->value)>{{ $t->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="keterangan" class="inst-label">Keterangan</label>
                    <textarea name="keterangan" id="keterangan" rows="2" class="inst-input" maxlength="5000">{{ old('keterangan') }}</textarea>
                </div>

                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit" class="inst-btn-primary" id="btn-submit-keluar">Simpan</button>
                    <a href="{{ route('stok.keluar.index') }}" class="inst-btn-outline">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const apiTpl = @json(url('/stok/api/barang')).replace(/\/$/, '') + '/';
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            let stokTersedia = 0;

            function profilParam() {
                const hid = document.getElementById('profil_mbg_hidden');
                return hid ? 'profil_mbg_id=' + encodeURIComponent(hid.value) : '';
            }

            function loadBarang(id) {
                const sat = document.getElementById('satuan');
                const info = document.getElementById('info-stok');
                if (!id) {
                    if (sat) sat.value = '';
                    if (info) info.textContent = '—';
                    stokTersedia = 0;
                    return;
                }
                fetch(apiTpl + id + '?' + profilParam(), { headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf } })
                    .then((r) => r.json())
                    .then((d) => {
                        if (sat) sat.value = d.satuan || '';
                        stokTersedia = parseFloat(d.stok_saat_ini) || 0;
                        if (info) info.textContent = stokTersedia.toLocaleString('id-ID', { minimumFractionDigits: 2 });
                        validateJumlah();
                    })
                    .catch(() => {});
            }

            function validateJumlah() {
                const j = parseFloat(document.getElementById('jumlah')?.value || '0');
                const err = document.getElementById('jumlah-error');
                const btn = document.getElementById('btn-submit-keluar');
                const bad = j > stokTersedia + 1e-9;
                err?.classList.toggle('hidden', !bad);
                if (btn) btn.disabled = bad || !j;
            }

            function initSelect2() {
                if (!window.jQuery || !jQuery.fn.select2) return;
                jQuery('.select2-stok').each(function () {
                    if (!jQuery(this).data('select2')) jQuery(this).select2({ width: '100%', language: { noResults: () => 'Tidak ada hasil' } });
                });
            }

            document.addEventListener('DOMContentLoaded', function () {
                initSelect2();
                setTimeout(initSelect2, 120);
                jQuery('#barang_id').on('change', function () {
                    loadBarang(this.value);
                });
                document.getElementById('jumlah')?.addEventListener('input', validateJumlah);
                const bid = document.getElementById('barang_id')?.value;
                if (bid) loadBarang(bid);
            });
        })();
    </script>
@endpush
