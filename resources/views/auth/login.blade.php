<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Nitra Campus Solar Weather Portal</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <!-- Custom Glassmorphism Stylesheet -->
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
</head>
<body class="login-page-bg">
    <!-- Floating background mesh elements -->
    <div class="bg-mesh"></div>

    <div class="d-flex flex-column align-items-center w-100">
        <!-- Top Theme and Status bar for Login page -->
        <div class="d-flex justify-content-between align-items-center mb-4 gap-4 px-4 py-2 glass-panel" style="width: 100%; max-width: 420px; border-radius: 12px;">
            <div class="text-secondary small">
                <i class="fa-regular fa-clock me-1"></i>
                <span id="loginClock">--:--:--</span>
            </div>
            <button class="theme-toggle-btn m-0" id="themeToggleLoginBtn" aria-label="Toggle Dark Mode">
                <i class="fa-solid fa-moon"></i>
            </button>
        </div>

        <!-- Frosted Glass Login Panel -->
        <div class="glass-card login-card">
            <!-- Brand header -->
            <div class="text-center mb-4">
                <div class="sidebar-brand-icon mx-auto mb-3" style="background: #ffffff; padding: 4px; border-radius: 12px; overflow: hidden; display: flex; align-items: center; justify-content: center; width: 60px; height: 60px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                    <img src="https://nitra.ac.in/wp-content/uploads/2024/08/cropped-cropped-Untitled-design-7.png" alt="Nitra Logo"
                         style="max-width: 100%; max-height: 100%; object-fit: contain; display: block;">
                </div>
                <h1 class="h3 font-heading fw-extrabold mb-1 text-primary">Nitra Campus</h1>
                <p class="text-warning fw-semibold small">Solar IoT Weather Portal</p>
            </div>

            <!-- Login Form -->
            <form action="{{ route('login') }}" method="POST" autocomplete="off">
                @csrf
                
                <!-- Email field -->
                <div class="mb-3">
                    <label for="email" class="form-label text-secondary fw-semibold small">Administrator Email</label>
                    <div class="input-group">
                        <span class="input-group-text form-control-glass border-end-0 text-muted" style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                            <i class="fa-solid fa-envelope"></i>
                        </span>
                        <input type="email" name="email" id="email" 
                               class="form-control form-control-glass border-start-0 @error('email') is-invalid @enderror" 
                               placeholder="admin@solar.yourdev.in" 
                               value="{{ old('email', 'admin@solar.yourdev.in') }}" 
                               required autofocus 
                               style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                        @error('email')
                        <div class="invalid-feedback text-danger mt-1 small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Password field -->
                <div class="mb-3">
                    <label for="password" class="form-label text-secondary fw-semibold small">Access Password</label>
                    <div class="input-group">
                        <span class="input-group-text form-control-glass border-end-0 text-muted" style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input type="password" name="password" id="password" 
                               class="form-control form-control-glass border-start-0 @error('password') is-invalid @enderror" 
                               placeholder="••••••••" 
                               value="admin123"
                               required 
                               style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                        @error('password')
                        <div class="invalid-feedback text-danger mt-1 small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Remember and Options -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input form-control-glass" type="checkbox" name="remember" id="remember" checked>
                        <label class="form-check-label text-secondary small" for="remember">
                            Remember session
                        </label>
                    </div>
                    <span class="text-muted small">v1.1.0</span>
                </div>

                <!-- Action Button -->
                <button type="submit" class="btn btn-premium-primary w-100 py-2.5 d-flex align-items-center justify-content-center gap-2">
                    <span>Authenticate</span>
                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Global Theme Switcher & Clock Javascript -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const btnTheme = document.getElementById('themeToggleLoginBtn');
            const htmlElement = document.documentElement;

            function updateThemeButton(theme) {
                const icon = theme === 'dark' ? 'fa-sun' : 'fa-moon';
                const classToAdd = theme === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
                if (btnTheme) btnTheme.innerHTML = `<i class="${classToAdd}"></i>`;
            }

            function toggleTheme() {
                const currentTheme = htmlElement.getAttribute('data-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                htmlElement.setAttribute('data-theme', newTheme);
                localStorage.setItem('solar_theme', newTheme);
                updateThemeButton(newTheme);
            }

            if (btnTheme) btnTheme.addEventListener('click', toggleTheme);

            const savedTheme = localStorage.getItem('solar_theme') || 'light';
            htmlElement.setAttribute('data-theme', savedTheme);
            updateThemeButton(savedTheme);

            // Clock
            function runClock() {
                const clockSpan = document.getElementById('loginClock');
                if (clockSpan) {
                    const now = new Date();
                    clockSpan.textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                }
            }
            setInterval(runClock, 1000);
            runClock();
        });
    </script>
</body>
</html>
