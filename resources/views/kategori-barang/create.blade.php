@extends('layouts.app')

@section('title', 'Tambah Kategori Barang')

@section('content')
    <div class="inst-form-page">
        <a href="{{ route('master.kategori-barang.index') }}" class="inst-back">← Kembali ke daftar</a>
        <h2 class="inst-form-title">Tambah kategori barang</h2>
        <p class="inst-form-lead">Isi nama dan deskripsi singkat kategori.</p>

        <div class="inst-form-card">
            <form method="POST" action="{{ route('master.kategori-barang.store') }}" class="space-y-6">
                @csrf
                @include('kategori-barang._form', ['kategori' => $kategori])
                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit" class="inst-btn-primary">Simpan</button>
                    <a href="{{ route('master.kategori-barang.index') }}" class="inst-btn-outline">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
