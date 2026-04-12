@extends('layouts.app')

@section('title', 'Ubah Barang Keluar')

@section('content')
    <div class="inst-form-page" style="max-width:48rem;">
        <a href="{{ route('stok.keluar.index') }}" class="inst-back">← Kembali</a>
        <h2 class="inst-form-title">Ubah barang keluar</h2>
        <p class="inst-form-lead font-mono text-sm" style="color:#4a6b7f;">{{ $keluar->kode_transaksi }}</p>
        @php($stokTersediaEdit = $keluar->barang ? $keluar->barang->getStokSaatIni($profilId, $periodeId) + (float) $keluar->jumlah : 0)
        <div class="inst-form-card">
            <form method="POST" action="{{ route('stok.keluar.update', $keluar) }}" class="space-y-5" id="form-keluar">
                @csrf
                @method('PUT')
                <input type="hidden" name="profil_mbg_id" value="{{ $profilId }}">
                <input type="hidden" id="stok-tersedia" value="{{ $stokTersediaEdit }}">

                <div>
                    <label class="inst-label">Barang</label>
                    <input type="text" class="inst-input bg-slate-50" readonly value="{{ $keluar->barang?->kode_barang }} — {{ $keluar->barang?->nama_barang }}" style="background:#f0f6fb;">
                </div>

                <div class="rounded-lg border p-3 text-sm" style="border-color:#d4e8f4;background:#f8fbfd;">
                    <span class="inst-label text-xs">Stok tersedia (jika transaksi ini dihapus dari perhitungan)</span>
                    <p id="info-stok" class="mt-1 font-mono font-semibold" style="color:#1a4a6b;">{{ number_format($stokTersediaEdit, 2, ',', '.') }}</p>
                </div>

                <div>
                    <label for="tanggal" class="inst-label">Tanggal <span class="inst-required">*</span></label>
                    <input type="date" name="tanggal" id="tanggal" class="inst-input" required value="{{ old('tanggal', $keluar->tanggal->format('Y-m-d')) }}">
                </div>

                <div>
                    <label for="jumlah" class="inst-label">Jumlah <span class="inst-required">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="jumlah" id="jumlah" class="inst-input font-mono" required value="{{ old('jumlah', $keluar->jumlah) }}">
                    <p id="jumlah-error" class="mt-1 hidden text-xs font-semibold" style="color:#c0392b;">Jumlah melebihi stok tersedia.</p>
                </div>

                <div>
                    <label for="satuan" class="inst-label">Satuan <span class="inst-required">*</span></label>
                    <input type="text" name="satuan" id="satuan" class="inst-input" required value="{{ old('satuan', $keluar->satuan) }}">
                </div>

                <div>
                    <label for="tujuan_penggunaan" class="inst-label">Tujuan <span class="inst-required">*</span></label>
                    <select name="tujuan_penggunaan" id="tujuan_penggunaan" class="inst-select" required>
                        @foreach (\App\Enums\BarangKeluarTujuan::cases() as $t)
                            <option value="{{ $t->value }}" @selected(old('tujuan_penggunaan', $keluar->tujuan_penggunaan?->value) === $t->value)>{{ $t->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="keterangan" class="inst-label">Keterangan</label>
                    <textarea name="keterangan" id="keterangan" rows="2" class="inst-input" maxlength="5000">{{ old('keterangan', $keluar->keterangan) }}</textarea>
                </div>

                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit" class="inst-btn-primary" id="btn-submit-keluar">Simpan</button>
                    <a href="{{ route('stok.keluar.show', $keluar) }}" class="inst-btn-outline">Detail</a>
                    <a href="{{ route('stok.keluar.index') }}" class="inst-btn-outline">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const stokTersedia = parseFloat(document.getElementById('stok-tersedia')?.value || '0');
            function validateJumlah() {
                const j = parseFloat(document.getElementById('jumlah')?.value || '0');
                const err = document.getElementById('jumlah-error');
                const btn = document.getElementById('btn-submit-keluar');
                const bad = j > stokTersedia + 1e-9;
                err?.classList.toggle('hidden', !bad);
                if (btn) btn.disabled = bad || !j;
            }
            document.addEventListener('DOMContentLoaded', function () {
                document.getElementById('jumlah')?.addEventListener('input', validateJumlah);
                validateJumlah();
            });
        })();
    </script>
@endpush
