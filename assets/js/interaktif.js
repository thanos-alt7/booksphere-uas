document.addEventListener('DOMContentLoaded', function () {
    const deleteButtons = document.querySelectorAll('[data-confirm-delete]');

    deleteButtons.forEach(function (button) {
        button.addEventListener('click', function (event) {
            const message = button.dataset.confirmDelete || 'Yakin ingin menghapus data ini?';

            if (!confirm(message)) {
                event.preventDefault();
            }
        });
    });

    const toggles = document.querySelectorAll('[data-toggle-target]');

    toggles.forEach(function (toggle) {
        toggle.addEventListener('click', function () {
            const target = document.querySelector(toggle.dataset.toggleTarget);

            if (target) {
                target.classList.toggle('is-open');
                target.classList.toggle('d-none', false);
            }
        });
    });

    const confirmForms = document.querySelectorAll('[data-confirm-submit]');

    confirmForms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const message = form.dataset.confirmSubmit || 'Yakin ingin menyimpan perubahan?';

            if (!confirm(message)) {
                event.preventDefault();
            }
        });
    });
});