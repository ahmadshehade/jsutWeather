<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="https://openweathermap.org/img/wn/02d.png">
    <title>الملف الشخصي - SkyCastPro</title>
    <!-- خطوط حديثة + Bootstrap RTL + أيقونات -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,400..700;1,14..32,400..700&family=Cairo:wght@300..700&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Alpine.js للمودال -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
            background: radial-gradient(125% 80% at 0% 100%, #102e42 0%, #031016 100%);
            font-family: "Inter", "Cairo", system-ui, sans-serif;
            color: white;
            min-height: 100vh;
            padding: 2rem 1rem;
            line-height: 1.5;
            backdrop-filter: blur(2px);
        }

        .glass-panel {
            background: rgba(8, 28, 41, 0.65);
            backdrop-filter: blur(12px) saturate(180%);
            -webkit-backdrop-filter: blur(12px) saturate(180%);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-xl);
            box-shadow: 0 25px 45px -12px rgba(0, 0, 0, 0.6), inset 0 1px 1px rgba(255, 255, 255, 0.08);
            transition: var(--transition-smooth);
        }

        .glass-panel:hover {
            border-color: rgba(59, 158, 255, 0.3);
            box-shadow: 0 30px 55px -12px rgba(59, 158, 255, 0.2);
        }

        .profile-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* شريط التنقل العلوي (مطابق للصفحة الرئيسية) */
        .navbar-glass {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.7rem 1.5rem;
            margin-bottom: 2rem;
            border-radius: 80px;
            background: rgba(5, 20, 30, 0.55);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.03);
            animation: slideDown 0.5s ease;
        }

        @keyframes slideDown {
            from { transform: translateY(-100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-icon {
            width: 48px;
            height: 48px;
            border-radius: 30% 70% 70% 30% / 30% 55% 45% 70%;
            background: linear-gradient(145deg, #4facfe, #25d49c);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.6rem;
            transform: rotate(-5deg);
            box-shadow: 0 10px 25px #00b4ff40;
            animation: pulse 3s infinite;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(59, 158, 255, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(59, 158, 255, 0); }
            100% { box-shadow: 0 0 0 0 rgba(59, 158, 255, 0); }
        }

        .title-tag .main {
            font-weight: 600;
            font-size: 1.3rem;
            letter-spacing: -0.3px;
            background: linear-gradient(135deg, #fff, #b8e1ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .title-tag .sub {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .btn-back {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-subtle);
            border-radius: 60px;
            padding: 0.5rem 1.5rem;
            color: white;
            text-decoration: none;
            transition: var(--transition-smooth);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-back:hover {
            background: rgba(59, 158, 255, 0.2);
            border-color: var(--accent-primary);
        }

        /* تنسيق العناوين */
        .section-header {
            margin-bottom: 2rem;
        }

        .section-header h2 {
            font-size: 1.8rem;
            font-weight: 600;
            background: linear-gradient(135deg, #fff, #b8e1ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
            margin-bottom: 0.3rem;
        }

        .section-header p {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        /* تنسيق الحقول */
        .input-glass {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 60px;
            padding: 0.75rem 1.5rem;
            color: white;
            width: 100%;
            transition: var(--transition-smooth);
        }

        .input-glass:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 3px rgba(59, 158, 255, 0.2);
            color: white;
            outline: none;
        }

        .input-glass::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        /* تنسيق الأزرار */
        .btn-primary {
            border-radius: 60px;
            background: linear-gradient(145deg, #1f6fbb, #135b9e);
            border: none;
            padding: 0.75rem 2rem;
            font-weight: 500;
            color: white;
            transition: var(--transition-smooth);
            position: relative;
            overflow: hidden;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-primary:hover {
            background: linear-gradient(145deg, #2780d1, #1b6bb0);
            transform: scale(1.02);
        }

        .btn-danger {
            border-radius: 60px;
            background: linear-gradient(145deg, #ff6b6b, #cc0000);
            border: none;
            padding: 0.75rem 2rem;
            font-weight: 500;
            color: white;
            transition: var(--transition-smooth);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-danger:hover {
            background: linear-gradient(145deg, #ff8080, #e60000);
            transform: scale(1.02);
        }

        .btn-secondary {
            border-radius: 60px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 0.75rem 2rem;
            font-weight: 500;
            color: white;
            transition: var(--transition-smooth);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.3);
        }

        /* تنسيق الأقسام */
        .profile-section {
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .profile-section:last-child {
            margin-bottom: 0;
        }

        /* تنسيق رسائل النجاح */
        .success-message {
            color: var(--success);
            font-size: 0.9rem;
            margin-top: 1rem;
            animation: fadeInOut 2s ease;
        }

        @keyframes fadeInOut {
            0% { opacity: 0; transform: translateY(5px); }
            20% { opacity: 1; transform: translateY(0); }
            80% { opacity: 1; transform: translateY(0); }
            100% { opacity: 0; transform: translateY(-5px); }
        }

        /* تنسيق الأخطاء */
        .error-message {
            color: var(--danger);
            font-size: 0.85rem;
            margin-top: 0.3rem;
        }

        /* تنسيق المودال */
        .modal-overlay {
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(8px);
        }

        .modal-content {
            background: rgba(10, 30, 45, 0.95);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-xl);
            color: white;
            max-width: 500px;
            margin: 0 auto;
        }

        .modal-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            padding: 1.5rem;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            padding: 1.5rem;
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }

        /* تنسيق الأيقونات داخل الحقول */
        .input-icon {
            position: relative;
        }

        .input-icon i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        .input-icon .input-glass {
            padding-left: 2.5rem;
        }

        /* الشبكة للحقول */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        @media (max-width: 640px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        /* تذييل */
        .footer-note {
            text-align: center;
            color: var(--text-muted);
            font-size: 0.8rem;
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <!-- شريط التنقل العلوي (مثل صفحة الطقس) -->
        <div class="navbar-glass">
            <div class="brand-logo">
                <div class="logo-icon">☁️</div>
                <div class="title-tag">
                    <div class="main">SkyCast<span style="font-weight:300;">Pro</span></div>
                    <div class="sub">الملف الشخصي</div>
                </div>
            </div>
            <a href="{{ route('weather.index') }}" class="btn-back">
                <i class="bi bi-arrow-right"></i>
                العودة للطقس
            </a>
        </div>

        <!-- معلومات الملف الشخصي -->
        <div class="glass-panel profile-section">
            <div class="section-header">
                <h2>معلومات الحساب</h2>
                <p>قم بتحديث معلوماتك الشخصية وعنوان البريد الإلكتروني</p>
            </div>

            <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                @csrf
            </form>

            <form method="post" action="{{ route('profile.update') }}" class="space-y-6">
                @csrf
                @method('patch')

                <div class="form-grid">
                    <div class="input-icon">
                        <i class="bi bi-person"></i>
                        <input id="name" name="name" type="text" class="input-glass"
                               placeholder="الاسم الكامل" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
                        @error('name')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="input-icon">
                        <i class="bi bi-envelope"></i>
                        <input id="email" name="email" type="email" class="input-glass"
                               placeholder="البريد الإلكتروني" value="{{ old('email', $user->email) }}" required autocomplete="username">
                        @error('email')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div class="mt-4">
                        <p class="text-sm text-muted">
                            {{ __('Your email address is unverified.') }}
                            <button form="send-verification" class="underline hover:text-white transition">
                                {{ __('Click here to re-send the verification email.') }}
                            </button>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <p class="success-message">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </p>
                        @endif
                    </div>
                @endif

                <div class="flex items-center gap-4">
                    <button type="submit" class="btn-primary">
                        <i class="bi bi-check-lg"></i>
                        حفظ التغييرات
                    </button>

                    @if (session('status') === 'profile-updated')
                        <span class="success-message" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 2000)">
                            <i class="bi bi-check-circle-fill"></i> تم الحفظ
                        </span>
                    @endif
                </div>
            </form>
        </div>

        <!-- تغيير كلمة المرور -->
        <div class="glass-panel profile-section">
            <div class="section-header">
                <h2>تغيير كلمة المرور</h2>
                <p>تأكد من استخدام كلمة مرور قوية للحفاظ على أمان حسابك</p>
            </div>

            <form method="post" action="{{ route('password.update') }}" class="space-y-6">
                @csrf
                @method('put')

                <div class="form-grid">
                    <div class="input-icon">
                        <i class="bi bi-lock"></i>
                        <input id="update_password_current_password" name="current_password" type="password" class="input-glass"
                               placeholder="كلمة المرور الحالية" autocomplete="current-password">
                        @error('current_password', 'updatePassword')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="input-icon">
                        <i class="bi bi-key"></i>
                        <input id="update_password_password" name="password" type="password" class="input-glass"
                               placeholder="كلمة المرور الجديدة" autocomplete="new-password">
                        @error('password', 'updatePassword')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="input-icon">
                        <i class="bi bi-key-fill"></i>
                        <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="input-glass"
                               placeholder="تأكيد كلمة المرور الجديدة" autocomplete="new-password">
                        @error('password_confirmation', 'updatePassword')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <button type="submit" class="btn-primary">
                        <i class="bi bi-check-lg"></i>
                        تحديث كلمة المرور
                    </button>

                    @if (session('status') === 'password-updated')
                        <span class="success-message" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 2000)">
                            <i class="bi bi-check-circle-fill"></i> تم الحفظ
                        </span>
                    @endif
                </div>
            </form>
        </div>

        <!-- حذف الحساب مع مودال -->
        <div class="glass-panel profile-section" x-data="{ showDeleteModal: false }">
            <div class="section-header">
                <h2 class="text-danger">حذف الحساب</h2>
                <p>بمجرد حذف حسابك، سيتم حذف جميع بياناته بشكل نهائي. قبل الحذف، يرجى تحميل أي معلومات تريد الاحتفاظ بها.</p>
            </div>

            <div class="flex justify-end">
                <button type="button" class="btn-danger" @click="showDeleteModal = true">
                    <i class="bi bi-trash"></i>
                    حذف الحساب
                </button>
            </div>

            <!-- مودال تأكيد الحذف -->
            <div x-show="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 modal-overlay" style="display: none;" @keydown.escape.window="showDeleteModal = false">
                <div class="modal-content w-full" @click.away="showDeleteModal = false">
                    <form method="post" action="{{ route('profile.destroy') }}">
                        @csrf
                        @method('delete')

                        <div class="modal-header">
                            <h3 class="text-xl font-semibold">تأكيد حذف الحساب</h3>
                        </div>
                        <div class="modal-body">
                            <p class="mb-4">هل أنت متأكد من رغبتك في حذف حسابك؟</p>
                            <p class="text-sm text-muted mb-4">بمجرد حذف حسابك، سيتم حذف جميع موارده وبياناته بشكل نهائي. يرجى إدخال كلمة المرور لتأكيد الحذف الدائم.</p>
                            <div class="input-icon">
                                <i class="bi bi-lock"></i>
                                <input id="password" name="password" type="password" class="input-glass"
                                       placeholder="كلمة المرور" autocomplete="current-password">
                            </div>
                            @error('password', 'userDeletion')
                                <div class="error-message mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn-secondary" @click="showDeleteModal = false">
                                إلغاء
                            </button>
                            <button type="submit" class="btn-danger">
                                نعم، احذف حسابي
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="footer-note">
            <span>⏱ آخر تحديث: {{ now()->format('H:i:s') }} — جميع الحقوق محفوظة © {{ date('Y') }} SkyCastPro</span>
        </div>
    </div>

    <!-- Bootstrap JS (اختياري) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
