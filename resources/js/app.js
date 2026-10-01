const initializePasswordToggles = () => {
    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        if (button.dataset.bound === 'true') {
            return;
        }

        const input = document.querySelector(button.dataset.passwordToggle);

        if (!input) {
            return;
        }

        button.dataset.bound = 'true';
        button.addEventListener('click', () => {
            const shouldShow = input.type === 'password';
            const icon = button.querySelector('i');

            input.type = shouldShow ? 'text' : 'password';
            button.setAttribute('aria-label', shouldShow ? 'Hide password' : 'Show password');

            if (icon) {
                icon.classList.toggle('fa-eye', !shouldShow);
                icon.classList.toggle('fa-eye-slash', shouldShow);
            }
        });
    });
};

const initializeConfirmations = () => {
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const message = form.dataset.confirm || 'Are you sure you want to continue?';

            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });
};

const initializeDateRanges = () => {
    if (typeof window.flatpickr !== 'function') {
        return;
    }

    document.querySelectorAll('.date-range-group').forEach((group) => {
        const startInput = group.querySelector('[data-date-start]');
        const endInput = group.querySelector('[data-date-end]');

        if (!startInput || !endInput) {
            return;
        }

        const startPicker = window.flatpickr(startInput, {
            dateFormat: 'Y-m-d',
            minDate: startInput.dataset.minDate || null,
            allowInput: true,
        });
        const endPicker = window.flatpickr(endInput, {
            dateFormat: 'Y-m-d',
            minDate: endInput.dataset.minDate || null,
            allowInput: true,
        });

        startPicker.config.onChange.push((selectedDates) => {
            if (!selectedDates.length) {
                return;
            }

            const minimumEndDate = new Date(selectedDates[0]);
            minimumEndDate.setDate(minimumEndDate.getDate() + 1);
            endPicker.set('minDate', minimumEndDate);

            if (endPicker.selectedDates[0] && endPicker.selectedDates[0] <= selectedDates[0]) {
                endPicker.clear();
            }
        });
    });

    document.querySelectorAll('.datetime-range-group').forEach((group) => {
        const openInput = group.querySelector('[data-datetime-start]');
        const closeInput = group.querySelector('[data-datetime-end]');

        if (!openInput || !closeInput) {
            return;
        }

        const openPicker = window.flatpickr(openInput, {
            enableTime: true,
            dateFormat: 'Y-m-d\\TH:i',
            time_24hr: true,
            minDate: openInput.dataset.minDate || null,
            allowInput: true,
        });
        const closePicker = window.flatpickr(closeInput, {
            enableTime: true,
            dateFormat: 'Y-m-d\\TH:i',
            time_24hr: true,
            allowInput: true,
        });
        const synchronizeCloseDate = (selectedDates) => {
            if (!selectedDates.length) {
                return;
            }

            const minimumCloseDate = new Date(selectedDates[0].getTime() + 30 * 60 * 1000);
            closePicker.set('minDate', minimumCloseDate);

            if (closePicker.selectedDates[0] && closePicker.selectedDates[0] <= minimumCloseDate) {
                closePicker.clear();
            }
        };

        openPicker.config.onChange.push(synchronizeCloseDate);

        if (openPicker.selectedDates.length) {
            synchronizeCloseDate(openPicker.selectedDates);
        }
    });
};

const initializeEditors = () => {
    if (typeof window.tinymce !== 'undefined') {
        window.tinymce.init({
            selector: '.tinymce-editor',
            menubar: false,
            plugins: 'lists link',
            toolbar: 'undo redo | blocks | bold italic | bullist numlist | link | removeformat',
            branding: false,
            height: 300,
            promotion: false,
        });
    }
};

const initializeSubmitStates = () => {
    document.querySelectorAll('form').forEach((form) => {
        if (form.dataset.submitStateBound === 'true' || form.method.toLowerCase() === 'get') {
            return;
        }

        form.dataset.submitStateBound = 'true';
        form.addEventListener('submit', (event) => {
            if (form.dataset.submitting === 'true') {
                event.preventDefault();
                return;
            }

            if (event.defaultPrevented) {
                return;
            }

            form.dataset.submitting = 'true';
            form.setAttribute('aria-busy', 'true');
            form.classList.add('is-submitting');
        });
    });
};

const initializeSidebarActiveLink = () => {
    const container = document.querySelector('.main-sidebar .sidebar');
    const activeLink = container?.querySelector('a.nav-link.active');

    if (!container || !activeLink) {
        return;
    }

    const containerBox = container.getBoundingClientRect();
    const linkBox = activeLink.getBoundingClientRect();

    if (linkBox.top < containerBox.top || linkBox.bottom > containerBox.bottom) {
        container.scrollTop += linkBox.top - containerBox.top - containerBox.height / 2 + linkBox.height / 2;
    }
};

document.addEventListener('DOMContentLoaded', () => {
    initializePasswordToggles();
    initializeConfirmations();
    initializeSubmitStates();
    initializeDateRanges();
    initializeEditors();
    initializeSidebarActiveLink();
});
