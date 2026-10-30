@props([
    'user',
    'size' => 'md',
])

@php
    $sizes = [
        'sm' => 'h-8 w-8 text-[0.7rem]',
        'md' => 'h-9 w-9 text-xs',
        'lg' => 'h-16 w-16 text-lg',
    ];
    $box = $sizes[$size] ?? $sizes['md'];
@endphp

@if ($user->avatarUrl())
    <img
        src="{{ $user->avatarUrl() }}"
        alt="{{ $user->name }}"
        class="{{ $box }} rounded-full object-cover"
    >
@else
    <span class="inline-flex {{ $box }} shrink-0 items-center justify-center rounded-full bg-blue-600 font-semibold text-white">
        {{ $user->initials() }}
    </span>
@endif
