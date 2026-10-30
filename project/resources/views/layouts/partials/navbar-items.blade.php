@foreach ($items as $item)
    @if (strtoupper($item['method'] ?? 'GET') === 'POST')
        <form method="POST" action="{{ $item['url'] }}" class="w-full">
            @csrf
            <button
                type="submit"
                class="w-full cursor-pointer rounded-md border-0 bg-transparent px-3 py-2 text-left text-[0.9rem] text-gray-300 hover:text-white md:hover:bg-gray-700"
            >
                {{ $item['label'] }}
            </button>
        </form>
    @else
        <a
            href="{{ $item['url'] }}"
            class="rounded-md px-3 py-2 text-[0.9rem] no-underline md:hover:bg-gray-700 {{ $item['is_active'] ? 'text-white' : 'text-gray-300 hover:text-white' }}"
        >
            {{ $item['label'] }}
        </a>
    @endif
@endforeach
