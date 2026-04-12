@extends('layouts.app')

@section('title', 'Ubah Barang Masuk')

@section('content')
    <div class="inst-form-page" style="max-width:48rem;">
        <a href="{{ route('stok.masuk.index') }}" class="inst-back">← Kembali</a>
        <h2 class="inst-form-title">Ubah barang masuk</h2>
        <p class="inst-form-lead text-sm" style="color:#4a6b7f;">Kode: <span class="font-mono font-semibold">{{ $masuk->kode_transaksi }}</span> (tidak dapat diubah)</p>
        <div class="inst-form-card">
            <form method="POST" action="{{ route('stok.masuk.update', $masuk) }}" enctype="multipart/form-data" class="space-y-5" id="form-masuk">
                @csrf
                @method('PUT')
                <input type="hidden" name="profil_mbg_id" value="{{ $profilId }}">

                <div>
                    <label class="inst-label">Barang</label>
                    <input type="text" class="inst-input bg-slate-50" readonly value="{{ $masuk->barang?->kode_barang }} — {{ $masuk->barang?->nama_barang }}" style="background:#f0f6fb;">
                </div>

                <div>
                    <label for="tanggal" class="inst-label">Tanggal <span class="inst-required">*</span></label>
                    <input type="date" name="tanggal" id="tanggal" class="inst-input" required value="{{ old('tanggal', $masuk->tanggal->format('Y-m-d')) }}">
                </div>

                <div>
                    <label for="jumlah" class="inst-label">Jumlah <span class="inst-required">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="jumlah" id="jumlah" class="inst-input font-mono" required value="{{ old('jumlah', $masuk->jumlah) }}">
                </div>

                <div>
                    <label for="satuan" class="inst-label">Satuan <span class="inst-required">*</span></label>
                    <input type="text" name="satuan" id="satuan" class="inst-input" required value="{{ old('satuan', $masuk->satuan) }}">
                </div>

                <div>
                    <label for="harga_satuan" class="inst-label">Harga satuan <span class="inst-required">*</span></label>
                    <input type="text" name="harga_satuan" id="harga_satuan" class="inst-input" required value="{{ old('harga_satuan', number_format((float) $masuk->harga_satuan, 0, ',', '.')) }}" inputmode="numeric">
                </div>

                <div>
                    <span class="inst-label">Total harga</span>
                    <p id="total_harga_preview" class="mt-1 text-lg font-bold" style="color:#2d7a60;"></p>
                </div>

                <div>
                    <label for="sumber" class="inst-label">Sumber <span class="inst-required">*</span></label>
                    <select name="sumber" id="sumber" class="inst-select" required>
                        @foreach (\App\Enums\BarangMasukSumber::cases() as $s)
                            <option value="{{ $s->value }}" @selected(old('sumber', $masuk->sumber?->value) === $s->value)>{{ $s->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="keterangan" class="inst-label">Keterangan</label>
                    <textarea name="keterangan" id="keterangan" rows="2" class="inst-input" maxlength="5000">{{ old('keterangan', $masuk->keterangan) }}</textarea>
                </div>

                <div>
                    <label for="gambar" class="inst-label">Gambar baru (opsional)</label>
                    @if ($masuk->gambar_url)
                        <p class="mb-2 text-xs" style="color:#7fa8c9;">Gambar saat ini:</p>
                        <img src="{{ $masuk->gambar_url }}" alt="" class="mb-3 max-h-40 rounded-lg border" style="border-color:#d4e8f4;">
                    @endif
                    <input type="file" name="gambar" id="gambar" accept="image/jpeg,image/png,image/webp" class="inst-input @error('gambar') border-red-500 @enderror">
                    @error('gambar')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit" class="inst-btn-primary">Simpan</button>
                    <a href="{{ route('stok.masuk.show', $masuk) }}" class="inst-btn-outline">Detail</a>
                    <a href="{{ route('stok.masuk.index') }}" class="inst-btn-outline">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            function formatRupiahDigits(raw) {
                const digits = String(raw || '').replace(/\D/g, '');
                if (!digits) return '';
                return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }
            function parseNum(v) {
                return parseFloat(String(v || '').replace(/\./g, '').replace(',', '.')) || 0;
            }
            function updateTotal() {
                const j = parseNum(document.getElementById('jumlah')?.value);
                const h = parseNum(document.getElementById('harga_satuan')?.value);
                const el = document.getElementById('total_harga_preview');
                if (el) el.textContent = 'Rp ' + Math.round(j * h).toLocaleString('id-ID');
            }
            document.addEventListener('DOMContentLoaded', function () {
                const harga = document.getElementById('harga_satuan');
                const jumlah = document.getElementById('jumlah');
                harga?.addEventListener('input', function () {
                    const c = harga.selectionStart;
                    const b = harga.value.length;
                    harga.value = formatRupiahDigits(harga.value);
                    try {
                        harga.setSelectionRange(c + (harga.value.length - b), c + (harga.value.length - b));
                    } catch (e) {}
                    updateTotal();
                });
                jumlah?.addEventListener('input', updateTotal);
                updateTotal();
            });
        })();
    </script>
@endpush
