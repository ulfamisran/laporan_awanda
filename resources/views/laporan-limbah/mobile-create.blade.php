@extends('layouts.app')

@section('title', 'Input Limbah Mobile')

@section('content')
    @include('components.mobile-expand-accordion')

    <div class="mx-auto w-full max-w-2xl px-3 pb-20 sm:px-4">
        <a href="{{ route('laporan-limbah.index') }}" class="inst-back">← Kembali</a>
        <h2 class="inst-form-title mt-2">Input Laporan Limbah</h2>
        <p class="inst-form-lead text-sm" style="color:#4a6b7f;">Halaman khusus input via HP. Ketuk kategori limbah untuk isi data; hanya satu kategori terbuka sekaligus.</p>
        @include('components.periode-aktif-badge')

        <form method="POST" action="{{ route('laporan-limbah.store') }}" enctype="multipart/form-data" class="mt-4 space-y-4" id="form-limbah-mobile">
            @csrf
            <input type="hidden" name="profil_mbg_id" value="{{ $profilId }}">
            <input type="hidden" name="mobile" value="1">

            <div class="rounded-2xl border bg-white p-4 shadow-sm" style="border-color:#d4e8f4;">
                <div>
                    <label for="tanggal" class="inst-label">Tanggal</label>
                    <input type="date" name="tanggal" id="tanggal" class="inst-input mt-1" required value="{{ old('tanggal', now()->toDateString()) }}">
                    @error('tanggal')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mt-4">
                    <label for="menu_makanan" class="inst-label">Menu makanan</label>
                    <textarea name="menu_makanan" id="menu_makanan" rows="3" class="inst-input mt-1" maxlength="1000" required placeholder="Contoh: Nasi, ayam, sayur, buah">{{ old('menu_makanan') }}</textarea>
                    @error('menu_makanan')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            @error('kategori')
                <p class="text-xs text-red-600">{{ $message }}</p>
            @enderror

            <div class="space-y-3" id="limbah-sections-mobile">
                @foreach ($kategoris as $k)
                    @php $kid = $k->id; @endphp
                    <div class="mbg-expand-item" data-expand-item>
                        <div data-expand-header role="button" tabindex="0" aria-expanded="false">
                            <button type="button" class="mbg-expand-trigger">
                                <span class="min-w-0 flex-1">
                                    <span class="mbg-expand-title">{{ $k->nama_kategori }}</span>
                                    <span class="mbg-expand-summary" data-summary-for="jumlah_{{ $kid }}">Belum diisi</span>
                                </span>
                                <svg class="mbg-expand-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                            </button>
                        </div>
                        <div class="mbg-expand-body" data-expand-body hidden>
                            <div class="space-y-4">
                                <div>
                                    <label class="inst-label" for="jumlah_{{ $kid }}">Jumlah</label>
                                    <input type="number" step="0.01" min="0" name="kategori[{{ $kid }}][jumlah]" id="jumlah_{{ $kid }}" class="inst-input mt-1 font-mono summary-source" value="{{ old("kategori.$kid.jumlah") }}" placeholder="0">
                                    @error("kategori.$kid.jumlah")
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="inst-label" for="satuan_{{ $kid }}">Satuan</label>
                                    <select name="kategori[{{ $kid }}][satuan]" id="satuan_{{ $kid }}" class="inst-select mt-1 w-full">
                                        @foreach (\App\Enums\SatuanLimbah::cases() as $s)
                                            <option value="{{ $s->value }}" @selected(old("kategori.$kid.satuan", 'kg') === $s->value)>{{ $s->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="inst-label" for="gambar_{{ $kid }}">Foto limbah</label>
                                    <input type="file" name="kategori[{{ $kid }}][gambar]" id="gambar_{{ $kid }}" accept="image/jpeg,image/png,image/webp" capture="environment" class="inst-input mt-1">
                                    @error("kategori.$kid.gambar")
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="inst-label" for="ket_{{ $kid }}">Keterangan</label>
                                    <textarea name="kategori[{{ $kid }}][keterangan]" id="ket_{{ $kid }}" rows="2" class="inst-input mt-1" maxlength="5000" placeholder="Opsional">{{ old("kategori.$kid.keterangan") }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="sticky bottom-3 z-10 rounded-2xl border bg-white/95 p-3 shadow-lg backdrop-blur" style="border-color:#d4e8f4;">
                <button type="submit" class="inst-btn-primary w-full">Simpan laporan limbah</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const container = document.getElementById('limbah-sections-mobile');
            window.MbgMobileAccordion.initGroup(container, { expandFirst: true });

            container.querySelectorAll('[data-expand-item]').forEach(function (item) {
                const jumlahInput = item.querySelector('.summary-source');
                const summaryEl = item.querySelector('.mbg-expand-summary');
                if (!jumlahInput || !summaryEl) return;

                const satuanSelect = item.querySelector('select[name*="[satuan]"]');
                const update = function () {
                    const jumlah = String(jumlahInput.value || '').trim();
                    const satuan = satuanSelect ? satuanSelect.options[satuanSelect.selectedIndex]?.text : '';
                    summaryEl.textContent = jumlah !== '' ? jumlah + ' ' + satuan : 'Belum diisi';
                };
                jumlahInput.addEventListener('input', update);
                jumlahInput.addEventListener('change', update);
                satuanSelect?.addEventListener('change', update);
                update();
            });
        })();
    </script>
@endpush
