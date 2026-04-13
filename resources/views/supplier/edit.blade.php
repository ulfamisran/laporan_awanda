@extends('layouts.app')

@section('title', 'Ubah Supplier')

@section('content')
    <div class="inst-form-page" style="max-width:40rem;">
        <a href="{{ route('master.supplier.index') }}" class="inst-back">← Kembali ke daftar</a>
        <h2 class="inst-form-title">Ubah supplier</h2>
        <div class="inst-form-card">
            <form method="POST" action="{{ route('master.supplier.update', $supplier) }}" class="space-y-6">
                @csrf
                @method('PUT')
                @include('supplier._form', ['supplier' => $supplier])
                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit" class="inst-btn-primary">Simpan</button>
                    <a href="{{ route('master.supplier.index') }}" class="inst-btn-outline">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
