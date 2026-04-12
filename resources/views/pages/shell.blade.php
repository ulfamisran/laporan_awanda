@extends('layouts.app')

@section('title', $title ?? 'Halaman')

@section('content')
    <div class="rounded-xl border border-emerald-100 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-emerald-900">{{ $title ?? 'Halaman' }}</h2>
        <p class="mt-2 text-sm text-slate-600">
            Modul ini akan diisi pada pengembangan berikutnya. Struktur rute dan menu sudah disiapkan.
        </p>
    </div>
@endsection
