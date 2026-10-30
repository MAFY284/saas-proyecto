@csrf

@if ($user->exists)
    @method('PUT')
@endif

<div class="grid grid-cols-1 gap-x-8 gap-y-5 md:grid-cols-2 xl:grid-cols-3">
    <div>
        <label for="name" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Nombre</label>
        <input
            id="name"
            name="name"
            value="{{ old('name', $user->name) }}"
            required
            class="w-full rounded-lg border border-gray-300 px-[0.7rem] py-[0.55rem] font-sans focus:border-blue-600 focus:outline focus:outline-2 focus:outline-offset-1 focus:outline-blue-600"
        >
    </div>

    <div>
        <label for="email" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Email</label>
        <input
            id="email"
            name="email"
            type="email"
            value="{{ old('email', $user->email) }}"
            required
            class="w-full rounded-lg border border-gray-300 px-[0.7rem] py-[0.55rem] font-sans focus:border-blue-600 focus:outline focus:outline-2 focus:outline-offset-1 focus:outline-blue-600"
        >
    </div>

    <div>
        <label for="password" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Password</label>
        <input
            id="password"
            name="password"
            type="password"
            @unless ($user->exists) required @endunless
            autocomplete="new-password"
            class="w-full rounded-lg border border-gray-300 px-[0.7rem] py-[0.55rem] font-sans focus:border-blue-600 focus:outline focus:outline-2 focus:outline-offset-1 focus:outline-blue-600"
        >
        @if ($user->exists)
            <span class="mt-1.5 block text-xs text-gray-500">Déjalo vacío para conservar el password actual.</span>
        @endif
    </div>

    <div class="md:col-span-2 xl:col-span-3">
        <p class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Imagen de perfil</p>
        <p class="mb-3 text-xs text-gray-500">Arrastra una imagen al recuadro o haz clic para seleccionarla. JPG, PNG o WEBP. Máximo 2 MB.</p>
        <div data-image-slots>
            <label
                class="image-slot image-slot--avatar {{ $user->avatarUrl() ? 'has-image' : '' }}"
                data-image-slot
                data-slot-index="0"
            >
                <span class="image-slot__label">Foto</span>
                <img
                    class="image-slot__preview {{ $user->avatarUrl() ? '' : 'hidden' }}"
                    src="{{ $user->avatarUrl() }}"
                    alt=""
                >
                <span class="image-slot__placeholder {{ $user->avatarUrl() ? 'hidden' : '' }}">
                    Arrastra o haz clic
                </span>
                <input
                    id="avatar"
                    class="sr-only"
                    type="file"
                    name="avatar"
                    accept="image/*"
                >
            </label>
        </div>
    </div>
</div>
