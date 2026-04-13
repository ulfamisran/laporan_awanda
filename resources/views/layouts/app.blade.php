@php
    $u = auth()->user();
    $u->loadMissing('roles');
    $initialBits = collect(preg_split('/\s+/', trim($u->name)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
    $initials = $initialBits !== '' ? $initialBits : 'U';
    $roleLabel = $u->roles->first()
        ? str_replace('_', ' ', $u->roles->first()->name)
        : 'Pengguna';
@endphp
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dasbor') — {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Tanpa plugin forms: bentrok dengan gaya form institusional (border #d4e8f4, fokus #4a9b7a). --}}
    <script src="https://cdn.tailwindcss.com/3.4.17?plugins=typography"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui'],
                        display: ['"Plus Jakarta Sans"', 'Inter', 'sans-serif'],
                    },
                },
            },
        };
    </script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.tailwindcss.css">

    <style>
        html, body { height: 100%; margin: 0; }
        * { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #e8f1f8; }

        .sidebar-item {
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }
        .sidebar-item::before {
            content: '';
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 3px;
            border-radius: 0 3px 3px 0;
            background: transparent;
            transition: background 0.25s;
        }
        .sidebar-item.active::before { background: #4a9b7a; }
        .sidebar-item:hover { background: rgba(255,255,255,0.08); }
        .sidebar-item.active { background: rgba(74, 155, 122, 0.15); }

        .stat-card {
            transition: transform 0.25s cubic-bezier(0.4,0,0.2,1), box-shadow 0.25s, border-color 0.25s;
            border: 1px solid #d4e8f4;
        }
        .stat-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 40px rgba(26, 74, 107, 0.08);
            border-color: #4a9b7a;
        }

        .fade-in { animation: mbgFadeIn 0.5s ease both; }
        .fade-in-d1 { animation-delay: 0.08s; }
        .fade-in-d2 { animation-delay: 0.16s; }
        .fade-in-d3 { animation-delay: 0.24s; }
        .fade-in-d4 { animation-delay: 0.32s; }

        @keyframes mbgFadeIn {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .bar { border-radius: 4px 4px 0 0; transition: height 0.6s cubic-bezier(0.4,0,0.2,1); }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(26, 74, 107, 0.2); border-radius: 3px; }

        .mobile-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,0.5);
            z-index: 40; display: none;
        }
        .mobile-overlay.show { display: block; }

        @media (max-width: 768px) {
            .sidebar-wrap {
                position: fixed; left: -280px; top: 0; bottom: 0;
                z-index: 50; transition: left 0.3s ease;
            }
            .sidebar-wrap.open { left: 0; }
        }

        [x-cloak] { display: none !important; }
        .select2-container { width: 100% !important; }

        /* —— Institutional: panel, table, form (template) —— */
        .inst-panel {
            border-radius: 1rem;
            background: #ffffff;
            border: 1px solid #d4e8f4;
        }
        .inst-filter-panel {
            border-radius: 1rem;
            background: #ffffff;
            border: 1px solid #d4e8f4;
            padding: 1.25rem;
        }
        .inst-filter-panel .inst-select,
        .inst-filter-panel .inst-input {
            background-color: #ffffff;
        }
        .inst-page-title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
            font-size: 1.5rem;
            color: #1a4a6b;
        }
        @media (min-width: 640px) {
            .inst-page-title { font-size: 1.75rem; }
        }
        .inst-page-desc { font-size: 0.875rem; color: #7fa8c9; margin-top: 0.25rem; }
        .inst-label-filter {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #7fa8c9;
        }
        .inst-required { color: #e74c3c; }

        /* Halaman form (judul + lead di luar kartu — seperti template) */
        .inst-form-page {
            max-width: 42rem;
            margin-left: auto;
            margin-right: auto;
        }
        .inst-form-page > .inst-back:first-child { display: inline-block; margin-bottom: 1.5rem; }
        .inst-form-title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
            font-size: 1.5rem;
            line-height: 2rem;
            color: #1a4a6b;
            letter-spacing: -0.02em;
        }
        .inst-form-lead {
            font-size: 0.875rem;
            line-height: 1.4;
            color: #7fa8c9;
            margin-top: 0.25rem;
        }
        .inst-form-card {
            margin-top: 1.5rem;
            border-radius: 1rem;
            padding: 1.5rem;
            background: #ffffff;
            border: 1px solid #d4e8f4;
        }

        /* Kontrol form = template (label text-sm font-semibold mb-2) */
        .inst-label {
            display: block;
            font-size: 0.875rem;
            line-height: 1.25rem;
            font-weight: 600;
            color: #1a4a6b;
            margin-bottom: 0.5rem;
        }
        p.inst-label { margin-top: 0; }
        input.inst-input,
        select.inst-select,
        textarea.inst-input,
        textarea.inst-textarea {
            box-sizing: border-box;
            display: block;
            width: 100%;
            border-radius: 0.5rem;
            border: 1px solid #d4e8f4;
            padding: 0.625rem 1rem;
            font-size: 0.875rem;
            line-height: 1.25rem;
            color: #1a4a6b;
            background-color: #ffffff;
            outline: none;
            box-shadow: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        select.inst-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%237fa8c9' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.65rem center;
            background-size: 1rem;
            padding-right: 2.5rem;
        }
        input.inst-input::placeholder,
        textarea.inst-input::placeholder,
        textarea.inst-textarea::placeholder {
            color: #a8c0d4;
        }
        input.inst-input:hover,
        select.inst-select:hover,
        textarea.inst-input:hover,
        textarea.inst-textarea:hover {
            border-color: #c5dce8;
        }
        input.inst-input:focus,
        select.inst-select:focus,
        textarea.inst-input:focus,
        textarea.inst-textarea:focus {
            border-color: #4a9b7a;
            box-shadow: 0 0 0 1px #4a9b7a;
        }
        textarea.inst-input,
        .inst-textarea {
            resize: vertical;
            min-height: 5rem;
        }
        .inst-checkbox {
            margin-top: 0.25rem;
            border-radius: 0.25rem;
            border-color: #d4e8f4;
            color: #4a9b7a;
        }
        .inst-btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border-radius: 0.5rem;
            padding: 0.625rem 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #ffffff;
            background: #4a9b7a;
            border: none;
            cursor: pointer;
            transition: background 0.15s, opacity 0.15s;
        }
        .inst-btn-primary:hover { background: #3d8566; }
        .inst-btn-navy {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #ffffff;
            background: #1a4a6b;
            border: none;
            cursor: pointer;
            transition: opacity 0.15s;
        }
        .inst-btn-navy:hover { opacity: 0.92; }
        .inst-btn-outline {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            padding: 0.625rem 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #1a4a6b;
            background: #fff;
            border: 1px solid #d4e8f4;
            cursor: pointer;
            transition: background 0.15s;
            text-decoration: none;
        }
        .inst-btn-outline:hover { background: #f0f6fb; }
        a.inst-btn-outline.flex-1 { width: 100%; }
        /* Tombol sekunder (dipakai di filter / export; sebelumnya kelas ini tidak punya gaya) */
        .inst-btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            box-sizing: border-box;
            border-radius: 0.5rem;
            padding: 0.625rem 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            line-height: 1.25;
            color: #1a4a6b;
            background: #ffffff;
            border: 1px solid #d4e8f4;
            cursor: pointer;
            transition: background 0.15s, border-color 0.15s;
            text-decoration: none;
        }
        a.inst-btn-secondary,
        a.inst-btn-secondary:visited {
            color: #1a4a6b;
            text-decoration: none;
        }
        .inst-btn-secondary:hover {
            background: #f0f6fb;
            border-color: #c5dce8;
        }
        button.inst-btn-secondary {
            font-family: inherit;
        }
        .inst-link { font-size: 0.875rem; font-weight: 600; color: #4a9b7a; text-decoration: none; }
        .inst-link:hover { text-decoration: underline; }
        .inst-back { font-size: 0.875rem; font-weight: 600; color: #4a9b7a; }
        .inst-back:hover { color: #3d8566; }

        .inst-table { width: 100%; text-align: left; font-size: 0.875rem; border-collapse: collapse; }
        .inst-table thead tr {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
            color: #7fa8c9;
            border-bottom: 2px solid #e8f1f8;
        }
        .inst-table thead th { padding: 0 0 0.75rem 0; }
        .inst-table tbody td {
            padding: 1rem 0;
            color: #1a4a6b;
            border-bottom: 1px solid #e8f1f8;
            vertical-align: middle;
        }
        .inst-table tbody tr { transition: background 0.15s; }
        .inst-table tbody tr:hover { background: #f0f6fb; }
        .inst-table .inst-td-muted { color: #7fa8c9; }
        .inst-table .inst-td-mono { font-family: ui-monospace, monospace; font-size: 0.75rem; color: #7fa8c9; }

        .inst-dropzone {
            border: 2px dashed #d4e8f4;
            border-radius: 0.5rem;
            padding: 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: background 0.15s, border-color 0.15s;
        }
        .inst-dropzone:hover { background: #f0f6fb; border-color: #a8c4d8; }

        /* DataTables di dalam panel institusional */
        .inst-datatable-wrap .dataTables_wrapper { padding: 0.5rem 0.25rem; }
        .inst-datatable-wrap table.dataTable thead th {
            font-size: 0.75rem !important;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600 !important;
            color: #7fa8c9 !important;
            border-bottom: 2px solid #e8f1f8 !important;
            padding-bottom: 0.75rem !important;
            padding-top: 0.5rem !important;
        }
        .inst-datatable-wrap table.dataTable tbody td {
            border-color: #e8f1f8 !important;
            color: #1a4a6b !important;
            padding-top: 0.875rem !important;
            padding-bottom: 0.875rem !important;
        }
        .inst-datatable-wrap table.dataTable tbody tr:hover { background-color: #f0f6fb !important; }
        .inst-datatable-wrap .dataTables_length,
        .inst-datatable-wrap .dataTables_filter { color: #7fa8c9; font-size: 0.875rem; }
        .inst-datatable-wrap .dataTables_length select,
        .inst-datatable-wrap .dataTables_filter input {
            border: 1px solid #d4e8f4 !important;
            border-radius: 0.5rem !important;
            color: #1a4a6b !important;
        }
        .inst-datatable-wrap .dataTables_paginate .paginate_button {
            border-radius: 0.375rem !important;
        }
        .inst-datatable-wrap .dataTables_paginate .paginate_button.current {
            background: #1a4a6b !important;
            color: #fff !important;
            border-color: #1a4a6b !important;
        }
    </style>

    @stack('styles')
</head>
<body class="h-full antialiased" x-data="{ sidebarOpen: false }">
    <div id="app" class="flex h-full min-h-0 w-full" style="background:#e8f1f8;">
        <div
            class="mobile-overlay md:hidden"
            :class="sidebarOpen ? 'show' : ''"
            @click="sidebarOpen = false"
            x-cloak
        ></div>

        <div
            id="sidebarWrap"
            class="sidebar-wrap flex-shrink-0"
            :class="sidebarOpen ? 'open' : ''"
        >
            <aside
                id="sidebar"
                class="flex h-full w-[260px] flex-col"
                style="background:#1a4a6b; color:#b8d4e8;"
            >
                <div class="flex items-center gap-3 border-b border-white/10 px-6 pb-6 pt-8">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl" style="background:#4a9b7a;">
                        <i data-lucide="shield-check" class="text-white" style="width:22px;height:22px;"></i>
                    </div>
                    <div>
                        <div class="text-sm font-bold tracking-tight text-white" style="font-family:'Plus Jakarta Sans',sans-serif;">
                            {{ config('app.name', 'Dapur MBG') }}
                        </div>
                        <div class="text-[10px]" style="color:#7fa8c9;">
                            Sistem Dapur MBG
                        </div>
                    </div>
                </div>

                <div class="mb-2 px-6 pt-4 text-[10px] font-semibold uppercase tracking-[0.15em]" style="color:#5a7a99;">
                    Menu
                </div>

                <div class="flex-1 overflow-y-auto px-2 pb-4">
                    @include('layouts.partials.sidebar')
                </div>

                <div class="mx-3 mb-4 flex items-center gap-3 rounded-xl px-4 py-4" style="background:rgba(74, 155, 122, 0.1);">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm font-semibold text-white" style="background:#4a9b7a;">
                        {{ $initials }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="truncate text-sm font-medium text-white">{{ $u->name }}</div>
                        <div class="truncate text-[11px]" style="color:#7fa8c9;">{{ $roleLabel }}</div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                        @csrf
                        <button type="submit" class="rounded-lg p-2 transition hover:bg-white/10" title="Keluar" aria-label="Keluar">
                            <i data-lucide="log-out" style="width:16px;height:16px;color:#7fa8c9;"></i>
                        </button>
                    </form>
                </div>
            </aside>
        </div>

        <div class="flex h-full min-h-0 min-w-0 flex-1 flex-col overflow-auto">
            <header class="flex shrink-0 flex-wrap items-center justify-between gap-4 border-b px-6 py-5 md:px-8" style="background:#ffffff; border-color:#d4e8f4;">
                <div class="flex min-w-0 flex-1 items-center gap-4">
                    <button
                        type="button"
                        class="rounded-lg p-2 md:hidden"
                        style="background:#f0f6fb;"
                        @click="sidebarOpen = true"
                        aria-label="Buka menu"
                    >
                        <i data-lucide="menu" style="width:20px;height:20px;color:#1a4a6b;"></i>
                    </button>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight md:text-3xl" style="font-family:'Plus Jakarta Sans',sans-serif;color:#1a4a6b;">
                            @yield('title', 'Dasbor')
                        </h1>
                        <p class="mt-1 text-xs" style="color:#7fa8c9;">
                            @hasSection('header_subtitle')
                                @yield('header_subtitle')
                            @else
                                Selamat datang kembali, {{ $u->name }}
                            @endif
                        </p>
                    </div>
                </div>
                <div class="flex min-w-0 flex-1 flex-col items-stretch gap-3 sm:max-w-xl sm:flex-none sm:flex-row sm:items-center sm:justify-end">
                    @if ($periodeToolbar['visible'] ?? false)
                        <form
                            method="POST"
                            action="{{ route('periode.pilih') }}"
                            class="flex w-full min-w-0 items-center gap-2 sm:w-auto sm:max-w-sm"
                        >
                            @csrf
                            <label for="header-periode-id" class="shrink-0 text-xs font-semibold uppercase tracking-wide" style="color:#7fa8c9;">
                                Periode
                            </label>
                            <select
                                id="header-periode-id"
                                name="periode_id"
                                class="inst-select min-w-0 flex-1 py-2 text-sm"
                                onchange="this.form.submit()"
                                title="Pilih periode laporan"
                            >
                                @foreach ($periodeToolbar['options'] as $p)
                                    <option
                                        value="{{ $p->id }}"
                                        @selected($periodeToolbar['current'] && (int) $periodeToolbar['current']->id === (int) $p->id)
                                    >
                                        {{ $p->labelRingkas() }}
                                        @if ($p->status === \App\Enums\StatusAktif::Aktif)
                                            — Aktif
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    @endif
                    <div class="hidden items-center gap-3 sm:flex">
                        <div class="flex items-center gap-2 rounded-lg px-4 py-2" style="background:#f0f6fb;">
                            <i data-lucide="search" style="width:16px;height:16px;color:#7fa8c9;"></i>
                            <span class="text-sm" style="color:#7fa8c9;">Panel operasional</span>
                        </div>
                        <div class="relative rounded-lg p-2.5" style="background:#f0f6fb;" aria-hidden="true">
                            <i data-lucide="bell" style="width:18px;height:18px;color:#1a4a6b;"></i>
                            <span class="absolute right-1.5 top-1.5 h-2 w-2 rounded-full" style="background:#4a9b7a;"></span>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 space-y-6 overflow-auto px-6 py-8 md:px-8">
                <div id="flash-toast" class="sr-only" aria-live="polite"></div>

                @if ($errors->any())
                    <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800" style="border-color:#fecaca;">
                        <p class="font-semibold">Terjadi kesalahan:</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/lucide@0.263.0/dist/umd/lucide.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

    <script>
        window.mbgConfirmDelete = function (options) {
            return Swal.fire({
                title: options.title || 'Hapus data?',
                text: options.text || 'Tindakan ini tidak dapat dibatalkan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#4a9b7a',
                cancelButtonColor: '#64748b',
                confirmButtonText: options.confirmText || 'Ya, hapus',
                cancelButtonText: options.cancelText || 'Batal',
                reverseButtons: true,
            });
        };

        function mbgInitLucide() {
            if (window.lucide && typeof lucide.createIcons === 'function') {
                lucide.createIcons();
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            mbgInitLucide();
            setTimeout(mbgInitLucide, 80);

            const notyf = new Notyf({
                duration: 4500,
                ripple: true,
                dismissible: true,
                position: { x: 'right', y: 'top' },
                types: [
                    { type: 'warning', background: '#d97706', className: 'notyf__toast--warning', icon: false },
                    { type: 'info', background: '#1a4a6b', className: 'notyf__toast--info', icon: false },
                ],
            });
            window.NotyfInstance = notyf;

            @if (session('success'))
                notyf.success(@json(session('success')));
            @endif
            @if (session('error'))
                notyf.error(@json(session('error')));
            @endif
            @if (session('warning'))
                notyf.open({ type: 'warning', message: @json(session('warning')) });
            @endif
            @if (session('info'))
                notyf.open({ type: 'info', message: @json(session('info')) });
            @endif

            if (window.jQuery && jQuery.fn.select2) {
                jQuery('.select2').select2({ width: '100%', language: { noResults: () => 'Tidak ada hasil' } });
            }
            if (window.flatpickr) {
                document.querySelectorAll('.flatpickr').forEach((el) => {
                    flatpickr(el, { dateFormat: 'd/m/Y', locale: flatpickr.l10ns.id });
                });
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
