@extends('layouts.app')

@section('title', $mode === 'edit' ? 'Ubah Akun Dana' : 'Tambah Akun Dana')

@section('content')
    <div class="inst-form-page" style="max-width:40rem;">
        <a href="{{ route('master.akun-dana.index') }}" class="inst-back">← Kembali</a>
        <h2 class="inst-form-title">{{ $mode === 'edit' ? 'Ubah akun dana' : 'Tambah akun dana' }}</h2>
        <div class="inst-form-card">
            <form method="POST" action="{{ $mode === 'edit' ? route('master.akun-dana.update', $akun) : route('master.akun-dana.store') }}" class="space-y-5">
                @csrf
                @if ($mode === 'edit')
                    @method('PUT')
                @endif

                <div>
                    <label for="kode" class="inst-label">Kode <span class="inst-required">*</span></label>
                    <input type="text" name="kode" id="kode" class="inst-input font-mono" required maxlength="32" value="{{ old('kode', $akun->kode) }}">
                    @error('kode')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="nama" class="inst-label">Nama akun <span class="inst-required">*</span></label>
                    <input type="text" name="nama" id="nama" class="inst-input" required maxlength="255" value="{{ old('nama', $akun->nama) }}">
                    @error('nama')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="parent_id" class="inst-label">Induk (grup)</label>
                    <select name="parent_id" id="parent_id" class="inst-select">
                        <option value="">— Akar —</option>
                        @foreach ($parents as $p)
                            <option value="{{ $p->id }}" @selected((int) old('parent_id', $akun->parent_id) === (int) $p->id)>{{ $p->kode }} — {{ $p->nama }}</option>
                        @endforeach
                    </select>
                    @error('parent_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="urutan" class="inst-label">Urutan <span class="inst-required">*</span></label>
                    <input type="number" name="urutan" id="urutan" class="inst-input font-mono" required min="0" max="65535" value="{{ old('urutan', $akun->urutan ?? 0) }}">
                    @error('urutan')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="is_grup" class="inst-label">Tipe <span class="inst-required">*</span></label>
                    @php($ig = (string) old('is_grup', $akun->exists ? ($akun->is_grup ? '1' : '0') : '0'))
                    <select name="is_grup" id="is_grup" class="inst-select" required>
                        <option value="0" @selected($ig === '0')>Akun detail (bisa diisi saldo)</option>
                        <option value="1" @selected($ig === '1')>Grup (penjumlahan anak)</option>
                    </select>
                    @error('is_grup')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-wrap gap-2 pt-2">
                    <button type="submit" class="inst-btn-primary">Simpan</button>
                    <a href="{{ route('master.akun-dana.index') }}" class="inst-btn-outline">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
