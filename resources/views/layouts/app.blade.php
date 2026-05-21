<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>@yield('title', 'Solar IoT Weather Platform')</title>

    <!-- ===== PWA Meta Tags ===== -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#3B82F6" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#0f172a" media="(prefers-color-scheme: dark)">
    <meta name="mobile-web-app-capable" content="yes">
    <!-- Favicons (same icon as PWA) -->
    <link rel="icon" type="image/png" sizes="16x16" href="/icons/icon-16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/icons/icon-32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/icons/icon-96.png">
    <link rel="shortcut icon" href="/icons/icon-32.png">
    <!-- Apple PWA Support -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Solar Weather">
    <link rel="apple-touch-icon" href="/icons/icon-152.png">
    <link rel="apple-touch-icon" sizes="192x192" href="/icons/icon-192.png">
    <!-- Microsoft PWA -->
    <meta name="msapplication-TileImage" content="/icons/icon-144.png">
    <meta name="msapplication-TileColor" content="#0f172a">
    <!-- SEO -->
    <meta name="description" content="Real-time solar IoT telemetry and environmental monitoring dashboard for ESP8266 weather stations.">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome 6 Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    
    <!-- Custom Glassmorphism Stylesheet -->
    <link href="{{ asset('css/style.css') }}?v={{ time() }}" rel="stylesheet">

    <style>
    /* PWA Install Banner */
    #pwa-install-banner {
        position: fixed;
        bottom: -120px;
        left: 50%;
        transform: translateX(-50%);
        width: calc(100% - 32px);
        max-width: 480px;
        background: rgba(15, 23, 42, 0.92);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(59, 130, 246, 0.3);
        border-radius: 20px;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        gap: 14px;
        z-index: 9999;
        transition: bottom 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: 0 -4px 40px rgba(59, 130, 246, 0.15), 0 8px 32px rgba(0,0,0,0.4);
    }
    #pwa-install-banner.show { bottom: 24px; }
    #pwa-install-banner .pwa-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        flex-shrink: 0;
        object-fit: cover;
    }
    #pwa-install-banner .pwa-text { flex: 1; min-width: 0; }
    #pwa-install-banner .pwa-title {
        font-size: 0.92rem;
        font-weight: 700;
        color: #f1f5f9;
        margin-bottom: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    #pwa-install-banner .pwa-subtitle {
        font-size: 0.75rem;
        color: rgba(148, 163, 184, 0.9);
    }
    #pwa-install-banner .pwa-install-btn {
        background: linear-gradient(135deg, #3B82F6, #6366f1);
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 10px 18px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        flex-shrink: 0;
        transition: transform 0.2s, box-shadow 0.2s;
        box-shadow: 0 4px 14px rgba(59, 130, 246, 0.4);
    }
    #pwa-install-banner .pwa-install-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(59, 130, 246, 0.5);
    }
    #pwa-install-banner .pwa-dismiss-btn {
        background: transparent;
        border: none;
        color: rgba(148, 163, 184, 0.7);
        padding: 6px;
        cursor: pointer;
        border-radius: 6px;
        flex-shrink: 0;
        font-size: 1rem;
        transition: color 0.2s;
    }
    #pwa-install-banner .pwa-dismiss-btn:hover { color: #f1f5f9; }
    </style>
    
    @yield('styles')
