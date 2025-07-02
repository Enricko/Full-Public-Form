// resources/views/index.blade.php
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PublicForum')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/content.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/tambah.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/settings.css') }}" />

    @stack('styles')

    <script>
        // Immediate scroll reset - runs before any other scripts
        (function () {
            // Disable browser scroll restoration
            if ('scrollRestoration' in history) {
                history.scrollRestoration = 'manual';
            }

            // Force scroll to top immediately
            if (window.scrollY > 0 || document.documentElement.scrollTop > 0) {
                window.scrollTo(0, 0);
                document.documentElement.scrollTop = 0;
                if (document.body) {
                    document.body.scrollTop = 0;
                }
            }

            // Add loading class to prevent scroll during load
            if (document.documentElement) {
                document.documentElement.classList.add('loading');
            }

            // Remove loading class after page loads
            window.addEventListener('load', function () {
                setTimeout(function () {
                    if (document.documentElement) {
                        document.documentElement.classList.remove('loading');
                        document.documentElement.classList.add('page-loaded');
                    }
                    // Final scroll reset
                    window.scrollTo(0, 0);
                }, 100);
            });
        })();
    </script>
</head>

<body>

    @include('components.auth-modals')

    <nav class="navbar navbar-expand-lg navbar-dark bg-danger">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('home') }}">PublicForum</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarContent">
                <form class="d-flex ms-auto" id="navbar-search-form">
                    <input class="form-control me-2" type="search" id="navbar-search-input" placeholder="Search..." aria-label="Search" />
                    <button class="btn btn-light" type="submit">Search</button>
                </form>
                
                <!-- Guest Navigation -->
                <div class="navbar-auth-guest login-btn register-btn" style="display: none;">
                    <button class="btn btn-outline-light ms-3" type="button" onclick="showLoginModal()">Login</button>
                    <button class="btn btn-light ms-2" type="button" onclick="showRegisterModal()">Daftar</button>
                </div>

                <!-- Authenticated User Navigation -->
                <div class="navbar-auth-user user-menu" style="display: none;">
                    <div class="dropdown ms-3">
                        <button class="btn btn-outline-light dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown">
                            <img src="/assets/images/profile.png" alt="Profile" class="user-avatar rounded-circle me-2" width="30" height="30">
                            <span class="user-display-name">User</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('profile') }}"><i class="fas fa-user me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="loadPage('settings')"><i class="fas fa-cog me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="logout()"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-3">
        <div class="row">

            <div class="col-md-2">
                <div class="sidebar">

                    <!-- Authenticated User Sidebar -->
                    <div class="sidebar-authenticated user-menu" style="display: none;">
                        <a class="post-button" href="{{ route('tambah') }}">
                            <i class="fas fa-edit"></i> Posting Baru
                        </a>
                        <ul class="nav flex-column">
                            <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">🏠 Home</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('profile') }}">👤 Profile</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('search') }}">🔍 Search</a></li>
                            <li class="nav-item"><a class="nav-link" href="javascript:void(0)" onclick="loadPage('settings')">⚙️ Settings</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('comment') }}">💬 Comment</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('about') }}">ℹ️ About Us</a></li>
                        </ul>
                    </div>

                    <!-- Guest User Sidebar -->
                    <div class="sidebar-guest login-btn register-btn" style="display: none;">
                        <div class="welcome-box p-3 mb-3 bg-light rounded">
                            <h5>Selamat datang di PublicForum!</h5>
                            <p class="mb-3">Bergabunglah dengan komunitas untuk berbagi dan berdiskusi.</p>
                            <button class="btn btn-danger w-100 mb-2" onclick="showRegisterModal()">Daftar Sekarang</button>
                            <button class="btn btn-outline-danger w-100" onclick="showLoginModal()">Login</button>
                        </div>
                        <ul class="nav flex-column">
                            <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">🏠 Home</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('about') }}">ℹ️ About Us</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mx-5" id="page-content">
                @yield('content')
            </div>

            <div class="col-md-2">
                <div class="right-sidebar">
                    <div class="trending">
                        <h3>Sedang Hangat 🔥</h3>
                        <div id="trending-container">
                            <!-- Loading placeholder -->
                            <div class="loading-placeholder">
                                <div class="placeholder-item">
                                    <div class="placeholder-shimmer"></div>
                                </div>
                                <div class="placeholder-item">
                                    <div class="placeholder-shimmer"></div>
                                </div>
                                <div class="placeholder-item">
                                    <div class="placeholder-shimmer"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="suggested-users">
                        <h3>Rekomendasi Akun</h3>
                        <div id="suggested-users-container">
                            <!-- Loading placeholder -->
                            <div class="loading-placeholder">
                                <div class="placeholder-item">
                                    <div class="placeholder-shimmer"></div>
                                </div>
                                <div class="placeholder-item">
                                    <div class="placeholder-shimmer"></div>
                                </div>
                                <div class="placeholder-item">
                                    <div class="placeholder-shimmer"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="container-fluid text-dark text-center py-3">
            <p class="mb-0">&copy; 2025 PublicForum. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.js') }}"></script>
    <script src="{{ asset('assets/js/content.js') }}"></script>
    <script src="{{ asset('assets/js/post-interactions.js') }}"></script>
    <script>
        // Global configuration
        window.AppConfig = {
            csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            baseUrl: '{{ url('/') }}',
            debug: {{ config('app.debug') ? 'true' : 'false' }}
        };
    </script>

    <script>
        // Modal functions
        function showLoginModal() {
            const modal = new bootstrap.Modal(document.getElementById('loginModal'));
            modal.show();
        }

        function showRegisterModal() {
            const modal = new bootstrap.Modal(document.getElementById('registerModal'));
            modal.show();
        }

        // Enhanced UI update functions for authentication
        function updateUIForAuthenticatedUser(user) {
            console.log('Updating UI for authenticated user:', user);
            
            // Hide all guest elements (includes navbar buttons and sidebar)
            document.querySelectorAll('.login-btn, .register-btn, .navbar-auth-guest, .sidebar-guest').forEach(el => {
                el.style.display = 'none';
            });
            
            // Show all user elements
            document.querySelectorAll('.user-menu, .navbar-auth-user, .sidebar-authenticated').forEach(el => {
                el.style.display = 'block';
            });
            
            // Update user info in UI
            document.querySelectorAll('.user-display-name').forEach(el => {
                el.textContent = user.display_name || user.username;
            });
            
            document.querySelectorAll('.user-avatar').forEach(el => {
                if (user.avatar_url) {
                    el.src = user.avatar_url.startsWith('http') ? user.avatar_url : `/storage/${user.avatar_url}`;
                } else {
                    el.src = '/assets/images/profile.png';
                }
            });
            
            // Load authenticated content
            loadAuthenticatedContent();
        }

        function updateUIForGuestUser() {
            console.log('Updating UI for guest user');
            
            // Show all guest elements (includes navbar buttons and sidebar)  
            document.querySelectorAll('.login-btn, .register-btn, .navbar-auth-guest, .sidebar-guest').forEach(el => {
                el.style.display = 'block';
            });
            
            // Hide all user elements
            document.querySelectorAll('.user-menu, .navbar-auth-user, .sidebar-authenticated').forEach(el => {
                el.style.display = 'none';
            });
            
            // Load guest content
            loadGuestContent();
        }
        
        // Load content for authenticated users
        function loadAuthenticatedContent() {
            loadTrendingHashtags();
            loadSuggestedUsers();
        }
        
        // Load content for guest users
        function loadGuestContent() {
            showFallbackTrending();
            showFallbackUsers();
        }

        // Sidebar management
        const Sidebar = {
            init: function () {
                // Don't load content immediately, wait for auth check
                console.log('Sidebar initialized, waiting for auth check...');
            }
        };
        
        // Load trending hashtags (for authenticated users only)
        function loadTrendingHashtags() {
            fetch('/trending-hashtags', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        renderTrendingHashtags(data.hashtags);
                    } else {
                        showFallbackTrending();
                    }
                })
                .catch(error => {
                    console.error('Error loading trending hashtags:', error);
                    showFallbackTrending();
                });
        }

        function loadSuggestedUsers() {
            fetch('/suggested-users', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        renderSuggestedUsers(data.users);
                    } else {
                        showFallbackUsers();
                    }
                })
                .catch(error => {
                    console.error('Error loading suggested users:', error);
                    showFallbackUsers();
                });
        }

        function renderTrendingHashtags(hashtags) {
            const container = document.getElementById('trending-container');
            let html = '';

            hashtags.forEach(hashtag => {
                const isAuthenticated = localStorage.getItem('user') !== null;
                html += `
                <div class="trending-item">
                    <div class="trending-item-info">
                        <strong>#${hashtag.name}</strong>
                        <small>${formatNumber(hashtag.post_count)} postingan</small>
                    </div>
                    <div class="follow-btn-container">
                        <button class="follow-btn ${hashtag.is_following ? 'outline' : ''}" 
                                onclick="${isAuthenticated ? `toggleHashtagFollow(${hashtag.id}, this)` : 'showLoginPrompt()'}"
                                data-hashtag-id="${hashtag.id}">
                            ${hashtag.is_following ? 'Mengikuti' : 'Ikuti'}
                        </button>
                    </div>
                </div>
            `;
            });

            container.innerHTML = html;
        }

        function renderSuggestedUsers(users) {
            const container = document.getElementById('suggested-users-container');
            let html = '';

            users.forEach(user => {
                const avatarUrl = user.avatar_url ? 
                    (user.avatar_url.startsWith('http') ? user.avatar_url : `/storage/${user.avatar_url}`) : 
                    '/assets/images/profile.png';
                const isAuthenticated = localStorage.getItem('user') !== null;
                
                html += `
                <div class="user">
                    <div class="user-avatar">
                        <img src="${avatarUrl}" alt="Profile" class="rounded-circle" width="40" height="40" />
                    </div>
                    <div class="user-info-container">
                        <span class="user-name">
                            <a href="/profile/${user.id}" class="text-decoration-none">
                                @${user.username}
                            </a>
                        </span>
                        <span class="user-description">${user.display_name || 'Community Member'}</span>
                        <span class="user-additional-info">${user.posts_count}+ posts • Member since ${formatDate(user.created_at)}</span>
                    </div>
                    <div class="follow-btn-container">
                        <button class="follow-btn ${user.is_following ? 'outline' : ''}" 
                                onclick="${isAuthenticated ? `toggleUserFollow(${user.id}, this)` : 'showLoginPrompt()'}"
                                data-user-id="${user.id}">
                            ${user.is_following ? 'Mengikuti' : 'Ikuti'}
                        </button>
                    </div>
                </div>
            `;
            });

            container.innerHTML = html;
        }

        function showFallbackTrending() {
            const container = document.getElementById('trending-container');
            const isAuthenticated = localStorage.getItem('user') !== null;
            container.innerHTML = `
            <div class="trending-item">
                <div class="trending-item-info">
                    <strong>#AIRevolusi</strong>
                    <small>23.000 postingan</small>
                </div>
                <div class="follow-btn-container">
                    <button class="follow-btn" onclick="${isAuthenticated ? 'showNotification(\'Fitur ini akan segera tersedia\', \'info\')' : 'showLoginPrompt()'}">Ikuti</button>
                </div>
            </div>
            <div class="trending-item">
                <div class="trending-item-info">
                    <strong>#Robotika2025</strong>
                    <small>11.500 postingan</small>
                </div>
                <div class="follow-btn-container">
                    <button class="follow-btn" onclick="${isAuthenticated ? 'showNotification(\'Fitur ini akan segera tersedia\', \'info\')' : 'showLoginPrompt()'}">Ikuti</button>
                </div>
            </div>
        `;
        }

        function showFallbackUsers() {
            const container = document.getElementById('suggested-users-container');
            const isAuthenticated = localStorage.getItem('user') !== null;
            container.innerHTML = `
            <div class="user">
                <div class="user-avatar">
                    <img src="/assets/images/profile.png" alt="Profile" class="rounded-circle" width="40" height="40" />
                </div>
                <div class="user-info-container">
                    <span class="user-name">@JavaScriptMaster</span>
                    <span class="user-description">JavaScript Developer</span>
                </div>
                <div class="follow-btn-container">
                    <button class="follow-btn" onclick="${isAuthenticated ? 'showNotification(\'Fitur ini akan segera tersedia\', \'info\')' : 'showLoginPrompt()'}">Ikuti</button>
                </div>
            </div>
        `;
        }

        function formatNumber(num) {
            if (num >= 1000000) {
                return (num / 1000000).toFixed(1) + 'M';
            } else if (num >= 1000) {
                return (num / 1000).toFixed(1) + 'K';
            }
            return num.toString();
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('id-ID', { month: 'short', year: 'numeric' });
        }

        // Follow functions
        function toggleHashtagFollow(hashtagId, button) {
            button.disabled = true;
            const originalText = button.textContent;
            button.textContent = 'Loading...';

            fetch(`/hashtag/${hashtagId}/follow`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.following) {
                            button.className = 'follow-btn outline';
                            button.textContent = 'Mengikuti';
                        } else {
                            button.className = 'follow-btn';
                            button.textContent = 'Ikuti';
                        }
                        showNotification(data.message, 'success');
                    } else {
                        showNotification(data.message, 'error');
                        button.textContent = originalText;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Terjadi kesalahan. Silakan coba lagi.', 'error');
                    button.textContent = originalText;
                })
                .finally(() => {
                    button.disabled = false;
                });
        }

        function toggleUserFollow(userId, button) {
            button.disabled = true;
            const originalText = button.textContent;
            button.textContent = 'Loading...';

            fetch(`/user/${userId}/follow`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.following) {
                            button.className = 'follow-btn outline';
                            button.textContent = 'Mengikuti';
                        } else {
                            button.className = 'follow-btn';
                            button.textContent = 'Ikuti';
                        }
                        showNotification(data.message, 'success');
                    } else {
                        showNotification(data.message, 'error');
                        button.textContent = originalText;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Terjadi kesalahan. Silakan coba lagi.', 'error');
                    button.textContent = originalText;
                })
                .finally(() => {
                    button.disabled = false;
                });
        }

        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} alert-dismissible fade show position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 1050; min-width: 300px;';
            notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

            document.body.appendChild(notification);

            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 5000);
        }

        function showLoginPrompt() {
            showNotification('Silakan login untuk mengikuti hashtag dan pengguna.', 'info');
            setTimeout(() => {
                showLoginModal();
            }, 1500);
        }

        // Initialize sidebar when page loads
        document.addEventListener('DOMContentLoaded', function () {
            Sidebar.init();
        });
    </script>
</body>

</html>