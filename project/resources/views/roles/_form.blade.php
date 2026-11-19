@csrf

@if ($role->exists)
    @method('PUT')
@endif

@php
    $fieldClass = 'w-full rounded-lg border border-gray-300 bg-white px-[0.7rem] py-[0.55rem] font-sans focus:border-blue-600 focus:outline focus:outline-2 focus:outline-offset-1 focus:outline-blue-600';
@endphp

<div class="grid grid-cols-1 gap-x-8 gap-y-5 md:grid-cols-2 xl:grid-cols-3">
    <div>
        <label for="name" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Rol</label>
        <select
            id="name"
            name="name"
            required
            class="{{ $fieldClass }}"
            onchange="handleRolePreset(this.value)"
        >
            <option value="">-- Selecciona el rol --</option>
            <option value="Administrador" @selected(old('name', $role->name) === 'Administrador' || old('slug', $role->slug) === 'admin')>Administrador</option>
            <option value="Usuario" @selected(old('name', $role->name) === 'Usuario' || old('slug', $role->slug) === 'usuario')>Usuario</option>
            @if ($role->exists && !in_array($role->name, ['Administrador', 'Usuario']))
                <option value="{{ $role->name }}" selected>{{ $role->name }} (Personalizado)</option>
            @endif
        </select>
        @error('name')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="slug" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Slug identificador</label>
        <input
            id="slug"
            name="slug"
            value="{{ old('slug', $role->slug) }}"
            placeholder="admin / usuario"
            class="{{ $fieldClass }} bg-gray-50 text-gray-700 font-mono text-sm"
        >
        <span class="mt-1.5 block text-xs text-gray-500">Identificador del rol en el sistema.</span>
        @error('slug')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div class="flex items-end">
        <input type="hidden" name="is_active" value="0">
        <label class="inline-flex items-center gap-2 text-[0.9rem] font-semibold text-gray-700 pb-2 cursor-pointer">
            <input
                id="is_active"
                name="is_active"
                type="checkbox"
                value="1"
                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                @checked((string) old('is_active', $role->exists ? (int) $role->is_active : 1) === '1')
            >
            Rol activo
        </label>
        @error('is_active')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <div class="md:col-span-2 xl:col-span-3">
        <label for="description" class="mb-1.5 block text-[0.9rem] font-semibold text-gray-700">Descripción del rol</label>
        <input
            id="description"
            name="description"
            value="{{ old('description', $role->description) }}"
            placeholder="Ej: Acceso total con lectura y escritura..."
            class="{{ $fieldClass }}"
        >
        @error('description')
            <span class="mt-1.5 block text-xs text-red-700">{{ $message }}</span>
        @enderror
    </div>

    <!-- Sección de Permisos: Palomear Lectura o Escritura -->
    <div class="md:col-span-2 xl:col-span-3 mt-4 border-t border-gray-200 pt-6">
        <div class="mb-4">
            <h3 class="text-base font-semibold text-gray-800">Tipo de Permisos</h3>
            <p class="text-sm text-gray-500">
                Marca con las casillas de verificación si este rol tendrá permisos de <strong>Lectura</strong>, <strong>Escritura</strong> o ambos.
            </p>
        </div>

        <!-- Casillas de palomear: Lectura / Escritura -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <label class="relative flex cursor-pointer items-start rounded-xl border border-gray-200 bg-white p-4 shadow-sm hover:border-blue-400 hover:bg-blue-50/20 transition-all">
                <div class="flex h-5 items-center">
                    <input
                        type="checkbox"
                        id="can_read"
                        name="can_read"
                        value="1"
                        class="h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        @checked(old('can_read', old('type_read', $hasReadPermission ?? false)))
                    >
                </div>
                <div class="ml-3 text-sm">
                    <span class="font-semibold text-gray-900 flex items-center gap-1.5 text-base">
                        Permiso de Lectura
                    </span>
                    <p class="text-xs text-gray-500 mt-1">
                        Permite ver, consultar y listar registros e información en el sistema.
                    </p>
                </div>
            </label>

            <label class="relative flex cursor-pointer items-start rounded-xl border border-gray-200 bg-white p-4 shadow-sm hover:border-emerald-400 hover:bg-emerald-50/20 transition-all">
                <div class="flex h-5 items-center">
                    <input
                        type="checkbox"
                        id="can_write"
                        name="can_write"
                        value="1"
                        class="h-5 w-5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                        @checked(old('can_write', old('type_write', $hasWritePermission ?? false)))
                    >
                </div>
                <div class="ml-3 text-sm">
                    <span class="font-semibold text-gray-900 flex items-center gap-1.5 text-base">
                        Permiso de Escritura
                    </span>
                    <p class="text-xs text-gray-500 mt-1">
                        Permite crear, modificar, actualizar y eliminar registros en el sistema.
                    </p>
                </div>
            </label>
        </div>
    </div>
</div>

<script>
    function handleRolePreset(role) {
        const slugInput = document.getElementById('slug');
        const descInput = document.getElementById('description');
        const canRead   = document.getElementById('can_read');
        const canWrite  = document.getElementById('can_write');

        if (role === 'Administrador') {
            if (slugInput) slugInput.value = 'admin';
            if (descInput && !descInput.value) descInput.value = 'Acceso total con permisos de lectura y escritura.';
            if (canRead)  canRead.checked  = true;
            if (canWrite) canWrite.checked = true;
        } else if (role === 'Usuario') {
            if (slugInput) slugInput.value = 'usuario';
            if (descInput && !descInput.value) descInput.value = 'Usuario estándar con permisos de lectura.';
            if (canRead)  canRead.checked  = true;
            if (canWrite) canWrite.checked = false;
        }
    }
</script>
