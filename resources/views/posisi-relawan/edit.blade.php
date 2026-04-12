@extends('layouts.app')

@section('title', 'Ubah Posisi Relawan')

@section('content')
    <div class="inst-form-page" style="max-width:40rem;">
        <a href="{{ route('master.posisi-relawan.index') }}" class="inst-back">← Kembali ke daftar</a>
        <h2 class="inst-form-title">Ubah posisi relawan</h2>
        <div class="inst-form-card">
            <form method="POST" action="{{ route('master.posisi-relawan.update', $posisi) }}" class="space-y-6">
                @csrf
                @method('PUT')
                @include('posisi-relawan._form', ['posisi' => $posisi])
                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit" class="inst-btn-primary">Simpan</button>
                    <a href="{{ route('master.posisi-relawan.index') }}" class="inst-btn-outline">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
