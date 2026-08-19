@extends('layouts.app')

@section('title', $pageTitle ?? 'Tambah Label Ompreng')

@section('content')
    @php
        $defaultLabels = old('labels', $initialLabels ?? [[
            'menu_1' => '',
            'menu_2' => '',
            'menu_3' => '',
            'menu_4' => '',
            'menu_5' => '',
            'energi_satuan' => 'kkal',
            'protein_satuan' => 'g',
            'lemak_satuan' => 'g',
            'karbohidrat_satuan' => 'g',
            'serat_satuan' => 'g',
            'porsi_besar_energi_nilai' => '',
            'porsi_besar_protein_nilai' => '',
            'porsi_besar_lemak_nilai' => '',
            'porsi_besar_karbohidrat_nilai' => '',
            'porsi_besar_serat_nilai' => '',
            'porsi_kecil_energi_nilai' => '',
            'porsi_kecil_protein_nilai' => '',
            'porsi_kecil_lemak_nilai' => '',
            'porsi_kecil_karbohidrat_nilai' => '',
            'porsi_kecil_serat_nilai' => '',
            'balita_energi_nilai' => '',
            'balita_protein_nilai' => '',
            'balita_lemak_nilai' => '',
            'balita_karbohidrat_nilai' => '',
            'balita_serat_nilai' => '',
            'bumil_busui_energi_nilai' => '',
            'bumil_busui_protein_nilai' => '',
            'bumil_busui_lemak_nilai' => '',
            'bumil_busui_karbohidrat_nilai' => '',
            'bumil_busui_serat_nilai' => '',
            'tanggal_produksi' => now()->toDateString(),
            'batas_konsumsi' => '10:00',
        ]]);
    @endphp

    <div class="inst-form-page" style="max-width: 80rem;">
        <a href="{{ route('label-ompreng.index') }}" class="inst-back">← Kembali</a>
        <h2 class="inst-form-title">{{ $heading ?? 'Tambah Label Ompreng' }}</h2>
        <p class="inst-form-lead text-sm" style="color:#4a6b7f;">Isi data umum dan beberapa label sekaligus, lalu simpan agar masuk ke riwayat.</p>

        <div class="inst-form-card">
            <form method="POST" action="{{ $formAction ?? route('label-ompreng.store') }}" class="space-y-6" id="form-label-ompreng">
                @csrf
                @if (($formMethod ?? 'POST') !== 'POST')
                    @method($formMethod)
                @endif

                <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                    <div class="lg:col-span-1">
                        <label class="inst-label">Instansi</label>
                        <input type="text" class="inst-input mt-1 bg-gray-50" value="Badan Gizi Nasional" readonly>
                    </div>
                    <input type="hidden" name="template" value="premium">
                    <div class="lg:col-span-2">
                        <label for="lokasi_sppg" class="inst-label">SPPG</label>
                        <input type="text" name="lokasi_sppg" id="lokasi_sppg" class="inst-input mt-1" required maxlength="255"
                            value="{{ old('lokasi_sppg', $defaultLokasi) }}">
                        @error('lokasi_sppg')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="lg:col-span-3">
                        <label for="peringatan" class="inst-label">Peringatan</label>
                        <textarea name="peringatan" id="peringatan" rows="2" class="inst-input mt-1" required maxlength="1000">{{ old('peringatan', $defaultPeringatan) }}</textarea>
                        @error('peringatan')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="lg:col-span-3">
                        <label for="himbauan" class="inst-label">Himbauan</label>
                        <textarea name="himbauan" id="himbauan" rows="2" class="inst-input mt-1" required maxlength="1000">{{ old('himbauan', $defaultHimbauan) }}</textarea>
                        @error('himbauan')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-base font-bold" style="color:#1a4a6b;">Data label</h3>
                        <p class="text-xs" style="color:#7fa8c9;">Setiap kartu mewakili 1 label ompreng.</p>
                    </div>
                    <button type="button" class="inst-btn-outline text-sm" id="btn-add-label">+ Tambah label</button>
                </div>

                @error('labels')
                    <p class="text-xs text-red-600">{{ $message }}</p>
                @enderror

                <div class="space-y-4" id="label-list"></div>

                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit" class="inst-btn-primary">{{ $submitLabel ?? 'Simpan data label' }}</button>
                    <a href="{{ route('label-ompreng.index') }}" class="inst-btn-outline">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <template id="tpl-label-card">
        <div class="rounded-2xl border p-4 shadow-sm label-card" style="border-color:#d4e8f4;background:#fbfdff;">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h4 class="text-sm font-bold uppercase tracking-wide" style="color:#1a4a6b;">Label <span class="label-number"></span></h4>
                <button type="button" class="text-xs font-semibold text-red-600 btn-remove-label">Hapus</button>
            </div>

            <div class="space-y-4">
                <div>
                    <p class="inst-label">Menu 5 poin</p>
                    <div class="mt-2 space-y-2">
                        <input type="text" class="inst-input menu-field" data-name="menu_1" maxlength="255" placeholder="Poin menu 1">
                        <input type="text" class="inst-input menu-field" data-name="menu_2" maxlength="255" placeholder="Poin menu 2">
                        <input type="text" class="inst-input menu-field" data-name="menu_3" maxlength="255" placeholder="Poin menu 3">
                        <input type="text" class="inst-input menu-field" data-name="menu_4" maxlength="255" placeholder="Poin menu 4">
                        <input type="text" class="inst-input menu-field" data-name="menu_5" maxlength="255" placeholder="Poin menu 5">
                    </div>
                </div>

                <div>
                    <p class="inst-label">Nilai gizi per porsi</p>
                    <div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-5">
                        @foreach ([
                            ['key' => 'energi', 'label' => 'Sat. Energi', 'unit' => 'kkal'],
                            ['key' => 'protein', 'label' => 'Sat. Protein', 'unit' => 'g'],
                            ['key' => 'lemak', 'label' => 'Sat. Lemak', 'unit' => 'g'],
                            ['key' => 'karbohidrat', 'label' => 'Sat. Karbo', 'unit' => 'g'],
                            ['key' => 'serat', 'label' => 'Sat. Serat', 'unit' => 'g'],
                        ] as $satuan)
                            <div>
                                <label class="inst-label text-xs">{{ $satuan['label'] }}</label>
                                <input type="text" class="inst-input mt-1" data-name="{{ $satuan['key'] }}_satuan" maxlength="20" value="{{ $satuan['unit'] }}">
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-3 overflow-x-auto rounded-xl border" style="border-color:#d4e8f4;background:#ffffff;">
                        <table class="w-full text-xs">
                            <thead>
                                <tr style="background:#eef6fb;">
                                    <th class="px-2 py-2 text-left font-bold uppercase tracking-wide" style="color:#1a4a6b;">Komponen</th>
                                    <th class="px-2 py-2 text-center font-bold uppercase tracking-wide" style="color:#1a4a6b;">Porsi Besar</th>
                                    <th class="px-2 py-2 text-center font-bold uppercase tracking-wide" style="color:#1a4a6b;">Porsi Kecil</th>
                                    <th class="px-2 py-2 text-center font-bold uppercase tracking-wide" style="color:#1a4a6b;">Balita</th>
                                    <th class="px-2 py-2 text-center font-bold uppercase tracking-wide" style="color:#1a4a6b;">Bumil/Busui</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ([
                                    ['key' => 'energi', 'label' => 'Energi'],
                                    ['key' => 'protein', 'label' => 'Protein'],
                                    ['key' => 'lemak', 'label' => 'Lemak'],
                                    ['key' => 'karbohidrat', 'label' => 'Karbohidrat'],
                                    ['key' => 'serat', 'label' => 'Serat'],
                                ] as $nutrisi)
                                    <tr style="border-top:1px solid #e8f1f8;">
                                        <td class="px-2 py-1.5 font-semibold" style="color:#334155;">{{ $nutrisi['label'] }}</td>
                                        @foreach (['porsi_besar', 'porsi_kecil', 'balita', 'bumil_busui'] as $prefix)
                                            <td class="px-1 py-1.5">
                                                <input type="number" min="0" step="0.01" class="inst-input text-center text-xs" data-name="{{ $prefix }}_{{ $nutrisi['key'] }}_nilai" placeholder="0" style="min-width:3.5rem;">
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="inst-label">Tanggal produksi</label>
                        <input type="date" class="inst-input mt-1" data-name="tanggal_produksi">
                    </div>
                    <div>
                        <label class="inst-label">Batas waktu konsumsi</label>
                        <input type="time" class="inst-input mt-1" data-name="batas_konsumsi">
                    </div>
                </div>
            </div>
        </div>
    </template>
