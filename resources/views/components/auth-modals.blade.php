<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="loginModalLabel">Login ke PublicForum</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Error Alert -->
                <div id="login-error-alert" class="alert alert-danger d-none" role="alert">
                    <ul id="login-errors" class="mb-0"></ul>
                </div>
                
                <!-- Success Alert -->
                <div id="login-success-alert" class="alert alert-success d-none" role="alert">
                    <span id="login-success-message"></span>
                </div>

                <form id="login-form">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username atau Email</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username atau email" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required>
                            <button class="btn btn-outline-secondary" type="button" id="toggle-password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember-me" name="remember_me">
                        <label class="form-check-label" for="remember-me">Ingat saya</label>
                        <a href="javascript:void(0)" class="float-end text-danger">Lupa password?</a>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-danger" id="login-submit-btn">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            <span class="btn-text">Login</span>
                        </button>
                    </div>
                </form>

                <div class="separator my-4">
                    <span>atau login dengan</span>
                </div>

                <div class="social-login d-flex justify-content-center gap-3 mb-3">
                    <button type="button" class="btn btn-outline-primary"><i class="fab fa-facebook-f me-2"></i>Facebook</button>
                    <button type="button" class="btn btn-outline-danger"><i class="fab fa-google me-2"></i>Google</button>
                    <button type="button" class="btn btn-outline-dark"><i class="fab fa-github me-2"></i>GitHub</button>
                </div>

                <div class="text-center mt-4">
                    <p>Belum punya akun? <a href="javascript:void(0)" class="text-danger" id="register-link" onclick="switchToRegister()">Daftar
                            sekarang</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Register Modal -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="registerModalLabel">Daftar Akun Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Error Alert -->
                <div id="register-error-alert" class="alert alert-danger d-none" role="alert">
                    <ul id="register-errors" class="mb-0"></ul>
                </div>
                
                <!-- Success Alert -->
                <div id="register-success-alert" class="alert alert-success d-none" role="alert">
                    <span id="register-success-message"></span>
                </div>

                <form id="register-form">
                    <div class="mb-3">
                        <label for="fullname" class="form-label">Nama Lengkap</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="fullname" name="fullname" placeholder="Masukkan nama lengkap" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="register-username" class="form-label">Username</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="register-username" name="username" placeholder="Masukkan username yang diinginkan" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-group">
                            <input type="email" class="form-control" id="email" name="email" placeholder="Masukkan alamat email" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="register-password" class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="register-password" name="password" placeholder="Masukkan password" required>
                            <button class="btn btn-outline-secondary" type="button" id="toggle-register-password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="confirm-password" class="form-label">Konfirmasi Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="confirm-password" name="confirm_password" placeholder="Konfirmasi password" required>
                        </div>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                        <label class="form-check-label" for="terms">Saya setuju dengan <a href="javascript:void(0)" class="text-danger">Syarat dan
                                Ketentuan</a></label>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-danger" id="register-submit-btn">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            <span class="btn-text">Daftar</span>
                        </button>
                    </div>
                </form>

                <div class="text-center mt-4">
                    <p>Sudah punya akun? <a href="javascript:void(0)" class="text-danger" id="login-link" onclick="switchToLogin()">Login
                            sekarang</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ===== LOGIN MODAL FUNCTIONALITY =====
    
    // Toggle password visibility for login
    document.getElementById('toggle-password').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        // Toggle eye icon
        const eyeIcon = this.querySelector('i');
        eyeIcon.classList.toggle('fa-eye');
        eyeIcon.classList.toggle('fa-eye-slash');
    });
    
    // Handle login form submission
    document.getElementById('login-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('login-submit-btn');
        const spinner = submitBtn.querySelector('.spinner-border');
        const btnText = submitBtn.querySelector('.btn-text');
        const errorAlert = document.getElementById('login-error-alert');
        const successAlert = document.getElementById('login-success-alert');
        const errorsList = document.getElementById('login-errors');
        
        // Show loading state
        submitBtn.disabled = true;
        spinner.classList.remove('d-none');
        btnText.textContent = 'Loading...';
        errorAlert.classList.add('d-none');
        successAlert.classList.add('d-none');
        
        // Get form data
        const formData = new FormData(this);
        
        // Add CSRF token if available
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            formData.append('_token', csrfToken.getAttribute('content'));
        }
        
        // Send AJAX request
        fetch('/login', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('Login response:', data);
            if (data.success) {
                // Show success message
                document.getElementById('login-success-message').textContent = data.message;
                successAlert.classList.remove('d-none');
                
                // Store user data if needed
                if (data.user) {
                    localStorage.setItem('user', JSON.stringify(data.user));
                    console.log('User data stored:', data.user);
                }
                
                // Debug: Check if cookies are set
                console.log('Cookies after login:', document.cookie);
                
                // Close modal after delay
                setTimeout(() => {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('loginModal'));
                    modal.hide();
                    
                    // Instead of reloading, let's manually update the UI first
                    console.log('Updating UI after login...');
                    if (data.user) {
                        updateUIForAuthenticatedUser(data.user);
                    }
                    
                    // Reload page or redirect
                    window.location.reload();
                }, 1500);
                
            } else {
                // Show error messages
                errorsList.innerHTML = '';
                
                if (data.errors) {
                    // Validation errors
                    Object.values(data.errors).forEach(errorArray => {
                        errorArray.forEach(error => {
                            const li = document.createElement('li');
                            li.textContent = error;
                            errorsList.appendChild(li);
                        });
                    });
                } else {
                    // General error
                    const li = document.createElement('li');
                    li.textContent = data.message || 'Terjadi kesalahan saat login';
                    errorsList.appendChild(li);
                }
                
                errorAlert.classList.remove('d-none');
            }
        })
        .catch(error => {
            console.error('Login error:', error);
            errorsList.innerHTML = '<li>Terjadi kesalahan koneksi. Silakan coba lagi.</li>';
            errorAlert.classList.remove('d-none');
        })
        .finally(() => {
            // Reset loading state
            submitBtn.disabled = false;
            spinner.classList.add('d-none');
            btnText.textContent = 'Login';
        });
    });

    // ===== REGISTER MODAL FUNCTIONALITY =====
    
    // Toggle password visibility for register
    document.getElementById('toggle-register-password').addEventListener('click', function() {
        const passwordInput = document.getElementById('register-password');
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        // Toggle eye icon
        const eyeIcon = this.querySelector('i');
        eyeIcon.classList.toggle('fa-eye');
        eyeIcon.classList.toggle('fa-eye-slash');
    });
    
    // Handle register form submission
    document.getElementById('register-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('register-submit-btn');
        const spinner = submitBtn.querySelector('.spinner-border');
        const btnText = submitBtn.querySelector('.btn-text');
        const errorAlert = document.getElementById('register-error-alert');
        const successAlert = document.getElementById('register-success-alert');
        const errorsList = document.getElementById('register-errors');
        
        // Show loading state
        submitBtn.disabled = true;
        spinner.classList.remove('d-none');
        btnText.textContent = 'Mendaftar...';
        errorAlert.classList.add('d-none');
        successAlert.classList.add('d-none');
        
        // Get form data
        const formData = new FormData(this);
        
        // Add CSRF token if available
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            formData.append('_token', csrfToken.getAttribute('content'));
        }
        
        // Send AJAX request
        fetch('/register', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                document.getElementById('register-success-message').textContent = data.message;
                successAlert.classList.remove('d-none');
                
                // Store user data if needed
                if (data.user) {
                    localStorage.setItem('user', JSON.stringify(data.user));
                }
                
                // Close modal after delay
                setTimeout(() => {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('registerModal'));
                    modal.hide();
                    
                    // Reload page or redirect
                    window.location.reload();
                }, 1500);
                
            } else {
                // Show error messages
                errorsList.innerHTML = '';
                
                if (data.errors) {
                    // Validation errors
                    Object.values(data.errors).forEach(errorArray => {
                        errorArray.forEach(error => {
                            const li = document.createElement('li');
                            li.textContent = error;
                            errorsList.appendChild(li);
                        });
                    });
                } else {
                    // General error
                    const li = document.createElement('li');
                    li.textContent = data.message || 'Terjadi kesalahan saat mendaftar';
                    errorsList.appendChild(li);
                }
                
                errorAlert.classList.remove('d-none');
            }
        })
        .catch(error => {
            console.error('Register error:', error);
            errorsList.innerHTML = '<li>Terjadi kesalahan koneksi. Silakan coba lagi.</li>';
            errorAlert.classList.remove('d-none');
        })
        .finally(() => {
            // Reset loading state
            submitBtn.disabled = false;
            spinner.classList.add('d-none');
            btnText.textContent = 'Daftar';
        });
    });
});

