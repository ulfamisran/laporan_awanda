@extends('layouts.app')

@section('title', 'Buat Order Barang')

@section('content')
    <div class="inst-form-page" style="max-width:72rem;">
        <a href="{{ route('stok.order.index') }}" class="inst-back">← Kembali</a>
        <h2 class="inst-form-title">Buat order barang</h2>
        <p class="inst-form-lead text-sm" style="color:#4a6b7f;">Nomor order: <span class="font-mono font-semibold">{{ $previewNomorOrder }}</span> (otomatis saat simpan)</p>
        @include('components.periode-aktif-badge')

        <div class="inst-form-card">
            <form method="POST" action="{{ route('stok.order.store') }}" class="space-y-5" id="form-order">
                @csrf

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="tanggal_order" class="inst-label">Tanggal pencatatan <span class="inst-required">*</span></label>
                        <input type="date" name="tanggal_order" id="tanggal_order" class="inst-input" required value="{{ old('tanggal_order', now()->toDateString()) }}">
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
                                    <th class="w-[28%] p-2 text-left">Barang</th>
                                    <th class="w-[10%] p-2 text-left">Harga</th>
                                    <th class="w-[9%] p-2 text-left">Jumlah</th>
                                    <th class="w-[9%] p-2 text-left">Satuan</th>
                                    <th class="w-[26%] p-2 text-left">Supplier</th>
                                    <th class="w-[10%] p-2 text-left">Hari pakai</th>
                                    <th class="w-[8%] p-2 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit" class="inst-btn-primary">Simpan order</button>
                    <a href="{{ route('stok.order.index') }}" class="inst-btn-outline">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        #table-items .select2-container--default .select2-selection--single {
            height: 42px;
            border: 1px solid #d4e8f4;
            border-radius: 0.5rem;
            background-color: #ffffff;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        #table-items .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 40px;
            color: #1a4a6b;
            padding-left: 1rem;
            padding-right: 2rem;
            font-size: 0.75rem;
        }

        #table-items .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
            right: 0.5rem;
        }

        #table-items .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #4a9b7a;
            box-shadow: 0 0 0 1px #4a9b7a;
        }

        .select2-dropdown .select2-results__option {
            font-size: 0.75rem;
        }

        .select2-container .select2-search--dropdown .select2-search__field {
            font-size: 0.75rem;
        }
    </style>
@endpush

@push('scripts')
    @php
        $barangsForJs = $barangs->map(function ($b) {
            return [
                'id' => $b->id,
                'label' => $b->kode_barang.' - '.$b->nama_barang,
                'harga' => (float) $b->harga_satuan,
                'satuan' => $b->satuan?->value ?? '',
            ];
        })->values();
        $suppliersForJs = $suppliers->map(function ($s) {
            return [
                'id' => $s->id,
                'nama' => $s->nama_supplier,
            ];
        })->values();
    @endphp
    <script>
        (function () {
            const barangs = @json($barangsForJs);
            const suppliers = @json($suppliersForJs);

            let rowIndex = 0;

            function rupiahRaw(v) {
                return String(v || '').replace(/\D/g, '');
            }

            function formatDigits(v) {
                const d = rupiahRaw(v);
                if (!d) return '';
                return d.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }

            function barangOptions() {
                return ['<option value="">Pilih barang...</option>']
                    .concat(barangs.map((b) => `<option value="${b.id}" data-harga="${Math.round(b.harga)}" data-satuan="${b.satuan}">${b.label}</option>`))
                    .join('');
            }

            function supplierOptions() {
                return ['<option value="">-</option>']
                    .concat(suppliers.map((s) => `<option value="${s.id}">${s.nama}</option>`))
                    .join('');
            }

            function addRow(init = {}) {
                const idx = rowIndex++;
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="p-2"><select name="items[${idx}][barang_id]" class="inst-select barang-select select2-order-barang" required>${barangOptions()}</select></td>
                    <td class="p-2"><input type="text" name="items[${idx}][harga_barang]" class="inst-input harga-input px-2" required value="${init.harga_barang || ''}"></td>
                    <td class="p-2"><input type="number" step="0.01" min="0.01" name="items[${idx}][jumlah_barang]" class="inst-input px-2" required value="${init.jumlah_barang || ''}"></td>
                    <td class="p-2"><input type="text" name="items[${idx}][satuan_barang]" class="inst-input satuan-input px-2" required value="${init.satuan_barang || ''}"></td>
                    <td class="p-2"><select name="items[${idx}][supplier_id]" class="inst-select supplier-select select2-order-barang">${supplierOptions()}</select></td>
                    <td class="p-2"><input type="number" min="0" max="3650" name="items[${idx}][jumlah_hari_pemakaian]" class="inst-input" required value="${init.jumlah_hari_pemakaian || 0}"></td>
                    <td class="p-2 text-right"><button type="button" class="text-xs font-semibold text-red-600 btn-remove">Hapus</button></td>
                `;
                document.querySelector('#table-items tbody').appendChild(tr);

                const barangSelect = tr.querySelector('.barang-select');
                const hargaInput = tr.querySelector('.harga-input');
                const satuanInput = tr.querySelector('.satuan-input');
                const supplierSelect = tr.querySelector('.supplier-select');

                if (window.jQuery && jQuery.fn.select2) {
                    jQuery(barangSelect).select2({ width: '100%', language: { noResults: () => 'Tidak ada hasil' } });
                    jQuery(supplierSelect).select2({ width: '100%', language: { noResults: () => 'Tidak ada hasil' } });
                }

                barangSelect?.addEventListener('change', function () {
                    const opt = this.selectedOptions?.[0];
                    if (!opt) return;
                    if (!hargaInput.value) hargaInput.value = formatDigits(opt.dataset.harga || '');
                    if (!satuanInput.value) satuanInput.value = opt.dataset.satuan || '';
                });

                hargaInput?.addEventListener('input', function () {
                    this.value = formatDigits(this.value);
                });

                tr.querySelector('.btn-remove')?.addEventListener('click', function () {
                    if (window.jQuery && jQuery.fn.select2) {
                        jQuery(barangSelect).select2('destroy');
                        jQuery(supplierSelect).select2('destroy');
                    }
                    tr.remove();
                });
            }

            document.addEventListener('DOMContentLoaded', function () {
                document.getElementById('btn-add-item')?.addEventListener('click', function () {
                    addRow();
                });
                addRow();

                document.getElementById('form-order')?.addEventListener('submit', function () {
                    this.querySelectorAll('.harga-input').forEach(function (el) {
                        el.value = rupiahRaw(el.value);
                    });
                });
            });
        })();
    </script>
@endpush
