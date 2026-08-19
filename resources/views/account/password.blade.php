@extends('layouts.app')

@section('title', 'Ganti Kata Sandi')

@section('content')
    <div class="inst-form-page mx-auto max-w-xl">
        <a href="{{ route('dashboard') }}" class="inst-back">← Kembali</a>

        <h2 class="inst-form-title">Ganti kata sandi</h2>
        <p class="inst-form-lead">Perbarui kata sandi akun <strong>{{ auth()->user()->email }}</strong>. Masukkan kata sandi saat ini untuk verifikasi.</p>

        <div class="inst-form-card">
            <form method="POST" action="{{ route('akun.password.update') }}" class="inst-form space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="inst-label">Kata sandi saat ini <span class="inst-required">*</span></label>
                    <input type="password" name="current_password" id="current_password" required class="inst-input" autocomplete="current-password">
                    @error('current_password')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="inst-label">Kata sandi baru <span class="inst-required">*</span></label>
                    <input type="password" name="password" id="password" required class="inst-input" autocomplete="new-password" placeholder="Minimal 8 karakter">
                    @error('password')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="inst-label">Ulangi kata sandi baru <span class="inst-required">*</span></label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required class="inst-input" autocomplete="new-password">
                </div>

                <div class="flex flex-col gap-3 pt-4 sm:flex-row">
                    <a href="{{ route('dashboard') }}" class="inst-btn-outline flex-1 justify-center text-center">Batal</a>
                    <button type="submit" class="inst-btn-primary flex-1">Simpan kata sandi baru</button>
                </div>
            </form>
        </div>
    </div>
@endsection
