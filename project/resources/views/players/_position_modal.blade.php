<dialog
    id="position-modal"
    aria-labelledby="position-modal-title"
    class="overflow-y-auto rounded-xl border border-gray-200 bg-white p-0 shadow-2xl backdrop:bg-black/50"
    style="position: fixed; inset: 0; margin: auto; width: min(92vw, 480px); max-height: 85vh;"
>
    <div class="border-b border-gray-200 px-6 py-5">
        <h2 id="position-modal-title" class="text-lg font-semibold text-gray-900">
            Administrar posiciones
        </h2>
        <p class="mt-1 text-sm text-gray-500">
            Agrega y administra posiciones de juego.
        </p>
    </div>

    <div class="px-6 py-5">
        <label for="new-position-name" class="mb-2 block text-sm font-semibold text-gray-700">
            Nombre de la posición
        </label>
        <input
            type="text"
            id="new-position-name"
            maxlength="255"
            placeholder="Ej. Delantero centro"
            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 focus:border-blue-600 focus:outline focus:outline-2 focus:outline-offset-1 focus:outline-blue-600"
        >
        <p id="position-error" role="alert" class="mt-2 hidden text-xs text-red-700"></p>

        <div class="mt-4 flex justify-end">
            <button
                type="button"
                id="save-position"
                disabled
                class="cursor-pointer rounded-lg border-0 bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-gray-800 disabled:cursor-not-allowed disabled:opacity-50"
            >
                Guardar posición
            </button>
        </div>

        <div class="mt-6 border-t border-gray-200 pt-5">
            <h3 class="mb-3 text-sm font-semibold text-gray-800">
                Posiciones registradas
            </h3>
            <div id="positions-list" class="max-h-48 space-y-2 overflow-y-auto">
                @forelse ($positions as $position)
                    <div
                        data-position-row="{{ $position->id }}"
                        class="flex items-center justify-between gap-3 rounded-lg border border-gray-200 bg-gray-50 p-3"
                    >
                        <span data-position-name-label class="text-sm font-medium text-gray-700">
                            {{ $position->name }}
                        </span>
                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                class="edit-position-btn text-sm font-medium text-blue-600 hover:text-blue-800"
                                data-position-id="{{ $position->id }}"
                                data-position-name="{{ $position->name }}"
                            >
                                Editar
                            </button>
                            <button
                                type="button"
                                class="delete-position-btn text-sm font-medium text-red-600 hover:text-red-800 disabled:opacity-50"
                                data-position-id="{{ $position->id }}"
                            >
                                Eliminar
                            </button>
                        </div>
                    </div>
                @empty
                    <p
                        id="positions-empty"
                        class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-5 text-center text-sm text-gray-500"
                    >
                        No hay posiciones registradas.
                    </p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="flex justify-end border-t border-gray-200 px-6 py-4">
        <button
            type="button"
            id="close-position-modal"
            class="cursor-pointer rounded-lg border border-gray-300 bg-white px-5 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
        >
            Cerrar
        </button>
    </div>