</head>
<body>
    <!-- Background mesh elements -->
    <div class="bg-mesh"></div>

    <div class="app-wrapper">
        <!-- Sticky Horizontal Glass Navbar -->
        <nav class="navbar navbar-expand-lg navbar-glass">
            <div class="container-xl">
                <!-- Branding Brand & Logo -->
                <a href="{{ route('dashboard') }}" class="navbar-brand-custom">
                    <div style="background: #ffffff; padding: 3px; border-radius: 8px; overflow: hidden; display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
                        <img src="https://nitra.ac.in/wp-content/uploads/2024/08/cropped-cropped-Untitled-design-7.png" alt="Nitra Logo"
                             style="max-width: 100%; max-height: 100%; object-fit: contain; display: block;">
                    </div>
                    <span class="d-none d-sm-inline text-primary">Nitra Technical Campus <span class="text-warning">Solar Portal</span></span>
                    <span class="d-inline d-sm-none text-warning">Solar</span>
                </a>

                <!-- Collapsible Navigation Links (Centered) -->
                <div class="collapse navbar-collapse-glass" id="navbarContent">
                    <div class="navbar-nav mx-auto gap-1">
                        <a href="{{ route('dashboard') }}" class="nav-link-custom {{ Route::is('dashboard') ? 'active' : '' }}">
                            <i class="fa-solid fa-chart-line"></i>
                            <span>Dashboard</span>
                        </a>
                        <a href="{{ route('devices.index') }}" class="nav-link-custom {{ Route::is('devices.index') || Route::is('devices.analytics') ? 'active' : '' }}">
                            <i class="fa-solid fa-microchip"></i>
                            <span>Devices</span>
                        </a>
                        <a href="{{ route('settings.index') }}" class="nav-link-custom {{ Route::is('settings.index') ? 'active' : '' }}">
                            <i class="fa-solid fa-sliders"></i>
                            <span>Settings</span>
                        </a>
                    </div>
                </div>

                <!-- Right Side Actions: Clock, Theme, Profile Dropdown, Mobile menu toggler -->
                <div class="d-flex align-items-center gap-3">
                    <!-- Clock -->
                    <div class="text-secondary small d-none d-md-flex align-items-center gap-1">
                        <i class="fa-regular fa-clock"></i>
                        <span id="liveClock">--:--:--</span>
                    </div>

                    <!-- Theme Toggle Button -->
                    <button class="theme-toggle-btn m-0" id="themeToggleNavbarBtn" aria-label="Toggle Dark Mode">
                        <i class="fa-solid fa-moon"></i>
                    </button>

                    <!-- User Profile Dropdown -->
                    @auth
                    <div class="dropdown">
                        <div class="profile-avatar-custom dropdown-toggle" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        </div>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-glass" aria-labelledby="profileDropdown">
                            <li class="px-3 py-2 border-bottom border-secondary-subtle mb-1">
                                <div class="fw-bold small text-primary">{{ Auth::user()->name }}</div>
                                <div class="text-muted" style="font-size: 0.72rem;">Administrator</div>
                            </li>
                            <li>
                                <a class="dropdown-item dropdown-item-custom" href="{{ route('settings.index') }}">
                                    <i class="fa-solid fa-sliders me-2"></i> Settings
                                </a>
                            </li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST" class="d-block w-100">
                                    @csrf
                                    <button type="submit" class="dropdown-item dropdown-item-custom text-danger w-100 border-0 bg-transparent text-start">
                                        <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                    @endauth

                    <!-- Mobile Toggler Menu button -->
                    <button class="navbar-toggler d-lg-none text-secondary border-0 p-1" type="button" id="mobileNavbarToggle" aria-label="Toggle Navigation">
                        <i class="fa-solid fa-bars" style="font-size: 1.3rem;"></i>
                    </button>
                </div>
            </div>
        </nav>

        <!-- Main Content Area -->
        <main class="main-content">
            <div class="container-xl">
                <!-- Page-specific dynamic contents -->
                @yield('content')

                <!-- Branded Academic Footer -->
                <footer class="mt-5 mb-4 text-secondary" style="font-size: 0.75rem; opacity: 0.85;">
                    <hr style="border-top: 1px solid var(--glass-border); margin-bottom: 20px;">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div>
                            © 2026 <strong class="text-black">Nitra Technical Campus</strong>. All Rights Reserved.
                        </div>
                        <div class="text-muted">
                            Developed by <span class="text-warning fw-semibold">Department of Computer Science and Engineering</span>
                        </div>
                        <div class="d-flex gap-3">
                            <a href="https://nitra.ac.in" target="_blank" class="text-secondary text-decoration-none hover-warning" style="transition: color 0.2s;">nitra.ac.in</a>
                            <span>•</span>
                            <a href="#" class="text-secondary text-decoration-none">System Status</a>
                        </div>
                    </div>
                </footer>
            </div>
        </main>
    </div>

    <!-- PWA Install Banner -->
    <div id="pwa-install-banner" role="dialog" aria-label="Install App">
        <img src="/icons/icon-192.png" alt="Solar Weather Icon" class="pwa-icon">
        <div class="pwa-text">
            <div class="pwa-title">Install Solar Weather</div>
            <div class="pwa-subtitle">Add to home screen for instant access</div>
        </div>
        <button class="pwa-install-btn" id="pwa-install-btn">
            <i class="fa-solid fa-download me-1"></i> Install
        </button>
        <button class="pwa-dismiss-btn" id="pwa-dismiss-btn" aria-label="Dismiss">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Global Theme Switcher & Clock Javascript -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Theme toggling elements
            const btnTheme = document.getElementById('themeToggleNavbarBtn');
            const htmlElement = document.documentElement;

            function updateThemeButtons(theme) {
                const icon = theme === 'dark' ? 'fa-sun' : 'fa-moon';
                const classToAdd = theme === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
                
                if (btnTheme) btnTheme.innerHTML = `<i class="${classToAdd}"></i>`;
            }

            function toggleTheme() {
                const currentTheme = htmlElement.getAttribute('data-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                
                htmlElement.setAttribute('data-theme', newTheme);
                localStorage.setItem('solar_theme', newTheme);
                updateThemeButtons(newTheme);
            }

            // Bind click handlers
            if (btnTheme) btnTheme.addEventListener('click', toggleTheme);

            // Read theme from storage on load
            const savedTheme = localStorage.getItem('solar_theme') || 'light';
            htmlElement.setAttribute('data-theme', savedTheme);
            updateThemeButtons(savedTheme);

            // Responsive Mobile Navbar Toggle
            const mobileToggle = document.getElementById('mobileNavbarToggle');
            const navbarCollapse = document.getElementById('navbarContent');

            if (mobileToggle && navbarCollapse) {
                mobileToggle.addEventListener('click', function (e) {
                    e.stopPropagation();
                    navbarCollapse.classList.toggle('show');
                });

                // Dismiss sidebar when tapping content area
                document.addEventListener('click', function (e) {
                    if (navbarCollapse.classList.contains('show') && !navbarCollapse.contains(e.target) && e.target !== mobileToggle) {
                        navbarCollapse.classList.remove('show');
                    }
                });
            }

            // Real-time Clock
            function runClock() {
                const clockSpan = document.getElementById('liveClock');
                if (clockSpan) {
                    const now = new Date();
                    clockSpan.textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                }
            }
            setInterval(runClock, 1000);
            runClock();
        });
    </script>
    
    @yield('scripts')

    <!-- ===== PWA Service Worker Registration & Install Prompt ===== -->
    <script>
    (function() {
        // Register Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('[PWA] Service Worker registered. Scope:', reg.scope))
                    .catch(err => console.warn('[PWA] Service Worker registration failed:', err));
            });
        }

        // PWA Install Banner Logic
        let deferredPrompt = null;
        const banner = document.getElementById('pwa-install-banner');
        const installBtn = document.getElementById('pwa-install-btn');
        const dismissBtn = document.getElementById('pwa-dismiss-btn');

        // Listen for the browser's native install prompt
        window.addEventListener('beforeinstallprompt', function(e) {
            e.preventDefault();
            deferredPrompt = e;

            // Only show if not already dismissed this session
            const dismissed = sessionStorage.getItem('pwa_banner_dismissed');
            if (!dismissed && banner) {
                setTimeout(() => banner.classList.add('show'), 1500);
            }
        });

        // Handle Install button click
        if (installBtn) {
            installBtn.addEventListener('click', function() {
                if (!deferredPrompt) return;
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then(choice => {
                    if (choice.outcome === 'accepted') {
                        console.log('[PWA] User accepted the install prompt!');
                    }
                    deferredPrompt = null;
                    banner.classList.remove('show');
                });
            });
        }

        // Handle Dismiss button click
        if (dismissBtn) {
            dismissBtn.addEventListener('click', function() {
                banner.classList.remove('show');
                sessionStorage.setItem('pwa_banner_dismissed', '1');
            });
        }

        // Hide banner once the app is installed
        window.addEventListener('appinstalled', function() {
            console.log('[PWA] App successfully installed to home screen!');
            if (banner) banner.classList.remove('show');
        });
    })();
    </script>
</body>
</html>
