@extends('layouts.app')

@section('title', 'Tambah Relawan')

@section('content')
    <div class="inst-form-page" style="max-width:52rem;">
        <a href="{{ route('master.relawan.index') }}" class="inst-back">← Kembali ke daftar</a>
        <h2 class="inst-form-title">Tambah relawan</h2>
        <p class="inst-form-lead">Isi data dasar relawan. Detail lain (kontak, foto, dll.) dapat dilengkapi saat ubah profil.</p>
        <div class="inst-form-card">
            <form id="form-relawan" method="POST" action="{{ route('master.relawan.store') }}" class="space-y-6">
                @csrf
                @include('relawan._form-create', ['relawan' => $relawan, 'posisis' => $posisis])
                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit" class="inst-btn-primary">Simpan</button>
                    <a href="{{ route('master.relawan.index') }}" class="inst-btn-outline">Batal</a>
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

            ['gaji_pokok', 'gaji_per_hari'].forEach(function (id) {
                const input = document.getElementById(id);
                if (!input) return;
                input.addEventListener('input', function () {
                    const caret = input.selectionStart;
                    const before = input.value.length;
                    input.value = formatRupiahDigits(input.value);
                    const after = input.value.length;
                    try {
                        input.setSelectionRange(caret + (after - before), caret + (after - before));
                    } catch (e) {}
                });
            });

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

            document.addEventListener('DOMContentLoaded', function () {
                initSelect2Relawan();
                setTimeout(initSelect2Relawan, 120);
            });
        })();
    </script>
@endpush