@endsection

@push('scripts')
    <script>
        (function () {
            const initialLabels = @json(array_values($defaultLabels));
            const container = document.getElementById('label-list');
            const template = document.getElementById('tpl-label-card');
            let labelIndex = 0;

            function updateNumbers() {
                Array.from(container.querySelectorAll('.label-card')).forEach(function (card, idx) {
                    const no = card.querySelector('.label-number');
                    if (no) no.textContent = String(idx + 1);
                });
            }

            function buildName(idx, field) {
                return 'labels[' + idx + '][' + field + ']';
            }

            function addCard(data) {
                const node = template.content.firstElementChild.cloneNode(true);
                const idx = labelIndex++;

                node.querySelectorAll('[data-name]').forEach(function (input) {
                    const field = input.getAttribute('data-name');
                    input.name = buildName(idx, field);
                    input.required = true;
                    if (data && Object.prototype.hasOwnProperty.call(data, field)) {
                        input.value = data[field] ?? '';
                    }
                });

                node.querySelectorAll('.menu-field').forEach(function (input) {
                    input.required = true;
                });

                node.querySelector('.btn-remove-label')?.addEventListener('click', function () {
                    node.remove();
                    updateNumbers();
                });

                container.appendChild(node);
                updateNumbers();
            }

            document.getElementById('btn-add-label')?.addEventListener('click', function () {
                addCard({
                    menu_1: '',
                    menu_2: '',
                    menu_3: '',
                    menu_4: '',
                    menu_5: '',
                    energi_satuan: 'kkal',
                    protein_satuan: 'g',
                    lemak_satuan: 'g',
                    karbohidrat_satuan: 'g',
                    serat_satuan: 'g',
                    porsi_besar_energi_nilai: '',
                    porsi_besar_protein_nilai: '',
                    porsi_besar_lemak_nilai: '',
                    porsi_besar_karbohidrat_nilai: '',
                    porsi_besar_serat_nilai: '',
                    porsi_kecil_energi_nilai: '',
                    porsi_kecil_protein_nilai: '',
                    porsi_kecil_lemak_nilai: '',
                    porsi_kecil_karbohidrat_nilai: '',
                    porsi_kecil_serat_nilai: '',
                    balita_energi_nilai: '',
                    balita_protein_nilai: '',
                    balita_lemak_nilai: '',
                    balita_karbohidrat_nilai: '',
                    balita_serat_nilai: '',
                    bumil_busui_energi_nilai: '',
                    bumil_busui_protein_nilai: '',
                    bumil_busui_lemak_nilai: '',
                    bumil_busui_karbohidrat_nilai: '',
                    bumil_busui_serat_nilai: '',
                    tanggal_produksi: new Date().toISOString().slice(0, 10),
                    batas_konsumsi: '10:00',
                });
            });

            if (Array.isArray(initialLabels) && initialLabels.length) {
                initialLabels.forEach(addCard);
            } else {
                document.getElementById('btn-add-label')?.click();
            }

        })();
        if (window.lucide) lucide.createIcons();
    </script>
@endpush
