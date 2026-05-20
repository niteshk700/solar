<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Solar IoT Weather Platform')</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome 6 Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    
    <!-- Custom Glassmorphism Stylesheet -->
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    
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
            <div class="sidebar-brand-name" style="font-size: 1.1rem; margin: 0;">Solar Weather</div>
            <button class="theme-toggle-btn" id="themeToggleBtnMobile" aria-label="Toggle Dark Mode">
                <i class="fa-solid fa-moon"></i>
            </button>
        </header>

        <!-- 2. Responsive Sidebar -->
        <aside class="sidebar" id="sidebarLayout">
            <a href="{{ route('dashboard') }}" class="sidebar-brand">
                <div class="sidebar-brand-icon">
                    <i class="fa-solid fa-solar-panel"></i>
                </div>
                <span class="sidebar-brand-name">Solar Weather</span>
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
        </main>
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
</body>
</html>
