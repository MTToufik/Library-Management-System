/**
 * validation.js
 * ------------------------------------------------------------
 * Client-side form validation for Login, Book, and Member forms.
 * Shows inline error messages before the form is submitted to PHP.
 * PHP still re-validates everything server-side for security.
 * ------------------------------------------------------------
 */

function showError(inputEl, message) {
    inputEl.classList.add('input-error');
    const errorEl = document.getElementById(inputEl.id + '_error');
    if (errorEl) errorEl.textContent = message;
}

function clearError(inputEl) {
    inputEl.classList.remove('input-error');
    const errorEl = document.getElementById(inputEl.id + '_error');
    if (errorEl) errorEl.textContent = '';
}

function isEmpty(value) {
    return value === null || value.trim() === '';
}

/* ================= LOGIN FORM ================= */
function validateLoginForm() {
    let valid = true;
    const username = document.getElementById('username');
    const password = document.getElementById('password');

    if (isEmpty(username.value)) {
        showError(username, 'Username is required.');
        valid = false;
    } else {
        clearError(username);
    }

    if (isEmpty(password.value)) {
        showError(password, 'Password is required.');
        valid = false;
    } else if (password.value.length < 4) {
        showError(password, 'Password must be at least 4 characters.');
        valid = false;
    } else {
        clearError(password);
    }

    return valid;
}

/* ================= BOOK FORM ================= */
function validateBookForm() {
    let valid = true;
    const title = document.getElementById('title');
    const author = document.getElementById('author');
    const isbn = document.getElementById('isbn');
    const category = document.getElementById('category');
    const totalCopies = document.getElementById('total_copies');

    if (isEmpty(title.value)) { showError(title, 'Book title is required.'); valid = false; } else { clearError(title); }
    if (isEmpty(author.value)) { showError(author, 'Author name is required.'); valid = false; } else { clearError(author); }

    if (isEmpty(isbn.value)) {
        showError(isbn, 'ISBN is required.');
        valid = false;
    } else if (!/^[0-9\-]{10,20}$/.test(isbn.value.trim())) {
        showError(isbn, 'Enter a valid ISBN (digits and dashes only).');
        valid = false;
    } else {
        clearError(isbn);
    }

    if (isEmpty(category.value)) { showError(category, 'Category is required.'); valid = false; } else { clearError(category); }

    if (isEmpty(totalCopies.value)) {
        showError(totalCopies, 'Total copies is required.');
        valid = false;
    } else if (parseInt(totalCopies.value) < 1) {
        showError(totalCopies, 'Total copies must be at least 1.');
        valid = false;
    } else {
        clearError(totalCopies);
    }

    return valid;
}

/* ================= MEMBER FORM ================= */
function validateMemberForm() {
    let valid = true;
    const fullName = document.getElementById('full_name');
    const email = document.getElementById('email');
    const phone = document.getElementById('phone');

    if (isEmpty(fullName.value)) { showError(fullName, 'Full name is required.'); valid = false; } else { clearError(fullName); }

    if (isEmpty(email.value)) {
        showError(email, 'Email is required.');
        valid = false;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
        showError(email, 'Enter a valid email address.');
        valid = false;
    } else {
        clearError(email);
    }

    if (isEmpty(phone.value)) {
        showError(phone, 'Phone number is required.');
        valid = false;
    } else if (!/^[0-9+\-\s]{7,20}$/.test(phone.value.trim())) {
        showError(phone, 'Enter a valid phone number.');
        valid = false;
    } else {
        clearError(phone);
    }

    return valid;
}

/* ================= STUDENT REGISTRATION FORM ================= */
function validateRegisterForm() {
    let valid = true;
    const fullName = document.getElementById('full_name');
    const email = document.getElementById('email');
    const phone = document.getElementById('phone');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');

    if (isEmpty(fullName.value)) { showError(fullName, 'Full name is required.'); valid = false; } else { clearError(fullName); }

    if (isEmpty(email.value)) {
        showError(email, 'Email is required.');
        valid = false;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
        showError(email, 'Enter a valid email address.');
        valid = false;
    } else {
        clearError(email);
    }

    if (isEmpty(phone.value)) {
        showError(phone, 'Phone number is required.');
        valid = false;
    } else if (!/^[0-9+\-\s]{7,20}$/.test(phone.value.trim())) {
        showError(phone, 'Enter a valid phone number.');
        valid = false;
    } else {
        clearError(phone);
    }

    if (isEmpty(password.value)) {
        showError(password, 'Password is required.');
        valid = false;
    } else if (password.value.length < 6) {
        showError(password, 'Password must be at least 6 characters.');
        valid = false;
    } else {
        clearError(password);
    }

    if (isEmpty(confirmPassword.value)) {
        showError(confirmPassword, 'Please confirm your password.');
        valid = false;
    } else if (confirmPassword.value !== password.value) {
        showError(confirmPassword, 'Passwords do not match.');
        valid = false;
    } else {
        clearError(confirmPassword);
    }

    return valid;
}

/* ================= STUDENT LOGIN FORM ================= */
function validateStudentLoginForm() {
    let valid = true;
    const email = document.getElementById('email');
    const password = document.getElementById('password');

    if (isEmpty(email.value)) {
        showError(email, 'Email is required.');
        valid = false;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
        showError(email, 'Enter a valid email address.');
        valid = false;
    } else {
        clearError(email);
    }

    if (isEmpty(password.value)) {
        showError(password, 'Password is required.');
        valid = false;
    } else {
        clearError(password);
    }

    return valid;
}

/* ================= ISSUE BOOK FORM ================= */
function validateIssueForm() {
    let valid = true;
    const bookId = document.getElementById('book_id');
    const memberId = document.getElementById('member_id');
    const issueDate = document.getElementById('issue_date');
    const dueDate = document.getElementById('due_date');

    if (isEmpty(bookId.value)) { showError(bookId, 'Please select a book.'); valid = false; } else { clearError(bookId); }
    if (isEmpty(memberId.value)) { showError(memberId, 'Please select a member.'); valid = false; } else { clearError(memberId); }
    if (isEmpty(issueDate.value)) { showError(issueDate, 'Issue date is required.'); valid = false; } else { clearError(issueDate); }
    if (isEmpty(dueDate.value)) { showError(dueDate, 'Due date is required.'); valid = false; } else { clearError(dueDate); }

    return valid;
}
