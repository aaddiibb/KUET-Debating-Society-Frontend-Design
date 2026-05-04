const loginForm = document.querySelector('.login-form');

if (loginForm) {
	const emailInput = document.getElementById('email');
	const passwordInput = document.getElementById('password');
	const flashMessage = document.querySelector('[data-flash-message]');
	const fields = [emailInput, passwordInput];

	function showFlashMessage(message, type) {
		if (!flashMessage || !message) return;
		flashMessage.textContent = message;
		flashMessage.classList.remove('flash-success', 'flash-error');
		flashMessage.classList.add(type === 'error' ? 'flash-error' : 'flash-success');
		flashMessage.hidden = false;
	}

	const loginUrl = new URL(window.location.href);

	// Show success flash from signup redirect
	const signupStatus = loginUrl.searchParams.get('signup');
	const flashText = loginUrl.searchParams.get('message');
	if (signupStatus === 'success' && flashText) {
		showFlashMessage(flashText, 'success');
		loginUrl.searchParams.delete('signup');
		loginUrl.searchParams.delete('message');
		window.history.replaceState({}, '', loginUrl.toString());
	}

	// Show error flash from failed login redirect
	const errorParam = loginUrl.searchParams.get('error');
	const emailParam = loginUrl.searchParams.get('email');
	if (errorParam) {
		showFlashMessage(decodeURIComponent(errorParam), 'error');
		if (emailInput && emailParam) {
			emailInput.value = decodeURIComponent(emailParam);
		}
		loginUrl.searchParams.delete('error');
		loginUrl.searchParams.delete('email');
		window.history.replaceState({}, '', loginUrl.toString());
	}

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

	function isValidKuetEmail(email) {
		return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email) && email.toLowerCase().endsWith('kuet.ac.bd');
	}

	function validateLoginForm() {
		let valid = true;
		const email = emailInput.value.trim();
		const password = passwordInput.value;

		if (email === '') {
			setFieldError(emailInput, 'This field is required.');
			valid = false;
		} else if (!isValidKuetEmail(email)) {
			setFieldError(emailInput, 'Please enter a valid kuet.ac.bd email address.');
			valid = false;
		}

		if (password === '') {
			setFieldError(passwordInput, 'This field is required.');
			valid = false;
		}

		return valid;
	}

	fields.forEach((field) => {
		field.addEventListener('input', () => clearFieldError(field));
	});

	loginForm.addEventListener('submit', (event) => {
		fields.forEach(clearFieldError);
		if (!validateLoginForm()) {
			event.preventDefault();
		}
	});
}
