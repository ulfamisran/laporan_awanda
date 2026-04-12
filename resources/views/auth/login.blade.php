@extends('layouts.guest')

@section('title', 'Masuk')

@section('brand')
    <div class="mb-10 text-center text-white">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl text-2xl font-bold text-white ring-2 ring-white/25" style="background:#4a9b7a;">
            M
        </div>
        <h1 class="mt-6 text-2xl font-bold tracking-tight text-white sm:text-3xl" style="font-family:'Plus Jakarta Sans',sans-serif;">
            {{ config('app.name') }}
        </h1>
        <p class="mt-2 text-sm" style="color:#b8d4e8;">Sistem Pengelolaan Dapur MBG</p>
    </div>
@endsection

@section('content')
    <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
        @csrf
        <div>
            <label for="email" class="inst-auth-label">Email <span style="color:#e74c3c;">*</span></label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                required
                autocomplete="username"
                class="inst-auth-input"
                placeholder="nama@email.com"
            >
            @error('email')
                <p class="mt-1 text-sm" style="color:#c0392b;">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="password" class="inst-auth-label">Kata sandi <span style="color:#e74c3c;">*</span></label>
            <input
                id="password"
                name="password"
                type="password"
                required
                autocomplete="current-password"
                class="inst-auth-input"
                placeholder="••••••••"
            >
        </div>
        <div class="flex items-center justify-between">
            <label class="inline-flex items-center gap-2 text-sm" style="color:#1a4a6b;">
                <input type="checkbox" name="remember" value="1" class="inst-auth-check h-4 w-4 rounded border">
                Ingat saya
            </label>
        </div>
        <button type="submit" class="inst-auth-btn">
            Masuk
        </button>
    </form>
@endsection
