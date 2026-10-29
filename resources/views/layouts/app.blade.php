<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SKARP IoT Dashboard')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.ico') }}">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        surface: 'rgba(255,255,255,0.03)',
                        border:  'rgba(255,255,255,0.06)',
                    }
                }
            }
        }
    </script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Firebase JS SDK v10 (modular via CDN compat) -->
    <script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-database-compat.js"></script>

    @stack('styles')
</head>
<body class="min-h-screen bg-slate-100 text-slate-950 antialiased">

    @yield('content')

    @stack('scripts')
</body>
</html>
