<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CRUD') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="m-0 flex min-h-screen flex-col bg-[#eef2f7] font-sans text-gray-800 antialiased">
    @include('layouts.partials.navbar')

    <main class="flex min-h-0 flex-1 flex-col bg-white">
        <header class="flex shrink-0 flex-col gap-3 overflow-visible border-b border-gray-200 px-6 py-4">
            <div class="flex items-center gap-4">
                <div class="min-w-0 flex-1">
                    @hasSection('subtitle')
                        <p class="mb-0.5 text-xs font-semibold uppercase tracking-wide text-gray-400">@yield('subtitle')</p>
                    @endif
                    <h1 class="m-0 text-[1.35rem] font-semibold text-gray-800">@yield('module', 'Usuarios')</h1>
                </div>

                @hasSection('header-search')
                    <div class="w-full max-w-sm shrink-0">
                        @yield('header-search')
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-3">
                <div class="flex flex-wrap items-center justify-start gap-2">
                    @yield('actions')
                </div>

                <div class="flex justify-center">
                    @yield('header-center')
                </div>

                <div class="flex justify-end">
                    @hasSection('header-right')
                        @yield('header-right')
                    @endif
                </div>
            </div>
        </header>

        <section class="min-h-0 flex-1 overflow-auto px-6 py-5">
            @yield('content')
        </section>
    </main>

    @include('layouts.partials.footer')

    @stack('scripts')
</body>
</html>
