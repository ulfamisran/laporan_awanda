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
                        <table class="min-w-full text-sm" id="table-items">
                            <thead style="background:#f8fbfd;">
                                <tr>
                                    <th class="p-2 text-left">Barang</th>
                                    <th class="p-2 text-left">Harga</th>
                                    <th class="p-2 text-left">Jumlah</th>
                                    <th class="p-2 text-left">Satuan</th>
                                    <th class="p-2 text-left">Supplier</th>
                                    <th class="p-2 text-left">Hari pakai</th>
                                    <th class="p-2 text-right">Aksi</th>
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
                    <td class="p-2"><select name="items[${idx}][barang_id]" class="inst-select barang-select" required>${barangOptions()}</select></td>
                    <td class="p-2"><input type="text" name="items[${idx}][harga_barang]" class="inst-input harga-input" required value="${init.harga_barang || ''}"></td>
                    <td class="p-2"><input type="number" step="0.01" min="0.01" name="items[${idx}][jumlah_barang]" class="inst-input" required value="${init.jumlah_barang || ''}"></td>
                    <td class="p-2"><input type="text" name="items[${idx}][satuan_barang]" class="inst-input satuan-input" required value="${init.satuan_barang || ''}"></td>
                    <td class="p-2"><select name="items[${idx}][supplier_id]" class="inst-select">${supplierOptions()}</select></td>
                    <td class="p-2"><input type="number" min="0" max="3650" name="items[${idx}][jumlah_hari_pemakaian]" class="inst-input" required value="${init.jumlah_hari_pemakaian || 0}"></td>
                    <td class="p-2 text-right"><button type="button" class="text-xs font-semibold text-red-600 btn-remove">Hapus</button></td>
                `;
                document.querySelector('#table-items tbody').appendChild(tr);

                const barangSelect = tr.querySelector('.barang-select');
                const hargaInput = tr.querySelector('.harga-input');
                const satuanInput = tr.querySelector('.satuan-input');

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
                    tr.remove();
                });
            }

            document.addEventListener('DOMContentLoaded', function () {
                document.getElementById('btn-add-item')?.addEventListener('click', function () {
                    addRow();
                });
                addRow();
            });
        })();
    </script>
@endpush
