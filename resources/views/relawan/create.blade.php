@extends('layouts.app')

@section('title', 'Tambah Relawan')

@section('content')
    <div class="inst-form-page" style="max-width:52rem;">
        <a href="{{ route('master.relawan.index') }}" class="inst-back">← Kembali ke daftar</a>
        <h2 class="inst-form-title">Tambah relawan</h2>
        <p class="inst-form-lead">Lengkapi data relawan dan unggah foto (opsional).</p>
        <div class="inst-form-card">
            <form id="form-relawan" method="POST" action="{{ route('master.relawan.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @include('relawan._form', ['relawan' => $relawan, 'posisis' => $posisis])
                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit" class="inst-btn-primary">Simpan</button>
                    <a href="{{ route('master.relawan.index') }}" class="inst-btn-outline">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
    <script>
        (function () {
            let cropper = null;
            const fileInput = document.getElementById('foto-relawan');
            const cropImg = document.getElementById('crop-image-relawan');
            const preview = document.getElementById('preview-foto-relawan');
            const form = document.getElementById('form-relawan');

            function formatRupiahDigits(raw) {
                const digits = String(raw || '').replace(/\D/g, '');
                if (!digits) return '';
                return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }

            const gaji = document.getElementById('gaji_pokok');
            if (gaji) {
                gaji.addEventListener('input', function () {
                    const caret = gaji.selectionStart;
                    const before = gaji.value.length;
                    gaji.value = formatRupiahDigits(gaji.value);
                    const after = gaji.value.length;
                    try {
                        gaji.setSelectionRange(caret + (after - before), caret + (after - before));
                    } catch (e) {}
                });
            }

            function initSelect2Relawan() {
                if (!window.jQuery || !jQuery.fn.select2) return;
                jQuery('.select2-relawan')
                    .not('[data-select2-relawan]')
                    .each(function () {
                        const $el = jQuery(this);
                        $el.attr('data-select2-relawan', '1');
                        $el.select2({ width: '100%', language: { noResults: () => 'Tidak ada hasil' } });
                    });
            }

            function destroyCropper() {
                if (cropper) {
                    cropper.destroy();
                    cropper = null;
                }
            }

            if (fileInput && cropImg) {
                fileInput.addEventListener('change', function () {
                    destroyCropper();
                    const file = fileInput.files && fileInput.files[0];
                    if (!file) return;
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        cropImg.src = e.target.result;
                        cropImg.classList.remove('hidden');
                        if (preview) preview.classList.add('hidden');
                        cropper = new Cropper(cropImg, {
                            aspectRatio: 1,
                            viewMode: 1,
                            autoCropArea: 0.92,
                        });
                    };
                    reader.readAsDataURL(file);
                });
            }

            if (form) {
                form.addEventListener('submit', function () {
                    const cx = document.getElementById('crop_x');
                    const cy = document.getElementById('crop_y');
                    const cw = document.getElementById('crop_w');
                    const ch = document.getElementById('crop_h');
                    if (cropper && fileInput && fileInput.files && fileInput.files.length) {
                        const d = cropper.getData(true);
                        if (cx) cx.value = Math.round(d.x);
                        if (cy) cy.value = Math.round(d.y);
                        if (cw) cw.value = Math.round(d.width);
                        if (ch) ch.value = Math.round(d.height);
                    }
                });
            }

            document.addEventListener('DOMContentLoaded', function () {
                initSelect2Relawan();
                setTimeout(initSelect2Relawan, 120);
            });
        })();
    </script>
@endpush
