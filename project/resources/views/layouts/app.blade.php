<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name')) — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="m-0 flex min-h-screen flex-col bg-[#eef2f7] font-sans text-gray-800 antialiased">
    @include('layouts.partials.navbar')

    <main class="flex flex-1 items-center justify-center px-4 py-10">
        <div class="w-full max-w-md rounded-[14px] bg-white px-7 py-8 text-center shadow-[0_12px_32px_rgba(17,24,39,0.08)]">
            @yield('content')
        </div>
    </main>

    @include('layouts.partials.footer')
</body>
</html>
