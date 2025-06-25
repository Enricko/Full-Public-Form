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

    <nav class="navbar navbar-expand-lg navbar-dark bg-danger">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">PublicForum</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarContent">
                <form class="d-flex ms-auto" id="navbar-search-form">
                    <input class="form-control me-2" type="search" id="navbar-search-input" placeholder="Search..." aria-label="Search" />
                    <button class="btn btn-light" type="submit">Search</button>
                </form>
                <button class="btn btn-outline-light ms-3" type="button" onclick="showLoginModal()">Login</button>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-3">
        <div class="row">

            <div class="col-md-2">
                <div class="sidebar">

                    <a class="post-button" href="{{ route('tambah') }}">
                        <i class="fas fa-edit"></i> Posting Baru
                    </a>
                    <ul class="nav flex-column">
                        <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">🏠
                                Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('profile') }}">👤 Profile</a></li>
                        <li class="nav-item"><a class="nav-link" href="javascript:void(0)" onclick="loadPage('search')">🔍 Search</a></li>
                        <li class="nav-item"><a class="nav-link" href="javascript:void(0)" onclick="loadPage('settings')">⚙️ Settings</a></li>
                        <li class="nav-item"><a class="nav-link" href="javascript:void(0)" onclick="loadPage('comment')">⚙️ Comment</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('about') }}" onclick="loadPage('about')">ℹ️ About Us</a></li>
                    </ul>
                </div>
            </div>

            <div class="col-md-6 mx-5" id="page-content">
                @yield('content')
            </div>

            <div class="col-md-2">

                <div class="right-sidebar">

                    <div class="trending">
                        <h3>Sedang Hangat 🔥</h3>

                        <div class="trending-item">
                            <div class="trending-item-info">
                                <strong>#AIRevolusi</strong>
                                <small>23.000 postingan</small>
                            </div>
                            <div class="follow-btn-container">
                                <button class="follow-btn">Ikuti</button>
                            </div>
                        </div>

                        <div class="trending-item">
                            <div class="trending-item-info">
                                <strong>#Robotika2025</strong>
                                <small>11.500 postingan</small>
                            </div>
                            <div class="follow-btn-container">
                                <button class="follow-btn">Ikuti</button>
                            </div>
                        </div>

                        <div class="trending-item">
                            <div class="trending-item-info">
                                <strong>#DesainWeb</strong>
                                <small>9.830 postingan</small>
                            </div>
                            <div class="follow-btn-container">
                                <button class="follow-btn">Ikuti</button>
                            </div>
                        </div>

                        <div class="trending-item">
                            <div class="trending-item-info">
                                <strong>#StartupTech</strong>
                                <small>8.210 postingan</small>
                            </div>
                            <div class="follow-btn-container">
                                <button class="follow-btn">Ikuti</button>
                            </div>
                        </div>

                        <div class="trending-item">
                            <div class="trending-item-info">
                                <strong>#XThreadsDesign</strong>
                                <small>7.980 postingan</small>
                            </div>
                            <div class="follow-btn-container">
                                <button class="follow-btn">Ikuti</button>
                            </div>
                        </div>

                        <div class="trending-item">
                            <div class="trending-item-info">
                                <strong>#UX2025</strong>
                                <small>6.100 postingan</small>
                            </div>
                            <div class="follow-btn-container">
                                <button class="follow-btn">Ikuti</button>
                            </div>
                        </div>

                        <div class="trending-item">
                            <div class="trending-item-info">
                                <strong>#NgodingSantai</strong>
                                <small>5.000 postingan</small>
                            </div>
                            <div class="follow-btn-container">
                                <button class="follow-btn">Ikuti</button>
                            </div>
                        </div>

                        <div class="trending-item">
                            <div class="trending-item-info">
                                <strong>#NextUITrend</strong>
                                <small>4.780 postingan</small>
                            </div>
                            <div class="follow-btn-container">
                                <button class="follow-btn">Ikuti</button>
                            </div>
                        </div>

                        <div class="trending-item">
                            <div class="trending-item-info">
                                <strong>#GPTWebDev</strong>
                                <small>3.400 postingan</small>
                            </div>
                            <div class="follow-btn-container">
                                <button class="follow-btn">Ikuti</button>
                            </div>
                        </div>
                    </div>

                    <div class="suggested-users">
                        <h3>Rekomendasi Akun</h3>

                        <div class="user">
                            <div class="user-avatar">
                                <img src="../assets/images/profile.png" alt="Profile" class="rounded-circle" width="40" height="40" />
                            </div>
                            <div class="user-info-container">
                                <span class="user-name">@JavaScriptMaster</span>
                                <span class="user-description">JavaScript Developer & Instructor</span>
                            </div>
                            <div class="follow-btn-container">
                                <button class="follow-btn">Ikuti</button>
                            </div>
                        </div>

                        <div class="user">
                            <div class="user-avatar">
                                <img src="../assets/images/profile.png" alt="Profile" class="rounded-circle" width="40" height="40" />
                            </div>
                            <div class="user-info-container">
                                <span class="user-name">@desainin</span>
                                <span class="user-description">JavaScript Developer & Instructor</span>
                                <span class="user-additional-info">500+ posts • Member since January 2023</span>
                            </div>
                            <div class="follow-btn-container">
                                <button class="follow-btn">Ikuti</button>
                            </div>
                        </div>

                        <div class="user">
                            <div class="user-avatar">
                                <img src="../assets/images/profile.png" alt="Profile" class="rounded-circle" width="40" height="40" />
                            </div>
                            <div class="user-info-container">
                                <span class="user-name">@techbabe</span>
                                <span class="user-description">JavaScript Developer & Instructor</span>
                                <span class="user-additional-info">500+ posts • Member since January 2023</span>
                            </div>
                            <div class="follow-btn-container">
                                <button class="follow-btn">Ikuti</button>
                            </div>
                        </div>

                        <div class="user">
                            <div class="user-avatar">
                                <img src="../assets/images/profile.png" alt="Profile" class="rounded-circle" width="40" height="40" />
                            </div>
                            <div class="user-info-container">
                                <span class="user-name">@aibot</span>
                                <span class="user-description">JavaScript Developer & Instructor</span>
                                <span class="user-additional-info">500+ posts • Member since January 2023</span>
                            </div>
                            <div class="follow-btn-container">
                                <button class="follow-btn">Ikuti</button>
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
</body>

</html>