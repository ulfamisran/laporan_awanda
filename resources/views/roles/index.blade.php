@extends('layouts.app')

@section('title', 'Peran')

@section('content')
    <div class="mb-6">
        <h2 class="inst-page-title">Daftar peran</h2>
        <p class="inst-page-desc">Peran bersifat tetap. Jumlah pengguna per peran ditampilkan di bawah.</p>
    </div>

    <div class="inst-panel overflow-hidden p-6">
        <div class="overflow-x-auto">
            <table class="inst-table">
                <thead>
                    <tr>
                        <th class="pr-4">Peran</th>
                        <th class="pr-4">Badge</th>
                        <th class="text-right">Jumlah pengguna</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($roles as $role)
                        @php
                            $pillBg = match ($role->name) {
                                'super_admin' => '#d4f0e8',
                                'admin_pusat' => '#e3f2fd',
                                default => '#f0f4f8',
                            };
                            $pillFg = match ($role->name) {
                                'super_admin' => '#2d7a60',
                                'admin_pusat' => '#1565c0',
                                default => '#1a4a6b',
                            };
                        @endphp
                        <tr>
                            <td class="pr-4 font-medium">{{ str_replace('_', ' ', $role->name) }}</td>
                            <td class="pr-4">
                                <span class="rounded-full px-3 py-1 text-[11px] font-semibold" style="background:{{ $pillBg }};color:{{ $pillFg }};">
                                    {{ $role->name }}
                                </span>
                            </td>
                            <td class="inst-td-muted text-right font-medium">{{ $role->users_count }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
