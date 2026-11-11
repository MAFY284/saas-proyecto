<nav id="navbar" class="group bg-gray-900 text-gray-50 md:flex md:items-center md:justify-between md:overflow-visible md:pr-7">
    <div class="flex items-center justify-between px-7 py-[0.8rem] md:pr-0">
        <a href="{{ route('home') }}" class="pr-2.5 text-[1.2rem] font-bold tracking-wide text-gray-50 no-underline hover:text-white">
            {{ config('app.name') }}
        </a>

        <button
            type="button"
            class="navbar-toggle flex h-[42px] w-[42px] flex-col items-center justify-center gap-[5px] rounded-lg border border-gray-700 bg-transparent p-0 md:hidden"
            aria-label="Abrir menú"
            aria-expanded="false"
            aria-controls="navbar-menu"
        >
            <span class="block h-0.5 w-5 bg-gray-50 transition duration-200 group-[.is-open]:translate-y-[7px] group-[.is-open]:rotate-45"></span>
            <span class="block h-0.5 w-5 bg-gray-50 transition duration-200 group-[.is-open]:opacity-0"></span>
            <span class="block h-0.5 w-5 bg-gray-50 transition duration-200 group-[.is-open]:-translate-y-[7px] group-[.is-open]:-rotate-45"></span>
        </button>
    </div>

    <div
        id="navbar-menu"
        class="hidden flex-col items-stretch gap-4 border-t border-gray-800 px-7 pb-4 group-[.is-open]:flex md:flex md:flex-1 md:flex-row md:items-center md:justify-between md:gap-6 md:overflow-visible md:border-0 md:p-0"
    >
        <div class="flex flex-col items-start gap-3 md:flex-row md:items-center md:gap-4">
            @foreach ($mainMenus as $group)
                @if ($group['items'] === [])
                    <a
                        href="{{ $group['url'] }}"
                        class="inline-flex w-full items-center font-sans text-[0.95rem] font-normal leading-6 no-underline md:w-auto {{ $group['is_active'] ? 'text-white' : 'text-gray-300 hover:text-white' }}"
                    >
                        {{ $group['label'] }}
                    </a>
                @else
                    <div class="group/nav relative w-full md:w-auto" data-submenu>
                        <button
                            type="button"
                            class="submenu-toggle inline-flex w-full cursor-pointer items-center border-0 bg-transparent p-0 text-left font-sans text-[0.95rem] font-normal leading-6 md:w-auto {{ $group['is_active'] ? 'text-white' : 'text-gray-300 hover:text-white' }}"
                            aria-expanded="false"
                            aria-controls="submenu-{{ $group['id'] }}"
                            aria-haspopup="true"
                        >
                            {{ $group['label'] }}
                        </button>
                        <div
                            id="submenu-{{ $group['id'] }}"
                            class="mt-2 hidden w-full flex-col group-[.is-open]/nav:flex md:absolute md:top-full md:left-0 md:z-20 md:mt-0 md:min-w-[11rem]"
                        >
                            <div class="flex flex-col pl-3 md:rounded-lg md:bg-gray-800 md:py-1 md:pl-0 md:shadow-lg">
                                @include('layouts.partials.navbar-items', ['items' => $group['items']])
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="flex flex-col items-start gap-3 md:flex-row md:items-center md:gap-4">
            @auth
                @include('layouts.partials.alerts-bell')

                @foreach ($accountMenus as $group)
                    <div class="group/account relative w-full md:w-auto" data-submenu="click">
                        <button
                            type="button"
                            class="submenu-toggle inline-flex w-full cursor-pointer items-center gap-2 border-0 bg-transparent p-0 text-left md:w-auto"
                            aria-expanded="false"
                            aria-controls="submenu-{{ $group['id'] }}"
                            aria-haspopup="true"
                        >
                            <x-user-avatar :user="auth()->user()" size="md" />
                            <span class="max-w-[10rem] truncate text-[0.9rem] font-semibold {{ $group['is_active'] ? 'text-white' : 'text-gray-200' }}">
                                {{ auth()->user()->name }}
                            </span>
                            <svg class="h-4 w-4 shrink-0 text-gray-400 transition group-[.is-open]/account:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div
                            id="submenu-{{ $group['id'] }}"
                            class="mt-2 hidden w-full flex-col group-[.is-open]/account:flex md:absolute md:top-full md:right-0 md:left-auto md:z-30 md:mt-1 md:min-w-[13rem]"
                        >
                            <div class="flex flex-col pl-3 md:rounded-lg md:bg-gray-800 md:py-1 md:pl-0 md:shadow-lg">
                                @include('layouts.partials.navbar-items', ['items' => $group['items']])
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                @foreach ($guestMenus as $item)
                    <a
                        href="{{ $item['url'] }}"
                        @class([
                            'w-full text-[0.95rem] no-underline md:w-auto' => ($item['style'] ?? 'link') !== 'button',
                            'inline-block w-full rounded-lg bg-blue-600 px-[0.85rem] py-[0.4rem] text-center text-[0.9rem] text-white no-underline hover:bg-blue-700 md:w-auto' => ($item['style'] ?? 'link') === 'button',
                            'text-white' => ($item['style'] ?? 'link') !== 'button' && $item['is_active'],
                            'text-gray-300 hover:text-white' => ($item['style'] ?? 'link') !== 'button' && ! $item['is_active'],
                        ])
                    >
                        {{ $item['label'] }}
                    </a>
                @endforeach
            @endauth
        </div>
    </div>
</nav>
