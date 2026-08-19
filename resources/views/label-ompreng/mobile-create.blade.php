@extends('layouts.app')

@section('title', $pageTitle ?? 'Input Label Ompreng Mobile')

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
            'batas_konsumsi_dari' => '06:00',
            'batas_konsumsi_sampai' => '10:00',
        ]]);
        $nutrisiDefs = [
            ['key' => 'energi', 'label' => 'Energi', 'unit' => 'kkal'],
            ['key' => 'protein', 'label' => 'Protein', 'unit' => 'g'],
            ['key' => 'lemak', 'label' => 'Lemak', 'unit' => 'g'],
            ['key' => 'karbohidrat', 'label' => 'Karbohidrat', 'unit' => 'g'],
            ['key' => 'serat', 'label' => 'Serat', 'unit' => 'g'],
        ];
        $porsiDefs = [
            ['prefix' => 'porsi_besar', 'title' => 'Porsi Besar'],
            ['prefix' => 'porsi_kecil', 'title' => 'Porsi Kecil'],
            ['prefix' => 'balita', 'title' => 'Balita'],
            ['prefix' => 'bumil_busui', 'title' => 'Bumil/Busui'],
        ];
    @endphp

    <div class="mx-auto w-full max-w-2xl px-3 pb-20 sm:px-4">
        <a href="{{ route('label-ompreng.index') }}" class="inst-back">← Kembali</a>
        <h2 class="inst-form-title mt-2">{{ $heading ?? 'Input Label Ompreng Mobile' }}</h2>
        <p class="inst-form-lead text-sm" style="color:#4a6b7f;">Halaman khusus input via HP. Setelah disimpan, label bisa dilihat atau dicetak dari detail data.</p>

        <form method="POST" action="{{ $formAction ?? route('label-ompreng.store') }}" class="space-y-5" id="form-label-ompreng-mobile">
            @csrf
            @if (($formMethod ?? 'POST') !== 'POST')
                @method($formMethod)
            @endif
            <input type="hidden" name="template" value="premium">

            <div class="rounded-2xl border bg-white p-4 shadow-sm" style="border-color:#d4e8f4;">
                <div class="space-y-4">
                    <div>
                        <label class="inst-label">Instansi</label>
                        <input type="text" class="inst-input mt-1 bg-gray-50" value="Badan Gizi Nasional" readonly>
                    </div>
                    <div>
                        <label for="lokasi_sppg" class="inst-label">SPPG</label>
                        <input type="text" name="lokasi_sppg" id="lokasi_sppg" class="inst-input mt-1" required maxlength="255" value="{{ old('lokasi_sppg', $defaultLokasi) }}">
                        @error('lokasi_sppg')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="peringatan" class="inst-label">Peringatan</label>
                        <textarea name="peringatan" id="peringatan" rows="3" class="inst-input mt-1" required maxlength="1000">{{ old('peringatan', $defaultPeringatan) }}</textarea>
                        @error('peringatan')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="himbauan" class="inst-label">Himbauan</label>
                        <textarea name="himbauan" id="himbauan" rows="3" class="inst-input mt-1" required maxlength="1000">{{ old('himbauan', $defaultHimbauan) }}</textarea>
                        @error('himbauan')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-bold" style="color:#1a4a6b;">Data label</h3>
                    <p class="text-xs" style="color:#7fa8c9;">Input satu label per kartu.</p>
                </div>
                <button type="button" class="inst-btn-outline text-sm" id="btn-add-label-mobile">+ Tambah</button>
            </div>

            @error('labels')
                <p class="text-xs text-red-600">{{ $message }}</p>
            @enderror

            <div class="space-y-4" id="label-list-mobile"></div>

            <div class="sticky bottom-3 z-10 rounded-2xl border bg-white/95 p-3 shadow-lg backdrop-blur" style="border-color:#d4e8f4;">
                <div class="flex flex-col gap-2 sm:flex-row">
                    <button type="submit" class="inst-btn-primary w-full sm:w-auto">{{ $submitLabel ?? 'Simpan data label' }}</button>
                    <a href="{{ route('label-ompreng.index') }}" class="inst-btn-outline w-full text-center sm:w-auto">Batal</a>
                </div>
            </div>
        </form>
    </div>

    <template id="tpl-label-card-mobile">
        <div class="rounded-2xl border bg-white p-4 shadow-sm" style="border-color:#d4e8f4;">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h3 class="text-sm font-bold uppercase tracking-wide" style="color:#1a4a6b;">Label <span class="label-number"></span></h3>
                <button type="button" class="text-xs font-semibold text-red-600 btn-remove-label">Hapus</button>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="inst-label">Menu Hari Ini</label>
                    <div class="mt-2 space-y-2">
                        <input type="text" class="inst-input menu-field" data-name="menu_1" maxlength="255" placeholder="Menu 1">
                        <input type="text" class="inst-input menu-field" data-name="menu_2" maxlength="255" placeholder="Menu 2">
                        <input type="text" class="inst-input menu-field" data-name="menu_3" maxlength="255" placeholder="Menu 3">
                        <input type="text" class="inst-input menu-field" data-name="menu_4" maxlength="255" placeholder="Menu 4">
                        <input type="text" class="inst-input menu-field" data-name="menu_5" maxlength="255" placeholder="Menu 5">
                    </div>
                </div>

                <div>
                    <label class="inst-label">Satuan nutrisi</label>
                    <div class="mt-2 grid grid-cols-2 gap-2">
                        @foreach ($nutrisiDefs as $nutrisi)
                            <div>
                                <label class="inst-label text-xs">{{ $nutrisi['label'] }}</label>
                                <input type="text" class="inst-input mt-1" data-name="{{ $nutrisi['key'] }}_satuan" maxlength="20" value="{{ $nutrisi['unit'] }}">
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="space-y-3">
                    @foreach ($porsiDefs as $porsi)
                        <div class="rounded-xl border p-3" style="border-color:#d4e8f4;background:#fbfdff;">
                            <div class="mb-2 text-sm font-bold" style="color:#1a4a6b;">{{ $porsi['title'] }}</div>
                            <div class="space-y-2">
                                @foreach ($nutrisiDefs as $nutrisi)
                                    <div>
                                        <label class="inst-label text-xs">{{ $nutrisi['label'] }}</label>
                                        <input type="number" min="0" step="0.01" class="inst-input mt-1" data-name="{{ $porsi['prefix'] }}_{{ $nutrisi['key'] }}_nilai" placeholder="0">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="inst-label">Tanggal produksi</label>
                        <input type="date" class="inst-input mt-1" data-name="tanggal_produksi">
                    </div>
                    <div>
                        <label class="inst-label">Batas waktu konsumsi</label>
                        <div class="mt-1 grid grid-cols-2 gap-2">
                            <input type="time" class="inst-input" data-name="batas_konsumsi_dari">
                            <input type="time" class="inst-input" data-name="batas_konsumsi_sampai">
                        </div>
                        <p class="mt-1 text-xs" style="color:#7fa8c9;">Isi jam mulai dan jam akhir konsumsi.</p>
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
            const container = document.getElementById('label-list-mobile');
            const template = document.getElementById('tpl-label-card-mobile');
            let labelIndex = 0;

            function updateNumbers() {
                Array.from(container.querySelectorAll('[data-label-card]')).forEach(function (card, idx) {
                    const no = card.querySelector('.label-number');
                    if (no) no.textContent = String(idx + 1);
                });
            }

            function buildName(idx, field) {
                return 'labels[' + idx + '][' + field + ']';
            }

            function addCard(data) {
                const node = template.content.firstElementChild.cloneNode(true);
                node.setAttribute('data-label-card', '1');
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

            document.getElementById('btn-add-label-mobile')?.addEventListener('click', function () {
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
                    batas_konsumsi_dari: '06:00',
                    batas_konsumsi_sampai: '10:00',
                });
            });

            if (Array.isArray(initialLabels) && initialLabels.length) {
                initialLabels.forEach(addCard);
            } else {
                document.getElementById('btn-add-label-mobile')?.click();
            }
        })();
    </script>
@endpush
