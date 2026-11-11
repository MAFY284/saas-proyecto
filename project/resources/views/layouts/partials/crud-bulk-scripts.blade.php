@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const selectAll = document.getElementById('select-all');
            const checks = () => [...document.querySelectorAll('.row-check:not(:disabled)')];
            const bulkMenu = document.getElementById('bulk-menu');
            const bulkToggle = document.getElementById('bulk-menu-toggle');
            const bulkPanel = document.getElementById('bulk-menu-panel');

            function selectedChecks() {
                return checks().filter((input) => input.checked);
            }

            function setBulkMenuOpen(open) {
                if (!bulkPanel || !bulkToggle) {
                    return;
                }

                bulkPanel.classList.toggle('hidden', !open);
                bulkToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            }

            function refreshActions() {
                const count = selectedChecks().length;

                if (bulkMenu) {
                    const hasSelection = count > 0;
                    bulkMenu.hidden = !hasSelection;
                    bulkMenu.classList.toggle('hidden', !hasSelection);

                    if (!hasSelection) {
                        setBulkMenuOpen(false);
                    }
                }

                if (selectAll) {
                    const enabled = checks();
                    selectAll.checked = enabled.length > 0 && enabled.every((input) => input.checked);
                }
            }

            bulkToggle?.addEventListener('click', (event) => {
                event.stopPropagation();
                setBulkMenuOpen(bulkPanel.classList.contains('hidden'));
            });

            document.addEventListener('click', (event) => {
                if (bulkMenu && !bulkMenu.contains(event.target)) {
                    setBulkMenuOpen(false);
                }
            });

            selectAll?.addEventListener('change', () => {
                checks().forEach((input) => {
                    input.checked = selectAll.checked;
                });
                refreshActions();
            });

            document.querySelectorAll('.row-check').forEach((input) => {
                input.addEventListener('change', refreshActions);
            });

            document.querySelectorAll('[data-edit-url]').forEach((row) => {
                row.addEventListener('click', (event) => {
                    if (event.target.closest('input')) {
                        return;
                    }

                    window.location.href = row.dataset.editUrl;
                });
            });

            refreshActions();
        });
    </script>
@endpush
