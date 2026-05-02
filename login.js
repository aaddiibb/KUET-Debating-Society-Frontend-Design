const loginForm = document.querySelector('.login-form');

if (loginForm) {
	const emailInput = document.getElementById('email');
	const passwordInput = document.getElementById('password');
	const fields = [emailInput, passwordInput];

	function getErrorElement(field) {
		const container = field.closest('.form-group') || field.parentElement;
		let errorElement = container.querySelector('.error-message');

		if (!errorElement) {
			errorElement = document.createElement('p');
			errorElement.className = 'error-message';
			container.appendChild(errorElement);
		}

		return errorElement;
	}

	function setFieldError(field, message) {
		const errorElement = getErrorElement(field);
		errorElement.textContent = message;
		field.classList.add('input-error');
		field.setAttribute('aria-invalid', 'true');
	}

	function clearFieldError(field) {
		const errorElement = getErrorElement(field);
		errorElement.textContent = '';
		field.classList.remove('input-error');
		field.removeAttribute('aria-invalid');
	}

	function isValidEmailFormat(email) {
		return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
	}

	function validateLoginForm() {
		let valid = true;
		const email = emailInput.value.trim();
		const password = passwordInput.value.trim();

		if (email === '') {
			setFieldError(emailInput, 'This field is required.');
			valid = false;
		} else if (!isValidEmailFormat(email)) {
			setFieldError(emailInput, 'Please enter a valid email address.');
			valid = false;
		}

		if (password === '') {
			setFieldError(passwordInput, 'This field is required.');
			valid = false;
		}

		return valid;
	}

	fields.forEach((field) => {
		field.addEventListener('input', () => {
			clearFieldError(field);
		});
	});

	loginForm.addEventListener('submit', (event) => {
		fields.forEach(clearFieldError);

		const isValid = validateLoginForm();
		if (isValid) {
			console.log('Login form validation passed.');
			return;
		}

		event.preventDefault();
	});
}
