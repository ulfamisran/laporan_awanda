@extends('layouts.app')

@section('title', 'Ubah Dana Masuk')

@section('content')
    <div class="inst-form-page" style="max-width:48rem;">
        <a href="{{ route('keuangan.masuk.index') }}" class="inst-back">← Kembali</a>
        <h2 class="inst-form-title">Ubah dana masuk</h2>
        <p class="inst-form-lead font-mono text-sm" style="color:#4a6b7f;">{{ $masuk->kode_transaksi }}</p>
        <div class="inst-form-card">
            <form method="POST" action="{{ route('keuangan.masuk.update', $masuk) }}" enctype="multipart/form-data" class="space-y-5" id="form-dana-masuk">
                @csrf
                @method('PUT')
                <input type="hidden" name="profil_mbg_id" value="{{ $profilId }}">
                <div>
                    <label for="akun_jenis_dana_id" class="inst-label">Jenis Buku Pembantu <span class="inst-required">*</span></label>
                    <p class="mb-1 text-xs" style="color:#7fa8c9;">Buku Pembantu Jenis Dana</p>
                    <select name="akun_jenis_dana_id" id="akun_jenis_dana_id" class="inst-select select2-keuangan" required>
                        @foreach ($akunJenisDana as $a)
                            <option value="{{ $a->id }}" @selected(old('akun_jenis_dana_id', $masuk->akun_jenis_dana_id) == $a->id)>{{ $a->kode }} — {{ $a->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="akun_kas_id" class="inst-label">Jenis Buku Kas <span class="inst-required">*</span></label>
                    <p class="mb-1 text-xs" style="color:#7fa8c9;">Buku Pembantu Kas</p>
                    <select name="akun_kas_id" id="akun_kas_id" class="inst-select select2-keuangan" required>
                        @foreach ($akunKas as $a)
                            <option value="{{ $a->id }}" @selected(old('akun_kas_id', $masuk->akun_kas_id) == $a->id)>{{ $a->kode }} — {{ $a->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="tanggal" class="inst-label">Tanggal <span class="inst-required">*</span></label>
                    <input type="date" name="tanggal" id="tanggal" class="inst-input" required value="{{ old('tanggal', $masuk->tanggal->format('Y-m-d')) }}">
                </div>
                <div>
                    <label for="jumlah_display" class="inst-label">Jumlah <span class="inst-required">*</span></label>
                    <input type="text" id="jumlah_display" class="inst-input font-mono" inputmode="numeric" autocomplete="off" value="{{ old('jumlah_display', 'Rp '.number_format((float) $masuk->jumlah, 0, ',', '.')) }}">
                    <input type="hidden" name="jumlah_angka" id="jumlah_angka" value="{{ old('jumlah_angka', $masuk->jumlah) }}">
                </div>
                <div>
                    <label for="nomor_bukti" class="inst-label">Nomor bukti <span class="inst-required">*</span></label>
                    <input type="text" name="nomor_bukti" id="nomor_bukti" class="inst-input font-mono" required value="{{ old('nomor_bukti', $masuk->nomor_bukti) }}" maxlength="64">
                </div>
                <div>
                    <label for="uraian_transaksi" class="inst-label">Uraian transaksi <span class="inst-required">*</span></label>
                    <textarea name="uraian_transaksi" id="uraian_transaksi" rows="3" class="inst-textarea" required maxlength="10000" placeholder="Ringkasan transaksi untuk laporan">{{ old('uraian_transaksi', $masuk->uraian_transaksi) }}</textarea>
                </div>
                <div>
                    <label for="sumber" class="inst-label">Sumber <span class="inst-required">*</span></label>
                    <input type="text" name="sumber" id="sumber" class="inst-input" required value="{{ old('sumber', $masuk->sumber) }}" maxlength="255">
                </div>
                <div>
                    <label for="keterangan" class="inst-label">Keterangan</label>
                    <textarea name="keterangan" id="keterangan" rows="2" class="inst-input" maxlength="5000">{{ old('keterangan', $masuk->keterangan) }}</textarea>
                </div>
                @if (count($masuk->gambar_nota ?? []) > 0)
                    <div>
                        <span class="inst-label">Nota saat ini</span>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ($masuk->gambarNotaUrls() as $url)
                                <a href="{{ $url }}" target="_blank" class="block"><img src="{{ $url }}" class="h-16 w-16 rounded border object-cover" style="border-color:#d4e8f4;" alt=""></a>
                            @endforeach
                        </div>
                    </div>
                @endif
                <div>
                    <label for="gambar_nota" class="inst-label">Tambah foto nota (opsional)</label>
                    <input type="file" name="gambar_nota[]" id="gambar_nota" accept="image/jpeg,image/png,image/webp" class="inst-input" multiple>
                </div>
                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit" class="inst-btn-primary">Simpan</button>
                    <a href="{{ route('keuangan.masuk.show', $masuk) }}" class="inst-btn-outline">Detail</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.jQuery && jQuery.fn.select2) {
                jQuery('.select2-keuangan').select2({ width: '100%', language: { noResults: () => 'Tidak ada hasil' } });
            }
            const disp = document.getElementById('jumlah_display');
            const hid = document.getElementById('jumlah_angka');
            function digitsOnly(v) { return String(v || '').replace(/\D/g, ''); }
            function formatRpDisplay(digits) {
                if (!digits) return '';
                return 'Rp ' + digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }
            disp?.addEventListener('input', function () {
                const d = digitsOnly(disp.value);
                disp.value = formatRpDisplay(d);
                if (hid) hid.value = d ? parseInt(d, 10) : '';
            });
            document.getElementById('form-dana-masuk')?.addEventListener('submit', function () {
                const d = digitsOnly(document.getElementById('jumlah_display')?.value || '');
                if (hid) hid.value = d || '0';
            });
        });
    </script>
@endpush
