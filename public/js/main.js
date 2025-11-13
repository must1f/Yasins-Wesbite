/**
 * Main JavaScript file for Apprenticeship Portal
 * Handles form validation, dynamic forms, and AJAX requests
 */

// Utility Functions
const $ = (selector) => document.querySelector(selector);
const $$ = (selector) => document.querySelectorAll(selector);

// Form Validation
class FormValidator {
    constructor(form) {
        this.form = form;
        this.errors = {};
    }

    validateRequired(input, message = 'This field is required') {
        if (!input.value.trim()) {
            this.addError(input.name, message);
            return false;
        }
        return true;
    }

    validateEmail(input) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(input.value)) {
            this.addError(input.name, 'Please enter a valid email address');
            return false;
        }
        return true;
    }

    validatePassword(input, minLength = 8) {
        if (input.value.length < minLength) {
            this.addError(input.name, `Password must be at least ${minLength} characters`);
            return false;
        }
        return true;
    }

    validatePasswordMatch(password, confirmPassword) {
        if (password.value !== confirmPassword.value) {
            this.addError(confirmPassword.name, 'Passwords do not match');
            return false;
        }
        return true;
    }

    validateFile(input, allowedTypes, maxSize) {
        const file = input.files[0];
        if (!file) {
            this.addError(input.name, 'Please select a file');
            return false;
        }

        if (allowedTypes && !allowedTypes.includes(file.type)) {
            this.addError(input.name, 'Invalid file type');
            return false;
        }

        if (maxSize && file.size > maxSize) {
            this.addError(input.name, `File size must be less than ${maxSize / 1024 / 1024}MB`);
            return false;
        }

        return true;
    }

    addError(fieldName, message) {
        this.errors[fieldName] = message;
    }

    clearErrors() {
        this.errors = {};
        $$('.form-error').forEach(el => el.remove());
        $$('.form-control.error').forEach(el => el.classList.remove('error'));
    }

    displayErrors() {
        this.clearErrors();
        for (const [fieldName, message] of Object.entries(this.errors)) {
            const input = this.form.querySelector(`[name="${fieldName}"]`);
            if (input) {
                input.classList.add('error');
                const errorDiv = document.createElement('div');
                errorDiv.className = 'form-error';
                errorDiv.textContent = message;
                input.parentElement.appendChild(errorDiv);
            }
        }
    }

    hasErrors() {
        return Object.keys(this.errors).length > 0;
    }
}

// AJAX Helper
async function fetchAPI(url, options = {}) {
    try {
        const response = await fetch(url, {
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        });

        const data = await response.json();
        return { success: response.ok, data };
    } catch (error) {
        console.error('API Error:', error);
        return { success: false, error: error.message };
    }
}

// Dynamic Form Builder
class DynamicFormBuilder {
    constructor(container, fields) {
        this.container = container;
        this.fields = fields;
    }

    render() {
        this.container.innerHTML = '';

        this.fields.forEach(field => {
            const formGroup = document.createElement('div');
            formGroup.className = 'form-group';

            const label = document.createElement('label');
            label.className = 'form-label';
            label.textContent = field.field_label;
            if (field.is_required) {
                label.innerHTML += ' <span class="text-red-500">*</span>';
            }

            formGroup.appendChild(label);

            let input;
            switch (field.field_type) {
                case 'text':
                    input = this.createTextInput(field);
                    break;
                case 'textarea':
                    input = this.createTextarea(field);
                    break;
                case 'dropdown':
                    input = this.createDropdown(field);
                    break;
                case 'file':
                    input = this.createFileInput(field);
                    break;
            }

            formGroup.appendChild(input);
            this.container.appendChild(formGroup);
        });
    }

    createTextInput(field) {
        const input = document.createElement('input');
        input.type = 'text';
        input.name = `field_${field.field_id}`;
        input.className = 'form-control';
        input.required = field.is_required;
        return input;
    }

    createTextarea(field) {
        const textarea = document.createElement('textarea');
        textarea.name = `field_${field.field_id}`;
        textarea.className = 'form-control';
        textarea.rows = 4;
        textarea.required = field.is_required;
        return textarea;
    }

    createDropdown(field) {
        const select = document.createElement('select');
        select.name = `field_${field.field_id}`;
        select.className = 'form-control';
        select.required = field.is_required;

        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = 'Select an option';
        select.appendChild(defaultOption);

        if (field.field_options) {
            const options = JSON.parse(field.field_options);
            options.forEach(opt => {
                const option = document.createElement('option');
                option.value = opt;
                option.textContent = opt;
                select.appendChild(option);
            });
        }

        return select;
    }

    createFileInput(field) {
        const wrapper = document.createElement('div');
        wrapper.className = 'file-upload-wrapper';

        const input = document.createElement('input');
        input.type = 'file';
        input.name = `field_${field.field_id}`;
        input.id = `field_${field.field_id}`;
        input.required = field.is_required;

        const label = document.createElement('label');
        label.htmlFor = `field_${field.field_id}`;
        label.className = 'file-upload-label';
        label.textContent = 'Choose file';

        wrapper.appendChild(input);
        wrapper.appendChild(label);

        input.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                label.textContent = e.target.files[0].name;
            }
        });

        return wrapper;
    }
}

// Flash Message Handler
function showFlashMessage(message, type = 'info') {
    const flashContainer = $('#flash-container');
    if (!flashContainer) return;

    const flash = document.createElement('div');
    flash.className = `flash-message flash-${type}`;
    flash.textContent = message;

    flashContainer.appendChild(flash);

    setTimeout(() => {
        flash.style.opacity = '0';
        setTimeout(() => flash.remove(), 300);
    }, 5000);
}

// Auto-hide flash messages
document.addEventListener('DOMContentLoaded', () => {
    const flashMessages = $$('.flash-message');
    flashMessages.forEach(flash => {
        setTimeout(() => {
            flash.style.opacity = '0';
            setTimeout(() => flash.remove(), 300);
        }, 5000);
    });
});

// Mobile Menu Toggle
function initMobileMenu() {
    const menuButton = $('#mobile-menu-button');
    const mobileMenu = $('#mobile-menu');

    if (menuButton && mobileMenu) {
        menuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    initMobileMenu();
});

// Export for use in other scripts
window.FormValidator = FormValidator;
window.DynamicFormBuilder = DynamicFormBuilder;
window.fetchAPI = fetchAPI;
window.showFlashMessage = showFlashMessage;
