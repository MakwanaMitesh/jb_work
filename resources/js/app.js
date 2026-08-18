import './bootstrap';

import Swal from 'sweetalert2';

window.Swal = Swal;

window.jbToast = function (icon, message) {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon,
        title: message,
        showConfirmButton: false,
        showCloseButton: true,
        timer: 3500,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
        }
    });
};

/**
 * Per-table column visibility toggle. Any element with
 * [data-col-toggle-table="<table-id>"] acts as a container of
 * checkboxes (input[data-col]); toggling one shows/hides every
 * [data-col="<value>"] cell in the matching table, and the choice is
 * remembered per table in localStorage.
 */
function initColumnToggles() {
    document.querySelectorAll('[data-col-toggle-table]').forEach((container) => {
        const tableId = container.dataset.colToggleTable;
        const table = document.getElementById(tableId);
        if (!table) return;

        const storageKey = `jb-cols-${tableId}`;
        const stored = JSON.parse(localStorage.getItem(storageKey) || '{}');

        const applyVisibility = (col, visible) => {
            table.querySelectorAll(`[data-col="${col}"]`).forEach((el) => {
                el.classList.toggle('d-none', !visible);
            });
        };

        container.querySelectorAll('input[type="checkbox"][data-col]').forEach((checkbox) => {
            const col = checkbox.dataset.col;
            const visible = stored[col] !== false;
            checkbox.checked = visible;
            applyVisibility(col, visible);

            checkbox.addEventListener('change', () => {
                applyVisibility(col, checkbox.checked);
                stored[col] = checkbox.checked;
                localStorage.setItem(storageKey, JSON.stringify(stored));
            });
        });
    });
}

document.addEventListener('DOMContentLoaded', initColumnToggles);
