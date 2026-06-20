document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('[data-validate="true"]');

    forms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            let isValid = true;
            const requiredFields = form.querySelectorAll('[data-required="true"]');

            requiredFields.forEach(function (field) {
                const message = field.dataset.message || 'Field ini wajib diisi.';
                const errorTarget = form.querySelector(`[data-error-for="${field.name}"]`);

                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                    if (errorTarget) {
                        errorTarget.textContent = message;
                    }
                } else {
                    field.classList.remove('is-invalid');
                    if (errorTarget) {
                        errorTarget.textContent = '';
                    }
                }
            });

            if (!isValid) {
                event.preventDefault();
            }
        });
    });
});
