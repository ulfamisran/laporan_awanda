@extends('layouts.app')

@section('title', 'Tambah Kategori Limbah')

@section('content')
    <div class="inst-form-page">
        <a href="{{ route('master.kategori-limbah.index') }}" class="inst-back">← Kembali ke daftar</a>
        <h2 class="inst-form-title">Tambah kategori limbah</h2>
        <p class="inst-form-lead">Isi nama dan deskripsi singkat kategori.</p>

        <div class="inst-form-card">
            <form method="POST" action="{{ route('master.kategori-limbah.store') }}" class="space-y-6">
                @csrf
                @include('kategori-limbah._form', ['kategori' => $kategori])
                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit" class="inst-btn-primary">Simpan</button>
                    <a href="{{ route('master.kategori-limbah.index') }}" class="inst-btn-outline">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
