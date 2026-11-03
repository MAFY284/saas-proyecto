@php
    $from = $paginator->firstItem() ?? 0;
    $to = $paginator->lastItem() ?? 0;
    $total = $paginator->total();
    $button = 'inline-flex h-8 w-8 items-center justify-center rounded-lg border text-sm font-semibold no-underline';
@endphp

<div class="flex items-center gap-2 text-sm text-gray-600">
    <span class="tabular-nums">{{ $from }}/{{ $to }} de {{ $total }}</span>

    @if ($paginator->onFirstPage())
        <span class="{{ $button }} cursor-default border-gray-200 text-gray-300" aria-disabled="true">&lt;</span>
    @else
        <a
            href="{{ $paginator->previousPageUrl() }}"
            rel="prev"
            aria-label="Anterior"
            class="{{ $button }} border-gray-300 text-gray-700 hover:bg-gray-50"
        >&lt;</a>
    @endif

    @if ($paginator->hasMorePages())
        <a
            href="{{ $paginator->nextPageUrl() }}"
            rel="next"
            aria-label="Siguiente"
            class="{{ $button }} border-gray-300 text-gray-700 hover:bg-gray-50"
        >&gt;</a>
    @else
        <span class="{{ $button }} cursor-default border-gray-200 text-gray-300" aria-disabled="true">&gt;</span>
    @endif
</div>
