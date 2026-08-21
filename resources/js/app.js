import './bootstrap';

import Swal from 'sweetalert2';
import intlTelInput from 'intl-tel-input';

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

function initPhoneInputs() {
    document.querySelectorAll('input[type="tel"]').forEach((input) => {
        if (input.dataset.intlTelInit) return;
        input.dataset.intlTelInit = "true";

        const iti = intlTelInput(input, {
            initialCountry: "in",
            separateDialCode: true,
            utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@24.3.4/build/js/utils.js"
        });

        const form = input.closest('form');
        if (form) {
            form.addEventListener('submit', () => {
                if (iti.isValidNumber()) {
                    input.value = iti.getNumber();
                }
            });
        }
    });
}

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

window.previewImage = function(input, removeBtnId, previewClass, placeholderClass) {
    const parentContainer = input.closest('.flex-col, .flex-row, .grid, .grid-cols-1');
    if (!parentContainer) return;
    const preview = previewClass ? parentContainer.querySelector(previewClass) : parentContainer.querySelector('.profile-preview-img');
    const placeholder = placeholderClass ? parentContainer.querySelector(placeholderClass) : parentContainer.querySelector('.profile-placeholder-icon');
    const removeInput = parentContainer.querySelector('input[name^="remove_"]');
    const removeBtn = removeBtnId ? parentContainer.querySelector(removeBtnId) : parentContainer.querySelector('#remove_photo_btn');

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            if (preview) {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
            }
            if (placeholder) {
                placeholder.classList.add('hidden');
            }
            if (removeInput) {
                removeInput.value = '0';
            }
            if (removeBtn) {
                removeBtn.classList.remove('hidden');
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
};

window.clearImage = function(btn) {
    const parentContainer = btn.closest('.flex-col, .flex-row');
    if (!parentContainer) return;
    const input = parentContainer.querySelector('input[type="file"]');
    const removeInput = parentContainer.querySelector('input[name="remove_profile_photo"]');
    const preview = parentContainer.querySelector('.profile-preview-img');
    const placeholder = parentContainer.querySelector('.profile-placeholder-icon');

    // Clear file input
    if (input) {
        input.value = '';
    }
    // Set hidden field to tell backend to delete file
    if (removeInput) {
        removeInput.value = '1';
    }
    // Reset preview
    if (preview) {
        preview.src = '';
        preview.classList.add('hidden');
    }
    if (placeholder) {
        placeholder.classList.remove('hidden');
    }
    // Hide remove button
    btn.classList.add('hidden');
};

window.clearFormFile = function(btn, hiddenInputName, previewImgClass, placeholderIconClass) {
    const rootContainer = btn.closest('.flex-col, .flex-row, .grid-cols-1, .grid');
    if (!rootContainer) return;
    const input = rootContainer.querySelector('input[type="file"]');
    const removeInput = rootContainer.querySelector(`input[name="${hiddenInputName}"]`);
    const preview = previewImgClass ? rootContainer.querySelector(previewImgClass) : null;
    const placeholder = placeholderIconClass ? rootContainer.querySelector(placeholderIconClass) : null;
    const downloadLink = rootContainer.querySelector('.download-link');

    if (input) {
        input.value = '';
    }
    if (removeInput) {
        removeInput.value = '1';
    }
    if (preview) {
        preview.src = '';
        preview.classList.add('hidden');
    }
    if (placeholder) {
        placeholder.classList.remove('hidden');
    }
    if (downloadLink) {
        downloadLink.classList.add('hidden');
    }
    btn.classList.add('hidden');
};

window.previewFilePopup = function(url, type) {
    if (!url) return;
    
    // Auto-detect type if not provided
    if (!type) {
        const ext = url.split('?')[0].split('.').pop().toLowerCase();
        if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(ext)) {
            type = 'image';
        } else if (ext === 'pdf') {
            type = 'pdf';
        } else {
            type = 'other';
        }
    }

    if (type === 'image') {
        Swal.fire({
            html: `<div class="p-1"><img src="${url}" class="w-full max-h-[75vh] object-contain rounded-xl shadow-inner"></div>`,
            showConfirmButton: false,
            showCloseButton: true,
            width: 'auto',
            maxWidth: '90%',
            background: 'transparent',
            customClass: {
                popup: 'bg-transparent border-0 shadow-none'
            }
        });
    } else if (type === 'pdf') {
        Swal.fire({
            html: `<iframe src="${url}" class="w-full h-[75vh] border-0 rounded-xl" style="background: white;"></iframe>`,
            showConfirmButton: false,
            showCloseButton: true,
            width: '80%',
            maxWidth: '1200px',
            background: 'transparent',
            customClass: {
                popup: 'bg-transparent border-0 shadow-none'
            }
        });
    } else {
        // Fallback for doc/docx, just open in new tab
        window.open(url, '_blank');
    }
};

document.addEventListener('DOMContentLoaded', () => {
    initColumnToggles();
    initPhoneInputs();
});
