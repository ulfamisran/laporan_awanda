@extends('layouts.app')

@section('title', 'Tambah Supplier')

@section('content')
    <div class="inst-form-page" style="max-width:40rem;">
        <a href="{{ route('master.supplier.index') }}" class="inst-back">← Kembali ke daftar</a>
        <h2 class="inst-form-title">Tambah supplier</h2>
        <p class="inst-form-lead">Lengkapi data supplier untuk kebutuhan operasional.</p>
        <div class="inst-form-card">
            <form method="POST" action="{{ route('master.supplier.store') }}" class="space-y-6">
                @csrf
                @include('supplier._form', ['supplier' => $supplier])
                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit" class="inst-btn-primary">Simpan</button>
                    <a href="{{ route('master.supplier.index') }}" class="inst-btn-outline">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
