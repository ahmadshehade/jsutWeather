<!-- resources/views/auth/register.blade.php -->
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>إنشاء حساب - SkyCastPro</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --bg-deep: #0b1c2b;
            --bg-card: rgba(18, 35, 50, 0.8);
            --accent-primary: #3b9eff;
            --accent-secondary: #6ad4b4;
            --text-muted: rgba(235, 245, 255, 0.7);
            --border-subtle: rgba(255, 255, 255, 0.05);
            --radius-xl: 24px;
            --radius-lg: 18px;
            --transition-smooth: all 0.25s cubic-bezier(0.2, 0, 0, 1);
            --danger: #ff6b6b;
            --warning: #ffd93d;
            --success: #6ad4b4;
            --info: #3b9eff;
        }

        body {
            font-family: 'Inter', 'Cairo', system-ui, sans-serif;
            background: radial-gradient(125% 80% at 0% 100%, #102e42 0%, #031016 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            position: relative;
            overflow-x: hidden;
            color: white;
        }

        /* خلفية متحركة بجسيمات */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            opacity: 0.6;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            background: rgba(59, 158, 255, 0.15);
            border-radius: 50%;
            animation: float 15s infinite ease-in-out;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) translateX(0); }
            25% { transform: translateY(-30px) translateX(20px); }
            50% { transform: translateY(20px) translateX(-20px); }
            75% { transform: translateY(-20px) translateX(30px); }
        }

        /* موجات متحركة في الخلفية */
        .waves {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 150px;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%233b9eff" fill-opacity="0.1" d="M0,192L48,197.3C96,203,192,213,288,218.7C384,224,480,224,576,213.3C672,203,768,181,864,186.7C960,192,1056,224,1152,234.7C1248,245,1344,235,1392,229.3L1440,224L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"/></svg>') repeat-x bottom;
            background-size: cover;
            z-index: 0;
            animation: wave 10s linear infinite;
        }

        @keyframes wave {
            0% { background-position-x: 0; }
            100% { background-position-x: 1440px; }
        }

        /* الحاوية الرئيسية */
        .register-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 500px;
            animation: containerAppear 0.8s cubic-bezier(0.2, 0.9, 0.3, 1) forwards;
        }

        @keyframes containerAppear {
            0% { opacity: 0; transform: scale(0.95) translateY(30px); }
            100% { opacity: 1; transform: scale(1) translateY(0); }
        }

        /* البطاقة الزجاجية المحسنة */
        .glass-card {
            background: rgba(8, 28, 41, 0.7);
            backdrop-filter: blur(16px) saturate(180%);
            -webkit-backdrop-filter: blur(16px) saturate(180%);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-xl);
            box-shadow: 0 35px 55px -20px rgba(0, 0, 0, 0.8), inset 0 1px 1px rgba(255, 255, 255, 0.1);
            transition: all 0.4s ease;
            padding: 2.5rem 2rem;
            position: relative;
            overflow: hidden;
        }

        .glass-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 30% 30%, rgba(59, 158, 255, 0.2), transparent 70%);
            opacity: 0;
            transition: opacity 0.6s ease;
            pointer-events: none;
        }

        .glass-card:hover::before {
            opacity: 1;
        }

        .glass-card:hover {
            border-color: rgba(59, 158, 255, 0.3);
            box-shadow: 0 45px 65px -20px rgba(59, 158, 255, 0.3);
        }

        /* الشعار */
        .logo-wrapper {
            text-align: center;
            margin-bottom: 1.8rem;
        }

        .logo-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.2rem;
            border-radius: 30% 70% 70% 30% / 30% 55% 45% 70%;
            background: linear-gradient(145deg, #4facfe, #25d49c);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            transform: rotate(-5deg);
            box-shadow: 0 15px 30px rgba(0, 180, 255, 0.4);
            animation: logoPulse 4s infinite, logoFloat 6s infinite ease-in-out;
        }

        @keyframes logoPulse {
            0%, 100% { box-shadow: 0 15px 30px rgba(59, 158, 255, 0.6); }
            50% { box-shadow: 0 25px 50px rgba(59, 158, 255, 0.9); }
        }

        @keyframes logoFloat {
            0%, 100% { transform: rotate(-5deg) translateY(0); }
            50% { transform: rotate(-5deg) translateY(-8px); }
        }

        .logo-text {
            font-size: 2.4rem;
            font-weight: 700;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #fff, #b8e1ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.3rem;
        }

        .logo-sub {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        /* حقول الإدخال */
        .input-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .input-label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-muted);
            font-size: 0.9rem;
            font-weight: 500;
            transition: var(--transition-smooth);
        }

        .input-field {
            width: 100%;
            background: rgba(0, 20, 30, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 60px;
            padding: 0.9rem 1.5rem;
            color: white;
            font-size: 1rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(4px);
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .input-field:focus {
            outline: none;
            border-color: var(--accent-primary);
            background: rgba(0, 30, 50, 0.6);
            box-shadow: 0 0 0 4px rgba(59, 158, 255, 0.2), inset 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .input-field::placeholder {
            color: rgba(255, 255, 255, 0.25);
            font-size: 0.9rem;
        }

        /* أيقونة داخل الحقل */
        .input-icon {
            position: relative;
        }

        .input-icon .input-field {
            padding-right: 3rem;
        }

        .input-icon::after {
            content: '';
            position: absolute;
            left: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            width: 1.2rem;
            height: 1.2rem;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            opacity: 0.6;
        }

        .input-icon.name::after {
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" fill="%23ffffff" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>');
        }

        .input-icon.email::after {
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" fill="%23ffffff" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>');
        }

        .input-icon.password::after {
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" fill="%23ffffff" viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>');
        }

        .input-icon.confirm-password::after {
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" fill="%23ffffff" viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>');
            /* يمكن استخدام نفس أيقونة القفل أو تمييزها بلون مختلف */
        }

        /* خطأ التحقق */
        .error-message {
            color: var(--danger);
            font-size: 0.8rem;
            margin-top: 0.3rem;
            padding-right: 1rem;
            opacity: 0;
            animation: slideError 0.3s ease forwards;
        }

        @keyframes slideError {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* زر التسجيل */
        .register-btn {
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 60px;
            background: linear-gradient(145deg, #1f6fbb, #135b9e);
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(0, 50, 100, 0.4);
            margin-top: 0.5rem;
        }

        .register-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .register-btn:hover {
            background: linear-gradient(145deg, #2780d1, #1b6bb0);
            transform: scale(1.02);
            box-shadow: 0 15px 30px rgba(0, 100, 200, 0.5);
        }

        .register-btn:hover::before {
            left: 100%;
        }

        .register-btn:active {
            transform: scale(0.98);
        }

        /* رابط تسجيل الدخول */
        .login-link {
            text-align: center;
            margin-top: 2rem;
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .login-link a {
            color: var(--accent-secondary);
            text-decoration: none;
            font-weight: 500;
            margin-right: 0.3rem;
            transition: var(--transition-smooth);
            border-bottom: 1px solid transparent;
        }

        .login-link a:hover {
            color: white;
            border-bottom-color: var(--accent-secondary);
        }

        /* حقوق النشر */
        .copyright {
            text-align: center;
            margin-top: 2rem;
            color: rgba(235, 245, 255, 0.3);
            font-size: 0.7rem;
            letter-spacing: 0.5px;
        }

        /* تأثيرات متجاوبة */
        @media (max-width: 480px) {
            .glass-card { padding: 2rem 1.2rem; }
            .logo-icon { width: 60px; height: 60px; font-size: 2rem; }
            .logo-text { font-size: 2rem; }
        }
    </style>
</head>
<body>
    <!-- جسيمات خلفية متحركة -->
    <div class="particles">
        <div class="particle" style="width: 300px; height: 300px; top: 10%; left: 5%;"></div>
        <div class="particle" style="width: 200px; height: 200px; bottom: 15%; right: 10%; background: rgba(106, 212, 180, 0.1);"></div>
        <div class="particle" style="width: 150px; height: 150px; top: 40%; right: 20%;"></div>
        <div class="particle" style="width: 250px; height: 250px; bottom: 30%; left: 15%; background: rgba(106, 212, 180, 0.1);"></div>
    </div>

    <!-- موجات متحركة -->
    <div class="waves"></div>

    <div class="register-container">
        <div class="glass-card">
            <!-- الشعار -->
            <div class="logo-wrapper">
                <div class="logo-icon">☁️</div>
                <h1 class="logo-text">SkyCast<span style="font-weight:300;">Pro</span></h1>
                <p class="logo-sub">توقعات دقيقة · خرائط متحركة</p>
            </div>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- Name -->
                <div class="input-group input-icon name">
                    <label for="name" class="input-label">الاسم الكامل</label>
                    <input id="name"
                           type="text"
                           name="name"
                           value="{{ old('name') }}"
                           required
                           autofocus
                           autocomplete="name"
                           placeholder="محمد أحمد"
                           class="input-field">
                    @error('name')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Email Address -->
                <div class="input-group input-icon email">
                    <label for="email" class="input-label">البريد الإلكتروني</label>
                    <input id="email"
                           type="email"
                           name="email"
                           value="{{ old('email') }}"
                           required
                           autocomplete="username"
                           placeholder="your@email.com"
                           class="input-field">
                    @error('email')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password -->
                <div class="input-group input-icon password">
                    <label for="password" class="input-label">كلمة المرور</label>
                    <input id="password"
                           type="password"
                           name="password"
                           required
                           autocomplete="new-password"
                           placeholder="••••••••"
                           class="input-field">
                    @error('password')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="input-group input-icon confirm-password">
                    <label for="password_confirmation" class="input-label">تأكيد كلمة المرور</label>
                    <input id="password_confirmation"
                           type="password"
                           name="password_confirmation"
                           required
                           autocomplete="new-password"
                           placeholder="••••••••"
                           class="input-field">
                    @error('password_confirmation')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Register Button -->
                <button type="submit" class="register-btn">
                    إنشاء حساب
                </button>

                <!-- Login Link -->
                <div class="login-link">
                    لديك حساب بالفعل؟
                    <a href="{{ route('login') }}">تسجيل الدخول</a>
                </div>
            </form>
        </div>

        <div class="copyright">
            <span>© {{ date('Y') }} SkyCastPro. جميع الحقوق محفوظة.</span>
        </div>
    </div>

    <!-- تأثيرات JavaScript بسيطة -->
    <script>
        (function() {
            // إضافة تأثير توهج للحقل عند التركيز
            const inputs = document.querySelectorAll('.input-field');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.closest('.input-group').classList.add('focused');
                });
                input.addEventListener('blur', function() {
                    this.closest('.input-group').classList.remove('focused');
                });
            });

            // جسيمات إضافية ديناميكية (اختياري)
            function createExtraParticles() {
                const particles = document.querySelector('.particles');
                for (let i = 0; i < 5; i++) {
                    const particle = document.createElement('div');
                    particle.className = 'particle';
                    const size = Math.random() * 200 + 50;
                    particle.style.width = size + 'px';
                    particle.style.height = size + 'px';
                    particle.style.top = Math.random() * 100 + '%';
                    particle.style.left = Math.random() * 100 + '%';
                    particle.style.animationDelay = Math.random() * 5 + 's';
                    particle.style.animationDuration = (Math.random() * 10 + 10) + 's';
                    particles.appendChild(particle);
                }
            }
            createExtraParticles();
        })();
    </script>
</body>
</html>
