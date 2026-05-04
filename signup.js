const signupForm = document.querySelector('.signup-form');

if (signupForm) {
	const fullNameInput = document.getElementById('full-name');
	const studentIdInput = document.getElementById('student-id');
	const departmentInput = document.getElementById('department');
	const emailInput = document.getElementById('email');
	const passwordInput = document.getElementById('password');
	const confirmPasswordInput = document.getElementById('confirm-password');
	const agreeInput = signupForm.querySelector('input[name="agree"]');

	const fields = [fullNameInput, studentIdInput, departmentInput, emailInput, passwordInput, confirmPasswordInput, agreeInput];

	function getErrorContainer(field) {
		if (field.type === 'checkbox') return field.closest('.agree-row') || field.parentElement;
		return field.closest('.form-group') || field.parentElement;
	}

	function getErrorElement(field) {
		const container = getErrorContainer(field);
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

	function validateRequiredFields() {
		let valid = true;
		fields.forEach((field) => {
			const isEmpty = field.type !== 'checkbox' ? field.value.trim() === '' : !field.checked;
			if (isEmpty) {
				setFieldError(field, 'This field is required.');
				valid = false;
			}
		});
		return valid;
	}

	function validateSignupForm() {
		let valid = validateRequiredFields();

		const studentId = studentIdInput.value.trim();
		if (studentId !== '' && !/^\d{7}$/.test(studentId)) {
			setFieldError(studentIdInput, 'Student ID must be exactly 7 digits.');
			valid = false;
		}

		const email = emailInput.value.trim();
		if (email !== '' && !isValidKuetEmail(email)) {
			setFieldError(emailInput, 'Email must be a valid kuet.ac.bd address.');
			valid = false;
		}

		const password = passwordInput.value;
		if (password !== '' && password.length < 8) {
			setFieldError(passwordInput, 'Password must be at least 8 characters.');
			valid = false;
		}

		const confirmPassword = confirmPasswordInput.value;
		if (confirmPassword !== '' && password !== '' && password !== confirmPassword) {
			setFieldError(confirmPasswordInput, 'Passwords do not match.');
			valid = false;
		}

		return valid;
	}

	fields.forEach((field) => {
		const eventName = field.type === 'checkbox' || field.tagName === 'SELECT' ? 'change' : 'input';
		field.addEventListener(eventName, () => clearFieldError(field));
	});

	signupForm.addEventListener('submit', (event) => {
		fields.forEach(clearFieldError);
		if (!validateSignupForm()) {
			event.preventDefault();
		}
	});
}
