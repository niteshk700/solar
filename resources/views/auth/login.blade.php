<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Sign In — Solar IoT Platform</title>

    <!-- Favicon & PWA Icons -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('icons/icon-32.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192.png') }}">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;

            /* Light Monochrome Palette */
            --mono-bg: #f8f9fa;
            --mono-dots: rgba(0, 0, 0, 0.04);
            --mono-card: #ffffff;
            --mono-border: #e4e4e7;
            --mono-border-subtle: #f4f4f5;
            --mono-text-primary: #09090b;
            --mono-text-secondary: #52525b;
            --mono-text-muted: #a1a1aa;
            --mono-input-bg: #ffffff;
            --mono-input-border: #d4d4d8;
            --mono-input-focus: #09090b;
            --mono-btn-bg: #09090b;
            --mono-btn-text: #ffffff;
            --mono-btn-hover: #27272a;
            --mono-pill-bg: #f4f4f5;
            --mono-pill-border: #e4e4e7;
            --mono-pill-text: #71717a;
            --mono-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.05), 0 1px 3px rgba(0, 0, 0, 0.03);
            --mono-logo-bg: #ffffff;
            --mono-logo-border: #e4e4e7;
        }

        [data-theme="dark"] {
            /* Dark Monochrome Palette */
            --mono-bg: #09090b;
            --mono-dots: rgba(255, 255, 255, 0.04);
            --mono-card: #121215;
            --mono-border: #27272a;
            --mono-border-subtle: #1f1f23;
            --mono-text-primary: #fafafa;
            --mono-text-secondary: #a1a1aa;
            --mono-text-muted: #71717a;
            --mono-input-bg: #18181b;
            --mono-input-border: #2e2e33;
            --mono-input-focus: #fafafa;
            --mono-btn-bg: #fafafa;
            --mono-btn-text: #09090b;
            --mono-btn-hover: #e4e4e7;
            --mono-pill-bg: #18181b;
            --mono-pill-border: #27272a;
            --mono-pill-text: #a1a1aa;
            --mono-shadow: 0 24px 50px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.05);
            --mono-logo-bg: #18181b;
            --mono-logo-border: #27272a;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-sans);
            background-color: var(--mono-bg);
            background-image: radial-gradient(var(--mono-dots) 1px, transparent 0);
            background-size: 24px 24px;
            color: var(--mono-text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            transition: background-color 0.25s ease, color 0.25s ease;
        }

        /* Top Bar */
        .login-topbar {
            width: 100%;
            max-width: 400px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .mono-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            border-radius: 9999px;
            background: var(--mono-pill-bg);
            border: 1px solid var(--mono-pill-border);
            color: var(--mono-pill-text);
            font-size: 0.78rem;
            font-weight: 500;
            letter-spacing: 0.02em;
        }

        .mono-pill .status-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--mono-text-primary);
            opacity: 0.6;
        }

        .theme-toggle-btn {
            background: var(--mono-pill-bg);
            border: 1px solid var(--mono-pill-border);
            color: var(--mono-text-secondary);
            width: 34px;
            height: 34px;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .theme-toggle-btn:hover {
            color: var(--mono-text-primary);
            border-color: var(--mono-text-muted);
            transform: scale(1.05);
        }

        /* Main Login Card */
        .mono-login-card {
            width: 100%;
            max-width: 400px;
            background: var(--mono-card);
            border: 1px solid var(--mono-border);
            border-radius: 20px;
            padding: 36px 32px;
            box-shadow: var(--mono-shadow);
            transition: all 0.25s ease;
        }

        /* Brand / Logo Section */
        .logo-container {
            width: 68px;
            height: 68px;
            border-radius: 16px;
            background: var(--mono-logo-bg);
            border: 1px solid var(--mono-logo-border);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px auto;
            padding: 8px;
            transition: all 0.25s ease;
        }

        .logo-img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            display: block;
        }

        .brand-title {
            font-size: 1.28rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: var(--mono-text-primary);
            margin-bottom: 6px;
            text-align: center;
        }

        .brand-subtitle {
            font-size: 0.85rem;
            color: var(--mono-text-secondary);
            text-align: center;
            margin-bottom: 28px;
            line-height: 1.4;
        }

        /* Form Controls */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label-mono {
            display: block;
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--mono-text-secondary);
            margin-bottom: 8px;
            letter-spacing: 0.01em;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon-left {
            position: absolute;
            left: 14px;
            color: var(--mono-text-muted);
            font-size: 0.88rem;
            pointer-events: none;
            transition: color 0.2s ease;
        }

        .mono-input {
            width: 100%;
            height: 44px;
            background: var(--mono-input-bg);
            border: 1px solid var(--mono-input-border);
            border-radius: 10px;
            padding: 0 14px 0 40px;
            font-size: 0.9rem;
            color: var(--mono-text-primary);
            font-family: inherit;
            transition: all 0.2s ease;
            outline: none;
        }

        .mono-input::placeholder {
            color: var(--mono-text-muted);
            opacity: 0.7;
        }

        .mono-input:focus {
            border-color: var(--mono-input-focus);
            box-shadow: 0 0 0 3px rgba(125, 125, 125, 0.12);
        }

        .mono-input:focus + .input-icon-left {
            color: var(--mono-text-primary);
        }

        .password-toggle-btn {
            position: absolute;
            right: 12px;
            background: transparent;
            border: none;
            color: var(--mono-text-muted);
            font-size: 0.88rem;
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s ease;
        }

        .password-toggle-btn:hover {
            color: var(--mono-text-primary);
        }

        /* Error States */
        .is-error {
            border-color: #ef4444 !important;
        }

        .error-message {
            font-size: 0.78rem;
            color: #ef4444;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Checkbox & Options */
        .options-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            font-size: 0.82rem;
        }

        .custom-checkbox-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--mono-text-secondary);
            cursor: pointer;
            user-select: none;
        }

        .custom-checkbox-label input[type="checkbox"] {
            appearance: none;
            -webkit-appearance: none;
            width: 16px;
            height: 16px;
            border: 1px solid var(--mono-input-border);
            border-radius: 4px;
            background: var(--mono-input-bg);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s ease;
            position: relative;
        }

        .custom-checkbox-label input[type="checkbox"]:checked {
            background: var(--mono-btn-bg);
            border-color: var(--mono-btn-bg);
        }

        .custom-checkbox-label input[type="checkbox"]:checked::after {
            content: "\f00c";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            font-size: 10px;
            color: var(--mono-btn-text);
        }

        /* Mono Submit Button */
        .mono-submit-btn {
            width: 100%;
            height: 44px;
            background: var(--mono-btn-bg);
            color: var(--mono-btn-text);
            border: none;
            border-radius: 10px;
            font-size: 0.88rem;
            font-weight: 600;
            letter-spacing: 0.01em;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .mono-submit-btn:hover {
            background: var(--mono-btn-hover);
            transform: translateY(-1px);
        }

        .mono-submit-btn:active {
            transform: translateY(0) scale(0.99);
        }

        /* Bottom Footer */
        .login-footer {
            margin-top: 24px;
            text-align: center;
            font-size: 0.76rem;
            color: var(--mono-text-muted);
            letter-spacing: 0.02em;
        }
    </style>
</head>
<body>

    <!-- Header bar with clock & theme switch -->
    <div class="login-topbar">
        <div class="mono-pill">
            <span class="status-dot"></span>
            <span id="loginClock">--:--:--</span>
        </div>
        <button class="theme-toggle-btn" id="themeToggleBtn" aria-label="Toggle light or dark theme" title="Toggle theme">
            <i class="fa-solid fa-sun" id="themeIcon"></i>
        </button>
    </div>

    <!-- Login Card -->
    <div class="mono-login-card">
        <!-- Logo -->
        <div class="logo-container">
            <img src="{{ asset('icons/logo.png') }}" alt="Solar Logo" class="logo-img">
        </div>

        <!-- Headings -->
        <h1 class="brand-title">Solar Telemetry</h1>
        <p class="brand-subtitle">Enter your credentials to access the IoT platform</p>

        <!-- Form -->
        <form action="{{ route('login') }}" method="POST" autocomplete="off">
            @csrf

            <!-- Email Field -->
            <div class="form-group">
                <label for="email" class="form-label-mono">Email Address</label>
                <div class="input-wrapper">
                    <input type="email" 
                           name="email" 
                           id="email" 
                           class="mono-input @error('email') is-error @enderror" 
                           placeholder="admin@solar.yourdev.in" 
                           value="{{ old('email', 'admin@solar.yourdev.in') }}" 
                           required 
                           autofocus>
                    <i class="fa-regular fa-envelope input-icon-left"></i>
                </div>
                @error('email')
                    <div class="error-message">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span>{{ $message }}</span>
                    </div>
                @enderror
            </div>

            <!-- Password Field -->
            <div class="form-group">
                <label for="password" class="form-label-mono">Password</label>
                <div class="input-wrapper">
                    <input type="password" 
                           name="password" 
                           id="password" 
                           class="mono-input @error('password') is-error @enderror" 
                           placeholder="••••••••" 
                           value="admin123" 
                           required 
                           style="padding-right: 40px;">
                    <i class="fa-regular fa-lock input-icon-left"></i>
                    <button type="button" class="password-toggle-btn" id="togglePasswordBtn" aria-label="Toggle password visibility">
                        <i class="fa-regular fa-eye" id="togglePasswordIcon"></i>
                    </button>
                </div>
                @error('password')
                    <div class="error-message">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span>{{ $message }}</span>
                    </div>
                @enderror
            </div>

            <!-- Options -->
            <div class="options-row">
                <label class="custom-checkbox-label">
                    <input type="checkbox" name="remember" id="remember" checked>
                    <span>Remember this session</span>
                </label>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="mono-submit-btn">
                <span>Sign In</span>
                <i class="fa-solid fa-arrow-right-long"></i>
            </button>
        </form>
    </div>

    <!-- Minimalist Footer -->
    <div class="login-footer">
        <span>SOLAR IOT WEATHER PLATFORM &bull; SECURE ACCESS</span>
    </div>

    <!-- Scripts: Clock, Theme & Password Visibility -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const htmlElement = document.documentElement;
            const themeBtn = document.getElementById('themeToggleBtn');
            const themeIcon = document.getElementById('themeIcon');
            const togglePasswordBtn = document.getElementById('togglePasswordBtn');
            const passwordInput = document.getElementById('password');
            const togglePasswordIcon = document.getElementById('togglePasswordIcon');
            const clockSpan = document.getElementById('loginClock');

            // 1. Password Visibility Toggle
            if (togglePasswordBtn && passwordInput) {
                togglePasswordBtn.addEventListener('click', function () {
                    const isPassword = passwordInput.getAttribute('type') === 'password';
                    passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                    togglePasswordIcon.className = isPassword ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
                });
            }

            // 2. Theme Toggle (Sync with saved preference, default dark/mono)
            function applyTheme(theme) {
                htmlElement.setAttribute('data-theme', theme);
                localStorage.setItem('solar_theme', theme);
                if (themeIcon) {
                    themeIcon.className = theme === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
                }
            }

            const savedTheme = localStorage.getItem('solar_theme') || 'dark';
            applyTheme(savedTheme);

            if (themeBtn) {
                themeBtn.addEventListener('click', function () {
                    const currentTheme = htmlElement.getAttribute('data-theme') || 'dark';
                    applyTheme(currentTheme === 'dark' ? 'light' : 'dark');
                });
            }

            // 3. Live Clock
            function updateClock() {
                if (clockSpan) {
                    const now = new Date();
                    clockSpan.textContent = now.toLocaleTimeString([], { 
                        hour: '2-digit', 
                        minute: '2-digit', 
                        second: '2-digit', 
                        hour12: false 
                    });
                }
            }
            setInterval(updateClock, 1000);
            updateClock();
        });
    </script>
</body>
</html>
