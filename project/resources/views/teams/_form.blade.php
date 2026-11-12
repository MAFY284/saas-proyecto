@csrf

@if ($team->exists)
    @method('PUT')
@endif

@php
    $fieldClass = 'w-full rounded-lg border border-gray-300 bg-white px-[0.7rem] py-[0.55rem] font-sans focus:border-blue-600 focus:outline focus:outline-2 focus:outline-offset-1 focus:outline-blue-600';
    
    $hasLogo = $team->exists && !empty($team->logo_url);
    $logoUrl = '';
    
    if ($hasLogo) {
        // Limpiamos la ruta por si se guardó con 'public/' o '/storage/' para que no choque
        $cleanPath = str_replace(['public/', '/storage/'], '', $team->logo_url);
        $logoUrl = asset('storage/' . $cleanPath);
    }
@endphp

<div class="grid grid-cols-1 gap-x-8 gap-y-5 md:grid-cols-2 xl:grid-cols-3">
    <!-- Nombre del equipo -->
    <div class="md:col-span-2 xl:col-span-2">
        <label for="name" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Nombre del equipo</label>
        <input
            id="name"
            name="name"
            type="text"
            value="{{ old('name', $team->name) }}"
            required
            placeholder="Ej. Deportivo Apatzingán"
            class="{{ $fieldClass }}"
        >
        @error('name')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <!-- Nombre corto / Abreviatura -->
    <div>
        <label for="short_name" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Abreviatura / Código</label>
        <input
            id="short_name"
            name="short_name"
            type="text"
            value="{{ old('short_name', $team->short_name) }}"
            placeholder="Ej. APT"
            class="{{ $fieldClass }}"
        >
        @error('short_name')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <!-- Localidad -->
    <div>
        <label for="locality_id" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Localidad</label>
        <select id="locality_id" name="locality_id" required class="{{ $fieldClass }}">
            <option value="" disabled hidden>Selecciona una localidad</option>
            @foreach ($localities as $locality)
                <option value="{{ $locality->id }}" @selected(old('locality_id', $team->locality_id) == $locality->id)>
                    {{ $locality->name }}
                </option>
            @endforeach
        </select>
        @error('locality_id')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <!-- Correo de contacto -->
    <div>
        <label for="contact_email" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Correo de contacto</label>
        <input
            id="contact_email"
            name="contact_email"
            type="email"
            value="{{ old('contact_email', $team->contact_email) }}"
            placeholder="correo@ejemplo.com"
            class="{{ $fieldClass }}"
        >
        @error('contact_email')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <!-- Teléfono de contacto -->
    <div>
        <label for="contact_phone" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Teléfono de contacto</label>
        <input
            id="contact_phone"
            name="contact_phone"
            type="text"
            value="{{ old('contact_phone', $team->contact_phone) }}"
            placeholder="4431234567"
            class="{{ $fieldClass }}"
        >
        @error('contact_phone')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <!-- Estado (Activo / Inactivo) -->
    <div>
        <label for="is_active" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Estado</label>
        <select id="is_active" name="is_active" required class="{{ $fieldClass }}">
            <option value="1" @selected(old('is_active', $team->exists ? $team->is_active : true) == 1)>Activo</option>
            <option value="0" @selected(old('is_active', $team->exists ? $team->is_active : true) == 0)>Inactivo</option>
        </select>
        @error('is_active')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <!-- Zona Interactiva de Carga de Logo -->
    <div class="md:col-span-2 xl:col-span-3">
        <label class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Logo del equipo</label>
        
        <div id="drop-zone" class="relative border-2 border-dashed border-gray-300 rounded-lg p-5 flex flex-col items-center justify-center transition bg-gray-50 overflow-hidden">
            <!-- Input file oculto -->
            <input
                id="logo_url"
                name="logo_url"
                type="file"
                accept="image/*"
                class="hidden"
            >

            <!-- Instrucciones iniciales (se muestran si NO hay imagen) -->
            <div id="upload-prompt" class="text-center py-4 {{ $hasLogo ? 'hidden' : '' }}">
                <svg class="mx-auto h-10 w-10 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <p class="text-sm text-gray-600 font-medium mb-1">
                    Arrastra y suelta tu imagen aquí o selecciona una
                </p>
                <button type="button" id="select-file-btn" class="inline-flex items-center justify-center rounded-lg bg-white border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 cursor-pointer">
                    Seleccionar archivo
                </button>
                <p class="text-xs text-gray-400 mt-2">PNG, JPG, GIF hasta 2MB</p>
            </div>

            <!-- Contenedor de Previsualización (se muestra si hay imagen) -->
            <div id="preview-container" class="{{ $hasLogo ? 'flex' : 'hidden' }} flex-col items-center">
                <img
                    id="image-preview"
                    src="{{ $hasLogo ? $logoUrl : '#' }}"
                    alt="Vista previa del logo"
                    style="height: 85px; width: 85px; object-fit: contain;"
                    class="rounded-md border border-gray-200 bg-white shadow-sm mb-1.5"
                    onerror="this.src='https://via.placeholder.com/85?text=Error+Img'"
                >
                <p id="file-name" class="text-xs text-gray-500 font-medium truncate max-w-xs mb-1.5">
                    {{ $hasLogo ? 'Logo actual guardado' : '' }}
                </p>
                
                <div>
                    <button type="button" id="change-image-btn" class="text-xs text-blue-600 hover:text-blue-800 font-semibold cursor-pointer">
                        Cambiar imagen
                    </button>
                </div>
            </div>
        </div>

        @error('logo_url')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message %></span>
        @enderror
    </div>
</div>

<!-- Script de control interactivo -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const fileInput = document.getElementById('logo_url');
        const uploadPrompt = document.getElementById('upload-prompt');
        const previewContainer = document.getElementById('preview-container');
        const imagePreview = document.getElementById('image-preview');
        const fileName = document.getElementById('file-name');
        const selectBtn = document.getElementById('select-file-btn');
        const changeBtn = document.getElementById('change-image-btn');
        const dropZone = document.getElementById('drop-zone');

        if (selectBtn) {
            selectBtn.addEventListener('click', function () {
                fileInput.click();
            });
        }

        if (changeBtn) {
            changeBtn.addEventListener('click', function (e) {
                e.preventDefault();
                fileInput.click();
            });
        }

        fileInput.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    imagePreview.src = e.target.result;
                    fileName.textContent = file.name;
                    uploadPrompt.classList.add('hidden');
                    previewContainer.classList.remove('hidden');
                    previewContainer.classList.add('flex');
                }
                reader.readAsDataURL(file);
            }
        });

        dropZone.addEventListener('dragover', function (e) {
            e.preventDefault();
            dropZone.classList.add('border-blue-600', 'bg-blue-50/50');
        });

        dropZone.addEventListener('dragleave', function (e) {
            e.preventDefault();
            dropZone.classList.remove('border-blue-600', 'bg-blue-50/50');
        });

        dropZone.addEventListener('drop', function (e) {
            e.preventDefault();
            dropZone.classList.remove('border-blue-600', 'bg-blue-50/50');
            
            const file = e.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) {
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                fileInput.files = dataTransfer.files;

                const reader = new FileReader();
                reader.onload = function (e) {
                    imagePreview.src = e.target.result;
                    fileName.textContent = file.name;
                    uploadPrompt.classList.add('hidden');
                    previewController.classList.remove('hidden');
                    previewContainer.classList.add('flex');
                }
                reader.readAsDataURL(file);
            }
        });
    });
</script>