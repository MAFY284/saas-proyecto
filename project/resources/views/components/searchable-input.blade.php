@props([
    'name',
    'id' => null,
    'value' => '',
    'options' => [],
    'placeholder' => 'Escribe o selecciona...',
    'addLabel' => 'Agregar',
    'help' => null,
    'required' => false,
    'allowCreate' => true,
])

@php
    $id = $id ?: $name;
    $value = old($name, $value);
    $options = collect($options)->filter(fn ($option) => filled($option))->unique()->values();
    $fieldClass = 'w-full rounded-lg border border-gray-300 bg-white py-[0.55rem] pr-10 pl-[0.7rem] font-sans focus:border-blue-600 focus:outline focus:outline-2 focus:outline-offset-1 focus:outline-blue-600';
@endphp

<div
    class="relative"
    data-searchable-input
    data-allow-create="{{ $allowCreate ? '1' : '0' }}"
>
    <input
        id="{{ $id }}"
        name="{{ $name }}"
        value="{{ $value }}"
        type="text"
        autocomplete="off"
        placeholder="{{ $placeholder }}"
        @if ($required) required @endif
        class="{{ $fieldClass }}"
        role="combobox"
        aria-autocomplete="list"
        aria-expanded="false"
        aria-controls="{{ $id }}-menu"
    >

    <button
        type="button"
        data-searchable-toggle
        class="absolute top-1/2 right-2 inline-flex h-7 w-7 -translate-y-1/2 cursor-pointer items-center justify-center rounded-md border-0 bg-transparent p-0 text-gray-400 hover:bg-gray-100 hover:text-gray-600"
        aria-label="Mostrar opciones"
        tabindex="-1"
    >
        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
        </svg>
    </button>

    <div
        id="{{ $id }}-menu"
        data-searchable-menu
        class="absolute z-30 mt-1 hidden max-h-60 w-full overflow-auto rounded-lg border border-gray-200 bg-white py-1 shadow-lg"
    >
        <div data-searchable-options>
            @foreach ($options as $option)
                <button
                    type="button"
                    data-searchable-option
                    class="block w-full cursor-pointer border-0 bg-transparent px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50"
                >
                    {{ $option }}
                </button>
            @endforeach
        </div>

        <button
            type="button"
            data-searchable-add
            class="hidden w-full cursor-pointer border-0 bg-transparent px-3 py-2 text-left text-sm font-semibold text-blue-700 hover:bg-blue-50"
        >
            {{ $addLabel }} "<span data-searchable-add-value></span>"
        </button>

        <p data-searchable-empty class="hidden px-3 py-2 text-sm text-gray-500">
            Sin resultados.
        </p>
    </div>
</div>

@if ($help)
    <span class="mt-1.5 block text-xs text-gray-500">{{ $help }}</span>
@endif

@error($name)
    <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
@enderror

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const closeSearchable = (root) => {
                    root.classList.remove('is-open');
                    root.querySelector('[data-searchable-menu]')?.classList.add('hidden');
                    root.querySelector('input')?.setAttribute('aria-expanded', 'false');
                };

                const closeAllSearchables = (except = null) => {
                    document.querySelectorAll('[data-searchable-input].is-open').forEach((root) => {
                        if (root !== except) {
                            closeSearchable(root);
                        }
                    });
                };

                const openSearchable = (root) => {
                    closeAllSearchables(root);
                    root.classList.add('is-open');
                    root.querySelector('[data-searchable-menu]')?.classList.remove('hidden');
                    root.querySelector('input')?.setAttribute('aria-expanded', 'true');
                };

                const filterSearchable = (root) => {
                    const input = root.querySelector('input');
                    const value = input ? input.value.trim() : '';
                    const term = value.toLocaleLowerCase();
                    const options = [...root.querySelectorAll('[data-searchable-option]')];
                    const allowCreate = root.dataset.allowCreate === '1';
                    const addButton = root.querySelector('[data-searchable-add]');
                    const addValue = root.querySelector('[data-searchable-add-value]');
                    const emptyMessage = root.querySelector('[data-searchable-empty]');

                    let matches = 0;

                    options.forEach((option) => {
                        const text = option.textContent.trim();
                        const matchesTerm = term === '' || text.toLocaleLowerCase().includes(term);

                        option.classList.toggle('hidden', !matchesTerm);

                        if (matchesTerm) {
                            matches += 1;
                        }
                    });

                    const exactMatch = options.some(
                        (option) => option.textContent.trim().toLocaleLowerCase() === term
                    );
                    const canAdd = allowCreate && value !== '' && !exactMatch;

                    addButton?.classList.toggle('hidden', !canAdd);

                    if (addValue) {
                        addValue.textContent = value;
                    }

                    emptyMessage?.classList.toggle('hidden', matches > 0 || canAdd);
                };

                const selectSearchable = (root, value) => {
                    const input = root.querySelector('input');

                    if (!input) {
                        return;
                    }

                    input.value = value;
                    closeSearchable(root);
                    input.focus();
                };

                document.querySelectorAll('[data-searchable-input]').forEach((root) => {
                    if (root.dataset.searchableReady === '1') {
                        return;
                    }

                    root.dataset.searchableReady = '1';

                    const input = root.querySelector('input');
                    const toggle = root.querySelector('[data-searchable-toggle]');
                    const addButton = root.querySelector('[data-searchable-add]');

                    root.querySelectorAll('[data-searchable-option]').forEach((option) => {
                        option.addEventListener('click', () => {
                            selectSearchable(root, option.textContent.trim());
                        });
                    });

                    addButton?.addEventListener('click', () => {
                        if (input && input.value.trim() !== '') {
                            selectSearchable(root, input.value.trim());
                        }
                    });

                    input?.addEventListener('focus', () => {
                        openSearchable(root);
                        filterSearchable(root);
                    });

                    input?.addEventListener('click', () => {
                        openSearchable(root);
                        filterSearchable(root);
                    });

                    input?.addEventListener('input', () => {
                        openSearchable(root);
                        filterSearchable(root);
                    });

                    toggle?.addEventListener('click', (event) => {
                        event.preventDefault();
                        const isOpen = root.classList.contains('is-open');

                        closeAllSearchables();

                        if (!isOpen) {
                            openSearchable(root);
                            filterSearchable(root);
                            input?.focus();
                        }
                    });
                });

                document.addEventListener('click', (event) => {
                    if (!event.target.closest('[data-searchable-input]')) {
                        closeAllSearchables();
                    }
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        closeAllSearchables();
                    }
                });
            });
        </script>
    @endpush
@endonce
