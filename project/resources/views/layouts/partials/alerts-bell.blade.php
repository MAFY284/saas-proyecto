@php
    $navbarAlerts = collect();

    if (session('status')) {
        $navbarAlerts->push(['type' => 'success', 'text' => session('status')]);
    }

    if (session('error')) {
        $navbarAlerts->push(['type' => 'error', 'text' => session('error')]);
    }

    foreach ($errors->all() as $error) {
        $navbarAlerts->push(['type' => 'error', 'text' => $error]);
    }

    $alertCount = $navbarAlerts->count();
@endphp

<div
    class="group/alerts relative w-full md:w-auto {{ $alertCount ? 'is-open' : '' }}"
    data-submenu="click"
>
    <button
        type="button"
        class="submenu-toggle relative inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-full border-0 bg-transparent text-gray-300 hover:bg-gray-800 hover:text-white"
        aria-expanded="{{ $alertCount ? 'true' : 'false' }}"
        aria-controls="submenu-alertas"
        aria-haspopup="true"
        aria-label="Notificaciones"
    >
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.8 18.5a2.8 2.8 0 0 1-5.6 0M6.2 9.8a5.8 5.8 0 1 1 11.6 0c0 3.2.9 4.4 1.6 5.4.4.5.6 1.1.2 1.6-.3.4-.9.7-1.5.7H5.9c-.6 0-1.2-.3-1.5-.7-.4-.5-.2-1.1.2-1.6.7-1 1.6-2.2 1.6-5.4Z" />
        </svg>
        @if ($alertCount)
            <span class="absolute -top-0.5 -right-0.5 inline-flex min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[0.65rem] font-bold leading-4 text-white">
                {{ $alertCount > 9 ? '9+' : $alertCount }}
            </span>
        @endif
    </button>

    <div
        id="submenu-alertas"
        class="mt-2 hidden w-full flex-col group-[.is-open]/alerts:flex md:absolute md:top-full md:right-0 md:left-auto md:z-30 md:mt-1 md:w-80"
    >
        <div class="rounded-lg border border-gray-200 bg-white py-1 text-gray-800 shadow-lg">
            <p class="border-b border-gray-100 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-gray-400">
                Notificaciones
            </p>

            @forelse ($navbarAlerts as $alert)
                <p class="border-b border-gray-50 px-3 py-2 text-sm last:border-0 {{ $alert['type'] === 'success' ? 'text-emerald-700' : 'text-red-700' }}">
                    {{ $alert['text'] }}
                </p>
            @empty
                <p class="px-3 py-3 text-sm text-gray-500">No hay notificaciones.</p>
            @endforelse
        </div>
    </div>
</div>
