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
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">

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
        <!-- 1. Mobile Navigation Header -->
        <header class="mobile-nav">
            <button class="mobile-nav-toggle" id="sidebarToggleMobile" aria-label="Toggle Sidebar">
                <i class="fa-solid fa-bars"></i>
            </button>
            <div class="d-flex align-items-center gap-2" style="margin: 0;">
                <img src="/icons/icon-32.png" alt="Solar Weather Logo" style="width: 26px; height: 26px; border-radius: 7px;">
                <span class="sidebar-brand-name" style="font-size: 1.05rem; margin: 0;">Solar Weather</span>
            </div>
            <button class="theme-toggle-btn" id="themeToggleBtnMobile" aria-label="Toggle Dark Mode">
                <i class="fa-solid fa-moon"></i>
            </button>
        </header>

        <!-- 2. Responsive Sidebar -->
        <aside class="sidebar" id="sidebarLayout">
            <a href="{{ route('dashboard') }}" class="sidebar-brand">
                <div class="sidebar-brand-icon" style="background: #ffffff; padding: 3px; border-radius: 8px; overflow: hidden; display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
                    <img src="https://nitra.ac.in/wp-content/uploads/2024/08/cropped-cropped-Untitled-design-7.png" alt="Nitra Logo"
                         style="max-width: 100%; max-height: 100%; object-fit: contain; display: block;">
                </div>
                <span class="sidebar-brand-name" style="font-size: 0.85rem; line-height: 1.2; text-align: left;">
                    Nitra Campus<br>
                    <span class="text-warning" style="font-size: 0.72rem; font-weight: 600;">Solar Portal</span>
                </span>
            </a>

            <nav class="sidebar-menu-wrapper">
                <ul class="sidebar-menu">
                    <li>
                        <a href="{{ route('dashboard') }}" class="sidebar-link {{ Route::is('dashboard') ? 'active' : '' }}">
                            <i class="fa-solid fa-chart-line"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('devices.index') }}" class="sidebar-link {{ Route::is('devices.index') || Route::is('devices.analytics') ? 'active' : '' }}">
                            <i class="fa-solid fa-microchip"></i>
                            <span>Devices</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('settings.index') }}" class="sidebar-link {{ Route::is('settings.index') ? 'active' : '' }}">
                            <i class="fa-solid fa-sliders"></i>
                            <span>Settings</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <footer class="sidebar-footer">
                @auth
                <div class="sidebar-user">
                    <div class="sidebar-user-avatar">
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    </div>
                    <div class="sidebar-user-info">
                        <div class="sidebar-user-name">{{ Auth::user()->name }}</div>
                        <div class="sidebar-user-role">Administrator</div>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-premium-secondary w-100 d-flex align-items-center justify-content-center gap-2">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>Logout</span>
                    </button>
                </form>
                @endauth
            </footer>
        </aside>

        <!-- 3. Main Content Container -->
        <main class="main-content">
            <!-- Header bar for larger displays -->
            <div class="d-none d-lg-flex justify-content-end align-items-center mb-4 gap-3">
                <button class="theme-toggle-btn" id="themeToggleBtnDesktop" aria-label="Toggle Dark Mode">
                    <i class="fa-solid fa-moon"></i>
                </button>
                <div class="text-secondary small">
                    <i class="fa-regular fa-clock me-1"></i>
                    <span id="liveClock">--:--:--</span>
                </div>
            </div>

            <!-- Page-specific dynamic contents -->
            @yield('content')

            <!-- Branded Academic Footer -->
            <footer class="mt-5 mb-4 text-secondary" style="font-size: 0.75rem; opacity: 0.85;">
                <hr style="border-top: 1px solid rgba(255,255,255,0.08); margin-bottom: 20px;">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        © 2026 <strong class="text-white">Nitra Technical Campus</strong>. All Rights Reserved.
                    </div>
                    <div class="text-muted">
                        Developed by <span class="text-warning fw-semibold">Department of Electrical & Electronics Engineering</span>
                    </div>
                    <div class="d-flex gap-3">
                        <a href="https://nitra.ac.in" target="_blank" class="text-secondary text-decoration-none hover-warning" style="transition: color 0.2s;">nitra.ac.in</a>
                        <span>•</span>
                        <a href="#" class="text-secondary text-decoration-none">System Status</a>
                    </div>
                </div>
            </footer>
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
            const btnDesktop = document.getElementById('themeToggleBtnDesktop');
            const btnMobile = document.getElementById('themeToggleBtnMobile');
            const htmlElement = document.documentElement;

            function updateThemeButtons(theme) {
                const icon = theme === 'dark' ? 'fa-sun' : 'fa-moon';
                const classToAdd = theme === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
                
                if (btnDesktop) btnDesktop.innerHTML = `<i class="${classToAdd}"></i>`;
                if (btnMobile) btnMobile.innerHTML = `<i class="${classToAdd}"></i>`;
            }

            function toggleTheme() {
                const currentTheme = htmlElement.getAttribute('data-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                
                htmlElement.setAttribute('data-theme', newTheme);
                localStorage.setItem('solar_theme', newTheme);
                updateThemeButtons(newTheme);
            }

            // Bind click handlers
            if (btnDesktop) btnDesktop.addEventListener('click', toggleTheme);
            if (btnMobile) btnMobile.addEventListener('click', toggleTheme);

            // Read theme from storage on load
            const savedTheme = localStorage.getItem('solar_theme') || 'light';
            htmlElement.setAttribute('data-theme', savedTheme);
            updateThemeButtons(savedTheme);

            // Responsive Mobile Sidebar Toggle
            const btnMobileSidebar = document.getElementById('sidebarToggleMobile');
            const sidebarLayout = document.getElementById('sidebarLayout');

            if (btnMobileSidebar && sidebarLayout) {
                btnMobileSidebar.addEventListener('click', function (e) {
                    e.stopPropagation();
                    sidebarLayout.classList.toggle('show');
                });

                // Dismiss sidebar when tapping content area
                document.addEventListener('click', function (e) {
                    if (sidebarLayout.classList.contains('show') && !sidebarLayout.contains(e.target) && e.target !== btnMobileSidebar) {
                        sidebarLayout.classList.remove('show');
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
