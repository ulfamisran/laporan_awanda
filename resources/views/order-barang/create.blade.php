@extends('layouts.app')

@section('title', $pageTitle ?? 'Buat Order Barang')

@section('content')
    <div class="inst-form-page" style="max-width:72rem;">
        <a href="{{ route('stok.order.index') }}" class="inst-back">← Kembali</a>
        <h2 class="inst-form-title">{{ $heading ?? 'Buat order barang' }}</h2>
        <p class="inst-form-lead text-sm" style="color:#4a6b7f;">Nomor order: <span class="font-mono font-semibold">{{ $previewNomorOrder }}</span> {{ ($isEdit ?? false) ? '' : '(otomatis saat simpan)' }}</p>
        @include('components.periode-aktif-badge')

        <div class="inst-form-card">
            <form method="POST" action="{{ $formAction ?? route('stok.order.store') }}" class="space-y-5" id="form-order">
                @csrf
                @if (($formMethod ?? 'POST') !== 'POST')
                    @method($formMethod)
                @endif

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="tanggal_order" class="inst-label">Tanggal pencatatan <span class="inst-required">*</span></label>
                        <input type="date" name="tanggal_order" id="tanggal_order" class="inst-input" required value="{{ $tanggalOrder ?? old('tanggal_order', now()->toDateString()) }}">
                    </div>
                </div>

                <div>
                    <div class="mb-2 flex items-center justify-between">
                        <label class="inst-label">Daftar barang order</label>
                        <button type="button" class="inst-btn-outline text-xs" id="btn-add-item">+ Tambah baris</button>
                    </div>

                    <div class="overflow-x-auto rounded-xl border" style="border-color:#d4e8f4;">
                        <table class="min-w-full table-fixed text-sm" id="table-items">
                            <thead style="background:#f8fbfd;">
                                <tr>
                                    <th class="w-[24%] p-2 text-left">Barang</th>
                                    <th class="w-[12%] p-2 text-left">Harga</th>
                                    <th class="w-[10%] p-2 text-left">Jumlah</th>
                                    <th class="w-[10%] p-2 text-left">Satuan</th>
                                    <th class="w-[18%] p-2 text-left">Supplier</th>
                                    <th class="w-[14%] p-2 text-left">Pemakaian (hari)</th>
                                    <th class="w-[12%] p-2 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit" class="inst-btn-primary">{{ $submitLabel ?? 'Simpan order' }}</button>
                    <a href="{{ route('stok.order.index') }}" class="inst-btn-outline">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    @php
        $initialItemsForJs = collect($initialItems ?? [])->values();
    @endphp
    <script>
        (function () {
            const initialItems = @json($initialItemsForJs);
            let rowIndex = 0;

            function rupiahRaw(v) {
                return String(v || '').replace(/\D/g, '');
            }

            function formatDigits(v) {
                const d = rupiahRaw(v);
                if (!d) return '';
                return d.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }

            function addRow(init = {}) {
                const idx = rowIndex++;
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="p-2"><input type="text" name="items[${idx}][nama_barang]" class="inst-input px-2" required value="${init.nama_barang || ''}" placeholder="Nama barang"></td>
                    <td class="p-2"><input type="text" name="items[${idx}][harga_barang]" class="inst-input harga-input px-2" required value="${init.harga_barang || ''}"></td>
                    <td class="p-2"><input type="number" step="0.01" min="0.01" name="items[${idx}][jumlah_barang]" class="inst-input px-2" required value="${init.jumlah_barang || ''}"></td>
                    <td class="p-2"><input type="text" name="items[${idx}][satuan_barang]" class="inst-input satuan-input px-2" required value="${init.satuan_barang || ''}"></td>
                    <td class="p-2"><input type="text" name="items[${idx}][supplier_nama]" class="inst-input px-2" value="${init.supplier_nama || ''}" placeholder="Nama supplier (opsional)"></td>
                    <td class="p-2"><input type="number" min="0" max="3650" step="1" name="items[${idx}][jumlah_hari_pemakaian]" class="inst-input px-2" required value="${init.jumlah_hari_pemakaian ?? 0}"></td>
                    <td class="p-2 text-right"><button type="button" class="text-xs font-semibold text-red-600 btn-remove">Hapus</button></td>
                `;
                document.querySelector('#table-items tbody').appendChild(tr);

                const hargaInput = tr.querySelector('.harga-input');

                hargaInput?.addEventListener('input', function () {
                    this.value = formatDigits(this.value);
                });

                tr.querySelector('.btn-remove')?.addEventListener('click', function () {
                    tr.remove();
                });
            }

            document.addEventListener('DOMContentLoaded', function () {
                document.getElementById('btn-add-item')?.addEventListener('click', function () {
                    addRow();
                });
                if (Array.isArray(initialItems) && initialItems.length > 0) {
                    initialItems.forEach(function (item) {
                        addRow(item || {});
                    });
                } else {
                    addRow();
                }

                document.getElementById('form-order')?.addEventListener('submit', function () {
                    this.querySelectorAll('.harga-input').forEach(function (el) {
                        el.value = rupiahRaw(el.value);
                    });
                });
            });
        })();
    </script>
@endpush
