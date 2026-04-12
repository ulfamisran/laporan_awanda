<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Masuk') — {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com/3.4.17"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        mbg: {
                            50: '#ecfdf5',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                        },
                    },
                },
            },
        };
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: Inter, ui-sans-serif, system-ui, sans-serif; }
        .inst-auth-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #1a4a6b;
            margin-bottom: 0.5rem;
        }
        .inst-auth-input {
            box-sizing: border-box;
            width: 100%;
            border-radius: 0.5rem;
            border: 1px solid #d4e8f4;
            padding: 0.625rem 1rem;
            font-size: 0.875rem;
            color: #1a4a6b;
            background: #fff;
            outline: none;
        }
        .inst-auth-input:focus {
            border-color: #4a9b7a;
            box-shadow: 0 0 0 1px #4a9b7a;
        }
        .inst-auth-btn {
            display: flex;
            width: 100%;
            justify-content: center;
            border-radius: 0.5rem;
            padding: 0.625rem 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #fff;
            background: #4a9b7a;
            border: none;
            cursor: pointer;
            transition: background 0.15s;
        }
        .inst-auth-btn:hover { background: #3d8566; }
        .inst-auth-check {
            border-radius: 0.25rem;
            border-color: #d4e8f4;
            color: #4a9b7a;
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-[#1a4a6b] via-[#1e5a82] to-[#2a7090]">
    <div class="flex min-h-screen flex-col items-center justify-center px-4 py-10">
        @hasSection('brand')
            @yield('brand')
        @endif
        <div class="w-full max-w-md rounded-2xl border bg-white p-8 shadow-2xl" style="border-color:#d4e8f4;">
            @yield('content')
        </div>
        <p class="mt-8 text-center text-xs text-white/80">© {{ date('Y') }} {{ config('app.name') }}</p>
    </div>
</body>
</html>
