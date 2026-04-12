@extends('layouts.app')

@section('title', 'Tambah Barang')

@section('content')
    @php($barangModel = new \App\Models\Barang())
    <div class="inst-form-page" style="max-width:48rem;">
        <a href="{{ route('master.barang.index') }}" class="inst-back">← Kembali ke daftar</a>
        <h2 class="inst-form-title">Tambah barang</h2>
        <p class="inst-form-lead">Lengkapi data master barang. Kode dihasilkan otomatis.</p>

        <div class="inst-form-card">
            <form method="POST" action="{{ route('master.barang.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @include('barang.partials.form-fields', ['barang' => $barangModel, 'kategoris' => $kategoris, 'nextKodePreview' => $nextKodePreview])
                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit" class="inst-btn-primary">Simpan</button>
                    <a href="{{ route('master.barang.index') }}" class="inst-btn-outline">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            function initSelect2Barang() {
                if (!window.jQuery || !jQuery.fn.select2) return;
                const $el = jQuery('.select2-barang');
                if ($el.data('select2')) return;
                $el.select2({ width: '100%', language: { noResults: () => 'Tidak ada hasil' } });
            }

            function formatRupiahDigits(raw) {
                const digits = String(raw || '').replace(/\D/g, '');
                if (!digits) return '';
                return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }

            const harga = document.getElementById('harga_satuan');
            if (harga) {
                harga.addEventListener('input', function () {
                    const caret = harga.selectionStart;
                    const before = harga.value.length;
                    harga.value = formatRupiahDigits(harga.value);
                    const after = harga.value.length;
                    try {
                        harga.setSelectionRange(caret + (after - before), caret + (after - before));
                    } catch (e) {}
                });
            }

            const inputFoto = document.getElementById('foto-barang');
            const preview = document.getElementById('preview-foto-barang');
            if (inputFoto && preview) {
                if (preview.getAttribute('src') && preview.getAttribute('src').length > 0) {
                    preview.classList.remove('hidden');
                }
                inputFoto.addEventListener('change', function () {
                    const file = inputFoto.files && inputFoto.files[0];
                    if (!file) return;
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        preview.src = e.target.result;
                        preview.classList.remove('hidden');
                    };
                    reader.readAsDataURL(file);
                });
            }

            document.addEventListener('DOMContentLoaded', function () {
                initSelect2Barang();
                setTimeout(initSelect2Barang, 120);
            });
        })();
    </script>
@endpush
