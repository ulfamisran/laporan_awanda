@extends('layouts.app')

@section('title', $mode === 'create' ? 'Tambah Periode' : 'Ubah Periode')

@section('content')
    <div class="inst-form-page">
        <a href="{{ route('periode.index') }}" class="inst-back">← Kembali ke daftar periode</a>
        <h2 class="inst-form-title mt-4">{{ $mode === 'create' ? 'Tambah periode' : 'Ubah periode' }}</h2>
        <p class="inst-form-lead">Isi rentang tanggal, tanggal pelaporan, dan status periode laporan.</p>

        <div class="inst-form-card">
            <form method="POST" action="{{ $mode === 'create' ? route('periode.store') : route('periode.update', $periode) }}">
                @csrf
                @if ($mode === 'edit')
                    @method('PUT')
                @endif

                <div class="space-y-4">
                    <div>
                        <label for="nama" class="inst-label">Nama periode <span class="text-xs font-normal" style="color:#7fa8c9;">(opsional)</span></label>
                        <input type="text" name="nama" id="nama" value="{{ old('nama', $periode->nama) }}" class="inst-input" maxlength="191" placeholder="Contoh: Triwulan I 2026">
                    </div>
                    <div>
                        <label for="tanggal_awal" class="inst-label">Tanggal awal periode <span class="inst-required">*</span></label>
                        <input type="date" name="tanggal_awal" id="tanggal_awal" value="{{ old('tanggal_awal', $periode->tanggal_awal?->format('Y-m-d')) }}" class="inst-input" required>
                    </div>
                    <div>
                        <label for="tanggal_akhir" class="inst-label">Tanggal akhir periode <span class="inst-required">*</span></label>
                        <input type="date" name="tanggal_akhir" id="tanggal_akhir" value="{{ old('tanggal_akhir', $periode->tanggal_akhir?->format('Y-m-d')) }}" class="inst-input" required>
                    </div>
                    <div>
                        <label for="tanggal_pelaporan" class="inst-label">Tanggal pelaporan <span class="inst-required">*</span></label>
                        <input type="date" name="tanggal_pelaporan" id="tanggal_pelaporan" value="{{ old('tanggal_pelaporan', $periode->tanggal_pelaporan?->format('Y-m-d')) }}" class="inst-input" required>
                        <p class="mt-2 text-xs" style="color:#7fa8c9;">Tanggal acuan pelaporan untuk periode ini (misalnya batas pengumpulan laporan).</p>
                    </div>
                    <div>
                        <label for="status" class="inst-label">Status periode <span class="inst-required">*</span></label>
                        <select name="status" id="status" class="inst-select" required>
                            @foreach (\App\Enums\StatusAktif::cases() as $st)
                                <option value="{{ $st->value }}" @selected(old('status', $periode->status?->value ?? $st->value) === $st->value)>{{ $st->label() }}</option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-xs" style="color:#7fa8c9;">Hanya satu periode yang boleh berstatus Aktif pada satu waktu. Mengaktifkan periode ini akan menonaktifkan periode lain.</p>
                    </div>
                </div>

                <div class="mt-8 flex flex-wrap gap-3">
                    <button type="submit" class="inst-btn-primary">Simpan</button>
                    <a href="{{ route('periode.index') }}" class="inst-btn-outline">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
