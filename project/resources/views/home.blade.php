<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Inicio') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="m-0 flex min-h-screen flex-col bg-[#eef2f7] font-sans text-gray-800 antialiased">
    @include('layouts.partials.navbar')

    <main class="flex min-h-0 flex-1 flex-col bg-white">
        @yield('content')
    </main>

    @include('layouts.partials.footer')

    @stack('scripts')
</body>
</html>