</dialog>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('position-modal');
    const openButton = document.getElementById('open-position-modal');
    const closeButton = document.getElementById('close-position-modal');
    const saveButton = document.getElementById('save-position');
    const nameInput = document.getElementById('new-position-name');
    const positionSelect = document.getElementById('position_id');
    const errorMessage = document.getElementById('position-error');
    const positionsList = document.getElementById('positions-list');

    if (!modal || !openButton || !closeButton || !saveButton ||
        !nameInput || !positionSelect || !errorMessage || !positionsList) {
        return;
    }

    const storeUrl = @json(route('positions.store'));
    const updateUrl = @json(route('positions.update', ['id' => '__ID__']));
    const deleteUrl = @json(route('positions.destroy', ['id' => '__ID__']));
    const csrfToken = @json(csrf_token());

    let saving = false;
    let editingId = null;

    function showError(message) {
        errorMessage.textContent = message;
        errorMessage.classList.toggle('hidden', !message);
    }

    function updateSaveButton() {
        saveButton.disabled = saving || nameInput.value.trim().length === 0;
    }

    function resetEditor() {
        editingId = null;
        nameInput.value = '';
        saveButton.textContent = 'Guardar posición';
        showError('');
        updateSaveButton();
    }

    function showEmptyStateIfNeeded() {
        if (positionsList.querySelector('[data-position-row]')) return;
        if (document.getElementById('positions-empty')) return;

        const message = document.createElement('p');
        message.id = 'positions-empty';
        message.className =
            'rounded-lg border border-gray-200 bg-gray-50 px-4 py-5 text-center text-sm text-gray-500';
        message.textContent = 'No hay posiciones registradas.';
        positionsList.appendChild(message);
    }

    function addPositionRow(position) {
        document.getElementById('positions-empty')?.remove();

        const row = document.createElement('div');
        row.dataset.positionRow = String(position.id);
        row.className =
            'flex items-center justify-between gap-3 rounded-lg border border-gray-200 bg-gray-50 p-3';

        const label = document.createElement('span');
        label.dataset.positionNameLabel = '';
        label.className = 'text-sm font-medium text-gray-700';
        label.textContent = position.name;

        const actions = document.createElement('div');
        actions.className = 'flex items-center gap-3';

        const editButton = document.createElement('button');
        editButton.type = 'button';
        editButton.className =
            'edit-position-btn text-sm font-medium text-blue-600 hover:text-blue-800';
        editButton.textContent = 'Editar';
        editButton.dataset.positionId = String(position.id);
        editButton.dataset.positionName = position.name;

        const deleteButton = document.createElement('button');
        deleteButton.type = 'button';
        deleteButton.className =
            'delete-position-btn text-sm font-medium text-red-600 hover:text-red-800 disabled:opacity-50';
        deleteButton.textContent = 'Eliminar';
        deleteButton.dataset.positionId = String(position.id);

        actions.append(editButton, deleteButton);
        row.append(label, actions);
        positionsList.appendChild(row);
    }

    openButton.addEventListener('click', function () {
        resetEditor();
        modal.showModal();
        nameInput.focus();
    });

    closeButton.addEventListener('click', function () {
        if (!saving) modal.close();
    });

    modal.addEventListener('cancel', function (event) {
        if (saving) event.preventDefault();
    });

    modal.addEventListener('close', resetEditor);

    nameInput.addEventListener('input', function () {
        showError('');
        updateSaveButton();
    });

    // Elegir una posición existente para editarla.
    positionsList.addEventListener('click', function (event) {
        const button = event.target.closest('.edit-position-btn');
        if (!button || saving) return;

        editingId = button.dataset.positionId;
        nameInput.value = button.dataset.positionName;
        saveButton.textContent = 'Guardar cambios';
        showError('');
        updateSaveButton();
        nameInput.focus();
    });

    // Guardar una posición nueva o actualizar su nombre.
    saveButton.addEventListener('click', async function () {
        if (saving) return;

        const name = nameInput.value.trim();
        if (!name) {
            showError('Escribe el nombre de la posición.');
            return;
        }

        const isEditing = editingId !== null;
        saving = true;
        updateSaveButton();
        showError('');

        try {
            const url = isEditing
                ? updateUrl.replace('__ID__', encodeURIComponent(editingId))
                : storeUrl;

            const response = await fetch(url, {
                method: isEditing ? 'PUT' : 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ name })
            });

            const result = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(
                    result.errors?.name?.[0] ||
                    result.message ||
                    'No se pudo guardar la posición.'
                );
            }

            const position = result.position;
            if (!position?.id || !position?.name) {
                throw new Error('El servidor no devolvió la posición registrada.');
            }

            if (isEditing) {
                const option = Array.from(positionSelect.options).find(
                    item => item.value === String(position.id)
                );
                if (option) option.textContent = position.name;

                const editButton = Array.from(
                    positionsList.querySelectorAll('.edit-position-btn')
                ).find(item => item.dataset.positionId === String(position.id));

                if (editButton) {
                    editButton.dataset.positionName = position.name;
                    const row = editButton.closest('[data-position-row]');
                    const label = row?.querySelector('[data-position-name-label]');
                    if (label) label.textContent = position.name;
                }
            } else {
                const option = new Option(
                    position.name,
                    String(position.id),
                    true,
                    true
                );
                positionSelect.add(option);
                positionSelect.dispatchEvent(new Event('change', { bubbles: true }));
                addPositionRow(position);
            }

            modal.close();
        } catch (error) {
            showError(error.message || 'Error al guardar la posición.');
        } finally {
            saving = false;
            updateSaveButton();
        }
    });

    // Eliminar solo si Laravel confirma que no tiene jugadores asociados.
    positionsList.addEventListener('click', async function (event) {
        const button = event.target.closest('.delete-position-btn');
        if (!button || saving) return;

        const id = button.dataset.positionId;
        const row = button.closest('[data-position-row]');
        if (!id || !row) return;

        const positionName = row.querySelector('[data-position-name-label]')?.textContent.trim()
            || 'esta posición';

        const confirmed = window.confirm(
            `¿Eliminar la posición "${positionName}"?\n\nEsta acción no se puede deshacer.`
        );
        if (!confirmed) return;

        saving = true;
        button.disabled = true;
        updateSaveButton();
        showError('');

        try {
            const url = deleteUrl.replace('__ID__', encodeURIComponent(id));
            const response = await fetch(url, {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });
            const result = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw new Error(
                    result.message ||
                    'No se pudo eliminar la posición.'
                );
            }

            const option = Array.from(positionSelect.options).find(
                item => item.value === String(id)
            );
            if (option) {
                const wasSelected = option.selected;
                option.remove();
                if (wasSelected) {
                    positionSelect.value = '';
                    positionSelect.dispatchEvent(
                        new Event('change', { bubbles: true })
                    );
                }
            }

            row.remove();
            if (editingId === String(id)) resetEditor();
            showEmptyStateIfNeeded();
        } catch (error) {
            showError(error.message || 'Error al eliminar la posición.');
        } finally {
            saving = false;
            button.disabled = false;
            updateSaveButton();
        }
    });
});
</script>
@endpush