// ===== MODAL SWITCHING FUNCTIONS =====

// Function to switch to register modal
function switchToRegister() {
    const loginModal = bootstrap.Modal.getInstance(document.getElementById('loginModal'));
    if (loginModal) {
        loginModal.hide();
    }
    
    // Show register modal
    const registerModal = document.getElementById('registerModal');
    if (registerModal) {
        const modal = new bootstrap.Modal(registerModal);
        modal.show();
    }
}

// Function to switch to login modal
function switchToLogin() {
    const registerModal = bootstrap.Modal.getInstance(document.getElementById('registerModal'));
    if (registerModal) {
        registerModal.hide();
    }
    
    // Show login modal
    const loginModal = document.getElementById('loginModal');
    if (loginModal) {
        const modal = new bootstrap.Modal(loginModal);
        modal.show();
    }
}

// ===== LOGOUT FUNCTION =====

// Global logout function
function logout() {
    fetch('/logout', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            localStorage.removeItem('user');
            window.location.reload();
        }
    })
    .catch(error => {
        console.error('Logout error:', error);
        // Force logout on error
        localStorage.removeItem('user');
        window.location.reload();
    });
}

// ===== AUTH STATE MANAGEMENT =====

// Check authentication status on page load
function checkAuthStatus() {
    fetch('/auth/check', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.authenticated && data.user) {
            localStorage.setItem('user', JSON.stringify(data.user));
            // Update UI for authenticated user
            updateUIForAuthenticatedUser(data.user);
        } else {
            localStorage.removeItem('user');
            // Update UI for guest user
            updateUIForGuestUser();
        }
    })
    .catch(error => {
        console.error('Auth check error:', error);
    });
}

// Update UI for authenticated user
function updateUIForAuthenticatedUser(user) {
    // Hide login/register buttons
    const loginButtons = document.querySelectorAll('.login-btn, .register-btn');
    loginButtons.forEach(btn => btn.style.display = 'none');
    
    // Show user menu/profile
    const userMenus = document.querySelectorAll('.user-menu');
    userMenus.forEach(menu => menu.style.display = 'block');
    
    // Update user info in UI
    const userNameElements = document.querySelectorAll('.user-display-name');
    userNameElements.forEach(el => el.textContent = user.display_name);
    
    const userAvatars = document.querySelectorAll('.user-avatar');
    userAvatars.forEach(el => {
        if (user.avatar_url) {
            el.src = user.avatar_url;
        }
    });
}

// Update UI for guest user
function updateUIForGuestUser() {
    // Show login/register buttons
    const loginButtons = document.querySelectorAll('.login-btn, .register-btn');
    loginButtons.forEach(btn => btn.style.display = 'block');
    
    // Hide user menu/profile
    const userMenus = document.querySelectorAll('.user-menu');
    userMenus.forEach(menu => menu.style.display = 'none');
}

// Run auth check when page loads
document.addEventListener('DOMContentLoaded', function() {
    checkAuthStatus();
});
</script>