@extends('layouts.app')

@section('title', 'Master Akun Dana')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="inst-page-title">Master akun dana</h2>
            <p class="inst-page-desc">Kode dan nama akun untuk saldo dana awal (hierarki buku kas).</p>
        </div>
        @if (auth()->user()->hasAnyRole(['super_admin', 'admin_pusat']))
            <a href="{{ route('master.akun-dana.create') }}" class="inst-btn-primary shrink-0">
                <i data-lucide="plus" class="h-[18px] w-[18px]"></i>
                Tambah akun
            </a>
        @endif
    </div>

    <div class="inst-panel overflow-hidden p-6">
        <div class="overflow-x-auto">
            <table class="inst-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama akun</th>
                        <th>Induk</th>
                        <th>Urutan</th>
                        <th>Grup</th>
                        @if (auth()->user()->hasAnyRole(['super_admin', 'admin_pusat']))
                            <th class="text-right">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $a)
                        @php
                            $depth = 0;
                            $p = $a->parent_id;
                            while ($p) {
                                $depth++;
                                $p = $items->firstWhere('id', $p)?->parent_id;
                            }
                        @endphp
                        <tr>
                            <td class="font-mono font-semibold">{{ $a->kode }}</td>
                            <td style="padding-left:{{ 4 + $depth * 14 }}px;">{{ $a->nama }}</td>
                            <td class="text-sm">{{ $a->parent?->kode ?? '—' }}</td>
                            <td class="font-mono">{{ $a->urutan }}</td>
                            <td>{{ $a->is_grup ? 'Ya' : 'Tidak' }}</td>
                            @if (auth()->user()->hasAnyRole(['super_admin', 'admin_pusat']))
                                <td class="text-right">
                                    <a href="{{ route('master.akun-dana.edit', $a) }}" class="text-xs font-semibold" style="color:#4a9b7a;">Ubah</a>
                                    <form method="POST" action="{{ route('master.akun-dana.destroy', $a) }}" class="ml-3 inline" onsubmit="return confirm('Hapus akun ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold" style="color:#c0392b;">Hapus</button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-sm" style="color:#7fa8c9;">Belum ada akun. Jalankan migrasi atau tambah akun.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        if (window.lucide) lucide.createIcons();
    </script>
@endpush
