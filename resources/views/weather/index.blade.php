<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5, user-scalable=yes" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="https://openweathermap.org/img/wn/02d.png">
    <title>نافذة الطقس — توقعات احترافية مع خرائط متحركة (جميع الطبقات المجانية)</title>

    <!-- خطوط حديثة + Bootstrap RTL + أيقونات -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,400..700;1,14..32,400..700&family=Cairo:wght@300..700&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-window@1.2.0/dist/leaflet-control-window.css" />

    <!-- Animate.css للحركات -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

    <!-- AOS للتمرير -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- المكتبات الأساسية -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-control-window@1.2.0/dist/leaflet-control-window.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <!-- مكتبة الرياح المتحركة (leaflet-velocity) -->
    <script src="https://cdn.jsdelivr.net/gh/danwild/leaflet-velocity@v2.0.0/dist/leaflet-velocity.min.js"></script>

    <!-- Leaflet Fullscreen -->
    <script src='https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/Leaflet.fullscreen.min.js'></script>
    <link href='https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/leaflet.fullscreen.css'
        rel='stylesheet' />

    <!-- Leaflet EasyButton -->
    <script src="https://cdn.jsdelivr.net/npm/leaflet-easybutton@2/src/easy-button.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet-easybutton@2/src/easy-button.css" />

    <!-- SweetAlert2 للتنبيهات -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* ========== المتغيرات الأساسية والتجاوب العالمي ========== */
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
            padding: 1rem;
            line-height: 1.5;
            backdrop-filter: blur(2px);
        }

        @media (min-width: 768px) {
            body {
                padding: 1.5rem 1.2rem;
            }
        }

        #loading-bar {
            position: fixed;
            top: 0;
            left: 0;
            width: 0%;
            height: 3px;
            background: linear-gradient(90deg, var(--accent-primary), var(--accent-secondary));
            z-index: 999999;
            transition: width 0.3s ease;
            box-shadow: 0 0 10px var(--accent-primary);
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

        .app-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        /* ========== Header متجاوب ========== */
        .navbar-glass {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 1rem;
            padding: 0.7rem 1rem;
            margin-bottom: 1.5rem;
            border-radius: 60px;
            background: rgba(5, 20, 30, 0.55);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.03);
            animation: slideDown 0.5s ease;
            position: relative;
            z-index: 10000;
        }

        @media (min-width: 768px) {
            .navbar-glass {
                padding: 0.7rem 1.5rem;
                margin-bottom: 2rem;
            }
        }

        @keyframes slideDown {
            from {
                transform: translateY(-100%);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            border-radius: 30% 70% 70% 30% / 30% 55% 45% 70%;
            background: linear-gradient(145deg, #4facfe, #25d49c);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.4rem;
            transform: rotate(-5deg);
            box-shadow: 0 10px 25px #00b4ff40;
            animation: pulse 3s infinite;
        }

        @media (min-width: 768px) {
            .logo-icon {
                width: 48px;
                height: 48px;
                font-size: 1.6rem;
            }
        }

        .title-tag .main {
            font-weight: 600;
            font-size: 1.1rem;
            letter-spacing: -0.3px;
            background: linear-gradient(135deg, #fff, #b8e1ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .title-tag .sub {
            font-size: 0.7rem;
            color: var(--text-muted);
        }

        @media (min-width: 768px) {
            .title-tag .main {
                font-size: 1.3rem;
            }

            .title-tag .sub {
                font-size: 0.75rem;
            }
        }

        /* search wrapper */
        .search-wrapper {
            flex: 1;
            min-width: 200px;
            position: relative;
            z-index: 10001;
        }

        @media (min-width: 640px) {
            .search-wrapper {
                min-width: 300px;
            }
        }

        .search-control {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 60px;
            padding: 0.5rem 1rem 0.5rem 2.8rem;
            color: white;
            transition: var(--transition-smooth);
            width: 100%;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23ffffff' class='bi bi-search' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: left 15px center;
            font-size: 0.9rem;
        }

        @media (min-width: 768px) {
            .search-control {
                padding: 0.7rem 1.5rem 0.7rem 3rem;
                background-position: left 20px center;
                font-size: 1rem;
            }
        }

        .btn--primary {
            border-radius: 60px;
            background: linear-gradient(145deg, #1f6fbb, #135b9e);
            border: none;
            padding: 0.5rem 1.2rem;
            font-weight: 500;
            color: white;
            transition: var(--transition-smooth);
            position: relative;
            overflow: hidden;
            font-size: 0.9rem;
            white-space: nowrap;
        }

        @media (min-width: 768px) {
            .btn--primary {
                padding: 0.6rem 1.8rem;
                font-size: 1rem;
            }
        }

        .btn--primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn--primary:hover::before {
            left: 100%;
        }

        .btn--primary:hover {
            background: linear-gradient(145deg, #2780d1, #1b6bb0);
            transform: scale(1.02);
        }

        .search-results-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 10002;
            background: rgba(10, 30, 45, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            margin-top: 5px;
            max-height: 300px;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            animation: fadeIn 0.2s ease;
        }

        .search-result-item {
            padding: 12px 15px;
            cursor: pointer;
            transition: all 0.2s ease;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .search-result-item:hover {
            background: rgba(59, 158, 255, 0.2);
            transform: translateX(-5px);
        }

        /* user menu */
        .user-menu {
            display: flex;
            align-items: center;
            position: relative;
            z-index: 10003;
        }

        .user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: linear-gradient(145deg, var(--accent-primary), var(--accent-secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            transition: var(--transition-smooth);
            border: 2px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            text-transform: uppercase;
        }

        @media (min-width: 768px) {
            .user-avatar {
                width: 42px;
                height: 42px;
                font-size: 1.2rem;
            }
        }

        .user-avatar:hover {
            transform: scale(1.1);
            border-color: white;
            box-shadow: 0 6px 15px var(--accent-primary);
        }

        .user-dropdown {
            position: absolute;
            top: 50px;
            left: 0;
            background: rgba(15, 35, 50, 0.98);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 20px;
            padding: 12px;
            min-width: 200px;
            z-index: 999999;
            display: none;
            animation: fadeIn 0.2s ease;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
        }

        @media (min-width: 768px) {
            .user-dropdown {
                min-width: 240px;
            }
        }

        .user-dropdown.show {
            display: block;
        }

        .user-dropdown-item {
            padding: 12px 15px;
            border-radius: 12px;
            transition: all 0.2s ease;
            cursor: pointer;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-dropdown-item:hover {
            background: rgba(59, 158, 255, 0.2);
            transform: translateX(-5px);
        }

        .user-dropdown-item i {
            width: 20px;
            color: var(--accent-primary);
            font-size: 1.1rem;
        }

        .user-dropdown-divider {
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
            margin: 8px 0;
        }

        /* ========== الشبكة الرئيسية متجاوبة ========== */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.2rem;
            align-items: stretch;
        }

        @media (min-width: 992px) {
            .dashboard-grid {
                grid-template-columns: 380px 1fr;
                gap: 1.5rem;
            }
        }

        .current-card,
        .analytics-card {
            min-height: auto;
            display: flex;
            flex-direction: column;
            padding: 1.2rem;
            border-radius: var(--radius-xl);
            background: rgba(8, 28, 41, 0.65);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-subtle);
        }

        @media (min-width: 768px) {
            .current-card {
                padding: 1.5rem;
            }

            .analytics-card {
                padding: 1.5rem;
            }
        }

        .temp-big {
            font-size: 3rem;
            font-weight: 600;
            line-height: 1;
            letter-spacing: -1px;
            background: linear-gradient(to bottom, #fff, #b8e1ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: glow 3s ease-in-out infinite;
        }

        @media (min-width: 768px) {
            .temp-big {
                font-size: 4.5rem;
                letter-spacing: -2px;
            }
        }

        @keyframes glow {

            0%,
            100% {
                text-shadow: 0 0 20px rgba(59, 158, 255, 0.5);
            }

            50% {
                text-shadow: 0 0 40px rgba(59, 158, 255, 0.8);
            }
        }

        .weather-meta-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
            gap: 0.6rem;
            margin: 1rem 0;
        }

        .meta-chip {
            background: rgba(0, 30, 50, 0.45);
            backdrop-filter: blur(3px);
            padding: 0.5rem 0.8rem;
            border-radius: 60px;
            font-size: 0.8rem;
            border: 1px solid rgba(255, 255, 255, 0.03);
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        @media (min-width: 768px) {
            .meta-chip {
                font-size: 0.9rem;
                padding: 0.5rem 1rem;
            }
        }

        .meta-chip:hover {
            background: rgba(0, 50, 80, 0.6);
            transform: translateY(-2px);
            border-color: var(--accent-primary);
        }

        /* التوقعات الأسبوعية */
        .week-strip {
            display: flex;
            gap: 0.8rem;
            margin-top: 1.5rem;
            overflow-x: auto;
            padding-bottom: 1rem;
            scrollbar-width: thin;
            scrollbar-color: var(--accent-primary) #1e3b4b;
            align-items: stretch;
        }

        .week-strip::-webkit-scrollbar {
            height: 5px;
        }

        .week-strip::-webkit-scrollbar-track {
            background: #152e3b;
            border-radius: 10px;
        }

        .week-strip::-webkit-scrollbar-thumb {
            background: var(--accent-primary);
            border-radius: 10px;
        }

        .day-card {
            min-width: 130px;
            background: rgba(16, 44, 60, 0.5);
            backdrop-filter: blur(4px);
            border: 1px solid rgba(255, 255, 255, 0.03);
            border-radius: 24px;
            padding: 1rem 0.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.3rem;
            transition: var(--transition-smooth);
            cursor: pointer;
            flex: 0 0 auto;
            box-shadow: 0 10px 25px -10px #00000080;
            position: relative;
            overflow: hidden;
        }

        @media (min-width: 768px) {
            .day-card {
                min-width: 160px;
                padding: 1.2rem 0.8rem;
            }
        }

        .day-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--accent-primary), transparent);
            transform: translateX(-100%);
            transition: transform 0.5s ease;
        }

        .day-card:hover::before {
            transform: translateX(100%);
        }

        .day-card:hover {
            transform: translateY(-8px);
            background: rgba(26, 62, 84, 0.7);
            border-color: rgba(90, 190, 255, 0.3);
            box-shadow: 0 22px 35px -12px #000000cc;
        }

        .rain-indicator {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 0.7rem;
            background: rgba(0, 100, 150, 0.5);
            padding: 0.2rem 0.5rem;
            border-radius: 20px;
            color: #7cc8ff;
            z-index: 2;
        }

        .day-name {
            font-weight: 600;
            font-size: 0.9rem;
            background: rgba(255, 255, 255, 0.02);
            padding: 0.2rem 0.8rem;
            border-radius: 60px;
        }

        @media (min-width: 768px) {
            .day-name {
                font-size: 1rem;
            }
        }

        .day-temp-range {
            font-size: 1.2rem;
            font-weight: 650;
            background: linear-gradient(to bottom, #fff, #cae6ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        @media (min-width: 768px) {
            .day-temp-range {
                font-size: 1.5rem;
            }
        }

        .day-stats {
            display: flex;
            gap: 0.8rem;
            justify-content: center;
            font-size: 0.8rem;
            color: var(--text-muted);
            width: 100%;
            margin: 0.3rem 0;
        }

        .humidity-indicator {
            width: 100%;
            height: 6px;
            background: rgba(0, 20, 30, 0.5);
            border-radius: 10px;
            overflow: hidden;
            margin: 0.3rem 0;
        }

        .humidity-fill {
            height: 100%;
            background: linear-gradient(90deg,
                    #4f9fff 0%,
                    #6cf0c1 50%,
                    #4f9fff 100%);
            background-size: 200% 100%;
            border-radius: 10px;
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }

        .detail-btn {
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(0, 0, 0, 0.25);
            border-radius: 40px;
            font-size: 0.75rem;
            padding: 0.35rem 1rem;
            margin-top: 0.2rem;
            transition: 0.2s;
            color: white;
            width: 100%;
        }

        .detail-btn:hover {
            background: var(--accent-primary);
            border-color: transparent;
        }

        .unit-toggle {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 100px;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .unit-btn {
            background: transparent;
            border: none;
            color: white;
            padding: 0.4rem 1rem;
            border-radius: 60px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .unit-btn.active {
            background: #2575c7;
        }

        .unit-btn:hover:not(.active) {
            background: rgba(255, 255, 255, 0.1);
        }

        /* المفضلة */
        .favorites-section {
            max-height: 300px;
            overflow-y: auto;
            padding-right: 5px;
        }

        .favorites-section::-webkit-scrollbar {
            width: 5px;
        }

        .favorites-section::-webkit-scrollbar-track {
            background: #152e3b;
            border-radius: 10px;
        }

        .favorites-section::-webkit-scrollbar-thumb {
            background: var(--accent-primary);
            border-radius: 10px;
        }

        .favorite-item {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 12px;
            padding: 10px;
            margin-bottom: 8px;
            transition: all 0.2s ease;
            border: 1px solid transparent;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .favorite-item:hover {
            background: rgba(59, 158, 255, 0.1);
            border-color: var(--accent-primary);
            transform: translateX(-5px);
        }

        @media (max-width: 480px) {
            .favorite-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .favorite-actions {
                align-self: flex-end;
            }
        }

        .favorite-item .city-name {
            font-weight: 600;
            color: white;
        }

        .favorite-item .city-temp {
            color: var(--accent-primary);
            font-size: 0.9rem;
        }

        .favorite-actions {
            display: flex;
            gap: 5px;
        }

        .favorite-actions button {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .favorite-actions .view-btn {
            background: rgba(59, 158, 255, 0.2);
            color: var(--accent-primary);
        }

        .favorite-actions .view-btn:hover {
            background: var(--accent-primary);
            color: white;
        }

        .favorite-actions .delete-btn {
            background: rgba(255, 107, 107, 0.2);
            color: var(--danger);
        }

        .favorite-actions .delete-btn:hover {
            background: var(--danger);
            color: white;
        }

        /* التنبيهات */
        .alert-custom {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 999998;
            min-width: 280px;
            max-width: 90%;
            padding: 12px 20px;
            border-radius: 50px;
            background: rgba(10, 30, 45, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.3s ease;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .alert-custom.success {
            border-right: 5px solid var(--success);
        }

        .alert-custom.error {
            border-right: 5px solid var(--danger);
        }

        .alert-custom.warning {
            border-right: 5px solid var(--warning);
        }

        /* الخريطة */
        .weather-map-container {
            margin-top: 1.5rem;
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 3;
        }

        .leaflet-container {
            background: #0a1a24;
            border-radius: 20px;
            font-family: 'Cairo', sans-serif;
            height: 400px;
            width: 100%;
        }

        @media (min-width: 768px) {
            .leaflet-container {
                height: 600px;
            }
        }

        .leaflet-popup-content-wrapper {
            background: rgba(10, 30, 45, 0.95);
            color: white;
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            font-family: 'Cairo', sans-serif;
        }

        .leaflet-popup-tip {
            background: rgba(10, 30, 45, 0.95);
        }

        .info-panel {
            background: rgba(10, 30, 45, 0.9);
            color: white;
            padding: 8px 15px;
            border-radius: 30px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 13px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .weather-layer-tip {
            position: absolute;
            bottom: 20px;
            right: 20px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 8px 15px;
            border-radius: 30px;
            font-size: 12px;
            z-index: 10;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            animation: fadeInOut 2s ease;
        }

        @keyframes fadeInOut {
            0% {
                opacity: 0;
                transform: translateY(10px);
            }

            20% {
                opacity: 1;
                transform: translateY(0);
            }

            80% {
                opacity: 1;
                transform: translateY(0);
            }

            100% {
                opacity: 0;
                transform: translateY(-10px);
            }
        }

        .layers-control-panel {
            background: rgba(10, 30, 45, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 15px;
            color: white;
            max-height: 70vh;
            overflow-y: auto;
            animation: slideUp 0.3s ease;
            position: relative;
            z-index: 1000;
            font-size: 0.9rem;
        }

        @media (min-width: 768px) {
            .layers-control-panel {
                max-height: 400px;
                font-size: 1rem;
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .layer-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 10px;
            border-radius: 10px;
            transition: all 0.2s ease;
            cursor: pointer;
            flex-wrap: wrap;
            gap: 5px;
        }

        .layer-item:hover {
            background: rgba(59, 158, 255, 0.2);
        }

        .layer-item.active {
            background: rgba(59, 158, 255, 0.3);
            border-right: 3px solid var(--accent-primary);
        }

        @media (max-width: 480px) {
            .layer-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .layer-item>div:last-child {
                width: 100%;
                justify-content: space-between;
            }
        }

        .layer-opacity-slider {
            width: 80px;
            height: 5px;
            -webkit-appearance: none;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 5px;
            outline: none;
        }

        @media (max-width: 480px) {
            .layer-opacity-slider {
                width: 60px;
            }
        }

        .layer-opacity-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            background: var(--accent-primary);
            cursor: pointer;
        }

        .footer-note {
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            padding-top: 1rem;
        }

        /* تحسينات عامة */
        h5,
        h6 {
            font-size: 1rem;
        }

        @media (min-width: 768px) {
            h5 {
                font-size: 1.25rem;
            }

            h6 {
                font-size: 1rem;
            }
        }

        .badge {
            font-size: 0.75rem;
            padding: 0.4rem 0.8rem;
        }

        .btn-group .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        @media (min-width: 768px) {
            .btn-group .btn-sm {
                padding: 0.25rem 0.8rem;
                font-size: 0.875rem;
            }
        }
    </style>
</head>

<body>
    <div id="loading-bar"></div>

    <div class="app-container">

        <!-- header محسن مع صورة المستخدم -->
        <header class="navbar-glass">
            <div class="brand-logo">
                <div class="logo-icon animate__animated animate__pulse animate__infinite">☁️</div>
                <div class="title-tag">
                    <div class="main">SkyCast<span style="font-weight:300; margin-right:4px;">Pro</span></div>
                    <div class="sub">توقعات دقيقة · خرائط متحركة</div>
                </div>
            </div>

            <form method="GET" action="{{ route('weather.index') }}" class="search-wrapper d-flex gap-2"
                id="searchForm">
                <div class="flex-grow-1 position-relative">
                    <input type="text" name="city" class="form-control search-control" id="citySearch"
                        placeholder="ابحث عن مدينة... لندن, دمشق, نيويورك"
                        value="{{ request('city', $input ?? 'London') }}">
                    <div id="searchResults" class="search-results-dropdown" style="display: none;"></div>
                </div>
                <button class="btn btn--primary" type="submit" id="searchBtn">
                    <span class="spinner-border spinner-border-sm d-none" role="status" id="searchSpinner"></span>
                    <span id="searchText"><i class="bi bi-search"></i> بحث</span>
                </button>
            </form>

            <!-- قائمة المستخدم المحسنة -->
            <div class="user-menu">
                <div class="position-relative">
                    <div class="user-avatar" onclick="toggleUserMenu(event)">
                        {{ Auth::check() ? substr(Auth::user()->name, 0, 1) : 'ز' }}
                    </div>
                    <div id="userDropdown" class="user-dropdown">
                        @auth
                            <div class="user-dropdown-item">
                                <i class="bi bi-person-circle"></i>
                                <div>
                                    <div style="font-weight: 600;">{{ Auth::user()->name }}</div>
                                    <small style="color: var(--text-muted);">{{ Auth::user()->email }}</small>
                                </div>
                            </div>
                            <div class="user-dropdown-divider"></div>
                            <a href="{{ route('profile.edit') }}" class="user-dropdown-item">
                                <i class="bi bi-gear"></i>
                                <span>الملف الشخصي</span>
                            </a>
                            <div class="user-dropdown-divider"></div>
                            <form method="POST" action="{{ route('logout') }}" id="logout-form">
                                @csrf
                                <a href="#" class="user-dropdown-item"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="bi bi-box-arrow-right"></i>
                                    <span>تسجيل خروج</span>
                                </a>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="user-dropdown-item">
                                <i class="bi bi-box-arrow-in-right"></i>
                                <span>تسجيل دخول</span>
                            </a>
                            <a href="{{ route('register') }}" class="user-dropdown-item">
                                <i class="bi bi-person-plus"></i>
                                <span>إنشاء حساب</span>
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </header>

        <!-- التنبيهات -->
        @if (session('success'))
            <div class="alert-custom success animate__animated animate__fadeInDown">
                <i class="bi bi-check-circle-fill" style="color: var(--success);"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="alert-custom error animate__animated animate__fadeInDown">
                <i class="bi bi-exclamation-triangle-fill" style="color: var(--danger);"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif
        @if (isset($weather) || isset($dailySummaries))
            <!-- الشبكة الرئيسية -->
            <div class="dashboard-grid">
                <!-- العمود الأيسر -->
                <section class="current-card" data-aos="fade-left">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                        <div>
                            <h2 style="font-weight: 500; font-size:1.4rem; margin:0;">
                                <i class="bi bi-geo-alt-fill" style="color: var(--accent-primary);"></i>
                                {{ $displayName ?? ($weather['name'] ?? ($input ?? 'اللاذقية')) }}
                            </h2>
                            <p class="text-muted small"><i class="bi bi-clock"></i> آخر تحديث:
                                {{ now()->format('H:i') }}</p>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            @auth
                                @php
                                    $isFavorite = false;
                                    $currentCityName = $displayName ?? ($weather['name'] ?? $input);
                                    foreach ($favorites as $fav) {
                                        if ($fav->name == $currentCityName) {
                                            $isFavorite = true;
                                            break;
                                        }
                                    }
                                @endphp
                                <button class="btn btn-outline-warning btn-sm rounded-pill" id="favoriteBtn"
                                    onclick="toggleFavorite()">
                                    <i class="bi bi-star{{ $isFavorite ? '-fill' : '' }}"></i>
                                    <span id="favoriteText">{{ $isFavorite ? 'في المفضلة' : 'أضف للمفضلة' }}</span>
                                </button>
                            @endauth

                            <span class="badge bg-light text-dark px-3 py-2 rounded-pill">
                                <i class="bi bi-{{ $weather['weather'][0]['icon'] ?? 'sun' }} ms-1"></i>
                                {{ $weather['weather'][0]['description'] ?? ($dailySummaries[0]['description'] ?? 'سماء صافية') }}
                            </span>
                        </div>
                    </div>

                    <div class="d-flex align-items-center mt-2">
                        <span class="temp-big" id="currentTempDisplay">
                            {{ round($weather['main']['temp'] ?? ($dailySummaries[0]['max'] ?? 26)) }}°
                        </span>
                        <img src="https://openweathermap.org/img/wn/{{ $weather['weather'][0]['icon'] ?? ($dailySummaries[0]['icon'] ?? '01d') }}@4x.png"
                            width="70" height="70" alt="أيقونة الطقس"
                            class="ms-2 weather-icon-animated animate__animated animate__pulse animate__infinite"
                            onerror="this.src='https://openweathermap.org/img/wn/01d@4x.png'">
                    </div>

                    <div class="weather-meta-grid">
                        <div class="meta-chip"><i class="bi bi-droplet" style="color: var(--accent-primary);"></i>
                            رطوبة {{ $weather['main']['humidity'] ?? ($dailySummaries[0]['humidity'] ?? 68) }}%</div>
                        <div class="meta-chip"><i class="bi bi-thermometer-half" style="color: var(--warning);"></i>
                            إحساس
                            {{ round($weather['main']['feels_like'] ?? ($dailySummaries[0]['feels_like'] ?? 24)) }}°
                        </div>
                        <div class="meta-chip"><i class="bi bi-wind" style="color: var(--accent-secondary);"></i>
                            رياح {{ $weather['wind']['speed'] ?? ($windSpeed ?? 4.2) }} م/ث</div>
                        <div class="meta-chip"><i class="bi bi-cloud" style="color: var(--text-muted);"></i> غيوم
                            {{ $weather['clouds']['all'] ?? ($dailySummaries[0]['clouds'] ?? 50) }}%</div>
                        <div class="meta-chip"><i class="bi bi-sunrise" style="color: var(--warning);"></i>
                            {{ isset($weather['sys']['sunrise']) ? \Carbon\Carbon::createFromTimestamp($weather['sys']['sunrise'])->format('H:i') : '06:47' }}
                        </div>
                        <div class="meta-chip"><i class="bi bi-sunset" style="color: var(--warning);"></i>
                            {{ isset($weather['sys']['sunset']) ? \Carbon\Carbon::createFromTimestamp($weather['sys']['sunset'])->format('H:i') : '17:32' }}
                        </div>
                        <div class="meta-chip bg-rain" style="border-color: #3b9eff;">
                            <i class="bi bi-cloud-rain-heavy-fill" style="color: #7cc8ff;"></i>
                            <span style="font-weight: 600;">فرصة أمطار:</span>
                            <span
                                style="font-weight: 700; color: #9cd4ff; font-size: 1.1rem;">{{ $dailySummaries[0]['rain_probability'] ?? ($currentRainProbability ?? 35) }}%</span>
                        </div>
                    </div>

                    <h6 class="mt-3 mb-2 d-flex align-items-center">
                        <i class="bi bi-calendar3 me-2" style="color: var(--accent-primary);"></i> توقعات 7 أيام
                    </h6>
                    <div class="week-strip" id="forecastWeekStrip">
                        @forelse($dailySummaries ?? [] as $index => $day)
                            <div class="day-card" role="listitem" tabindex="0"
                                onclick="showDayDetails({{ $index }})"
                                data-day='@json($day)'>
                                @if (($day['rain_probability'] ?? 0) > 30)
                                    <div class="rain-indicator"><i class="bi bi-cloud-rain"></i>
                                        {{ $day['rain_probability'] }}%</div>
                                @endif
                                <span
                                    class="day-name">{{ \Carbon\Carbon::parse($day['date'])->translatedFormat('D') }}</span>
                                <img src="https://openweathermap.org/img/wn/{{ $day['icon'] }}@2x.png"
                                    width="48" height="48" alt="أيقونة"
                                    onerror="this.src='https://openweathermap.org/img/wn/02d@2x.png'">
                                <span class="day-temp-range">{{ $day['max'] }}° / {{ $day['min'] }}°</span>
                                <div class="humidity-indicator">
                                    <div class="humidity-fill" style="width: {{ $day['humidity'] }}%"></div>
                                </div>
                            </div>
                        @empty
                            @php $sampleDays = ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت']; @endphp
                            @foreach ($sampleDays as $i => $dayName)
                                <div class="day-card">
                                    <span class="day-name">{{ $dayName }}</span>
                                    <img src="https://openweathermap.org/img/wn/02d@2x.png" width="48"
                                        height="48">
                                    <span class="day-temp-range">--° / --°</span>
                                    <div class="humidity-indicator">
                                        <div class="humidity-fill" style="width: 50%"></div>
                                    </div>
                                </div>
                            @endforeach
                        @endforelse
                    </div>

                    <!-- قسم المفضلة المحسن مع قاعدة البيانات -->
                    <h6 class="mt-4 mb-2 d-flex align-items-center">
                        <i class="bi bi-star-fill text-warning me-2"></i>
                        مدنك المفضلة
                        @auth
                            <span class="badge bg-primary ms-2">{{ $favorites->count() }}</span>
                        @endauth
                    </h6>

                    <div class="favorites-section">
                        @auth
                            @if ($favorites->count() > 0)
                                @foreach ($favorites as $fav)
                                    <div class="favorite-item">
                                        <div>
                                            <div class="city-name">
                                                <i class="bi bi-star-fill text-warning ms-2"></i>
                                                {{ $fav->name }}
                                            </div>
                                            @if ($fav->current_weather)
                                                <small class="city-temp">
                                                    <i class="bi bi-thermometer-half"></i>
                                                    {{ round($fav->current_weather['main']['temp'] ?? 0) }}°C
                                                </small>
                                            @endif
                                        </div>
                                        <div class="favorite-actions">
                                            <button class="btn btn-sm btn-outline-info"
                                                onclick="window.location.href='{{ route('weather.index', ['city' => $fav->lat . ',' . $fav->lon]) }}'"
                                                title="عرض">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger"
                                                onclick="removeFavorite({{ $fav->id }})" title="حذف">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-muted text-center p-3">
                                    <i class="bi bi-star fs-1 d-block mb-2"></i>
                                    <p>لا توجد مدن مفضلة بعد</p>
                                    <small>ابحث عن مدينة واضغط على النجمة لإضافتها</small>
                                </div>
                            @endif
                        @else
                            <div class="text-center p-3" style="background: rgba(255,255,255,0.03); border-radius: 10px;">
                                <i class="bi bi-person-circle fs-2 d-block mb-2 text-primary"></i>
                                <p class="mb-2">سجل دخول لحفظ المدن المفضلة</p>
                                <div class="d-flex gap-2 justify-content-center">
                                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm">تسجيل دخول</a>
                                    <a href="{{ route('register') }}" class="btn btn-outline-light btn-sm">إنشاء حساب</a>
                                </div>
                            </div>
                        @endauth
                    </div>

                    <!-- قسم سجل البحث (يبقى في Session) -->
                    <h6 class="mt-3 mb-2 d-flex align-items-center">
                        <i class="bi bi-clock-history me-2" style="color: var(--accent-secondary);"></i> آخر عمليات
                        البحث
                    </h6>
                    <div id="searchHistoryList">
                        @php $history = Session::get('search_history', []); @endphp
                        @if (count($history) > 0)
                            @foreach ($history as $item)
                                <div class="d-flex justify-content-between align-items-center p-2 mb-1"
                                    style="background: rgba(255,255,255,0.03); border-radius: 8px;">
                                    <span><i class="bi bi-search ms-2 text-muted"></i> {{ $item }}</span>
                                    <button class="btn btn-sm btn-outline-info"
                                        onclick="window.location.href='{{ route('weather.index', ['city' => $item]) }}'">
                                        <i class="bi bi-arrow-left"></i>
                                    </button>
                                </div>
                            @endforeach
                        @else
                            <div class="text-muted text-center p-2">لا يوجد سجل بحث</div>
                        @endif
                    </div>
                </section>

                <!-- العمود الأيمن (الرسم البياني) -->
                <aside class="analytics-card" data-aos="fade-right">
                    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                        <h5 class="fw-semibold"><i class="bi bi-graph-up-arrow ms-2"
                                style="color: var(--accent-primary);"></i> حرارة / رطوبة (24 ساعة)</h5>
                        <div class="d-flex gap-2">
                            <div class="unit-toggle">
                                <button id="unitC" class="unit-btn active" onclick="setUnit('C')">°C</button>
                                <button id="unitF" class="unit-btn" onclick="setUnit('F')">°F</button>
                            </div>
                            <button class="btn btn-sm btn-outline-light rounded-pill" onclick="refreshData()"
                                title="تحديث">
                                <i class="bi bi-arrow-repeat"></i>
                            </button>
                        </div>
                    </div>
                    <div class="chart-container" style="min-height: 200px;">
                        <canvas id="hourlyChart"></canvas>
                    </div>
                </aside>
            </div>

            <!-- الخريطة التفاعلية مع جميع الطبقات المجانية -->
            @if (isset($coordinates))
                <section class="mt-5" data-aos="fade-up">
                    <div class="d-flex align-items-center mb-3 flex-wrap gap-2">
                        <i class="bi bi-map fs-4 me-2" style="color: var(--accent-primary);"></i>
                        <h5 class="fw-semibold mb-0">خرائط الطقس المتكاملة - {{ $displayName ?? ($input ?? '') }}</h5>
                        <span class="badge bg-rain me-3 px-3 py-2 rounded-pill">
                            <i class="bi bi-geo-alt"></i> {{ number_format($coordinates['lat'], 4) }}°,
                            {{ number_format($coordinates['lon'], 4) }}°
                        </span>
                        <div class="me-auto"></div>
                        <div class="d-flex gap-2 flex-wrap">
                            <span class="badge" style="background: linear-gradient(135deg, #00aaff, #0066aa);"
                                id="windStatus"><i class="bi bi-wind"></i> رياح</span>
                            <span class="badge" style="background: linear-gradient(135deg, #33ccff, #0099cc);"
                                id="rainStatus"><i class="bi bi-cloud-rain"></i> رادار</span>
                            <span class="badge" style="background: linear-gradient(135deg, #aaaaaa, #666666);"
                                id="cloudStatus"><i class="bi bi-cloud"></i> غيوم</span>
                            <span class="badge" style="background: linear-gradient(135deg, #ff6b6b, #cc0000);"
                                id="tempStatus"><i class="bi bi-thermometer-half"></i> حرارة</span>
                            <span class="badge" style="background: linear-gradient(135deg, #b8a0ff, #6b4f9e);"
                                id="pressureStatus"><i class="bi bi-speedometer2"></i> ضغط</span>
                            <span class="badge" style="background: linear-gradient(135deg, #ffaa00, #ff5500);"
                                id="lightningStatus"><i class="bi bi-lightning"></i> برق</span>
                            <span class="badge" style="background: linear-gradient(135deg, #800080, #4b0082);"
                                id="stormStatus"><i class="bi bi-cloud-lightning-rain"></i> عواصف</span>
                            <span class="badge" style="background: linear-gradient(135deg, #2c3e50, #1a1a2e);"
                                id="darkCloudsStatus"><i class="bi bi-clouds"></i> غيوم داكنة</span>
                        </div>
                    </div>

                    <div class="weather-map-container position-relative">
                        <div id="weatherMap" style="height: 400px; width: 100%;"></div>

                        <!-- مؤشر شدة العواصف -->
                        <div id="stormSeverity" class="position-absolute top-0 start-0 m-3 p-2 rounded-pill"
                            style="background: rgba(0,0,0,0.7); backdrop-filter: blur(5px); border: 1px solid rgba(255,255,255,0.2); display: none;">
                            <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                            <span id="severityText">شدة العواصف: منخفضة</span>
                        </div>

                        <!-- زر فتح لوحة التحكم بالطبقات -->
                        <button id="toggleLayersPanel"
                            class="btn btn-primary position-absolute top-0 end-0 m-3 rounded-pill"
                            style="z-index: 1000; background: rgba(10, 30, 45, 0.9); border: 1px solid rgba(255, 255, 255, 0.2);">
                            <i class="bi bi-layers"></i> الطبقات
                        </button>
                    </div>

                    <!-- لوحة التحكم بالطبقات (مخفية افتراضياً) -->
                    <div id="layersPanel" class="layers-control-panel mt-3" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="fw-bold"><i class="bi bi-layers-fill ms-2"></i>الطبقات المتاحة</h5>
                            <button class="btn-close btn-close-white"
                                onclick="document.getElementById('layersPanel').style.display='none'"></button>
                        </div>

                        <!-- طبقات الأساس -->
                        <h6 class="mt-3"><i class="bi bi-map ms-2"></i>الخريطة الأساسية</h6>
                        <div class="layer-item" onclick="switchBaseLayer('street')">
                            <span><i class="bi bi-signpost-2 ms-2"></i>خريطة الشارع</span>
                            <span class="badge bg-primary" id="baseStreet">✓</span>
                        </div>
                        <div class="layer-item" onclick="switchBaseLayer('satellite')">
                            <span><i class="bi bi-satellite ms-2"></i>قمر صناعي</span>
                            <span class="badge bg-secondary" id="baseSatellite"></span>
                        </div>
                        <div class="layer-item" onclick="switchBaseLayer('terrain')">
                            <span><i class="bi bi-mountain ms-2"></i>تضاريس</span>
                            <span class="badge bg-secondary" id="baseTerrain"></span>
                        </div>
                        <div class="layer-item" onclick="switchBaseLayer('dark')">
                            <span><i class="bi bi-moon-stars ms-2"></i>ليلي (داكن)</span>
                            <span class="badge bg-secondary" id="baseDark"></span>
                        </div>

                        <!-- طبقات الطقس -->
                        <h6 class="mt-4"><i class="bi bi-cloud-sun ms-2"></i>الطقس</h6>

                        <!-- رادار RainViewer (أرضي) -->
                        <div class="layer-item" id="layer-rainviewer">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-cloud-rain ms-2"></i>
                                <span>رادار الأمطار (RainViewer) - أرضي</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <input type="range" class="layer-opacity-slider" id="rainviewer-opacity"
                                    min="0" max="1" step="0.1" value="0.7">
                                <div class="form-check form-switch d-inline-block m-0">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        id="rainviewer-toggle" checked>
                                </div>
                            </div>
                        </div>

                        <!-- طبقة المطر العالمية (قمر صناعي) -->
                        <div class="layer-item" id="layer-globalrain">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-globe ms-2"></i>
                                <span>الهطول العالمي (قمر صناعي) - يغطي العالم كامل</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <input type="range" class="layer-opacity-slider" id="globalrain-opacity"
                                    min="0" max="1" step="0.1" value="0.6">
                                <div class="form-check form-switch d-inline-block m-0">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        id="globalrain-toggle">
                                </div>
                            </div>
                        </div>

                        <!-- طبقة البرق (Blitzortung) - لحظي عبر WebSocket -->
                        <div class="layer-item" id="layer-lightning">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-lightning ms-2" style="color: #ffaa00;"></i>
                                <span>البرق (Blitzortung) - لحظي عبر WebSocket</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <input type="range" class="layer-opacity-slider" id="lightning-opacity"
                                    min="0" max="1" step="0.1" value="1">
                                <div class="form-check form-switch d-inline-block m-0">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        id="lightning-toggle">
                                </div>
                            </div>
                        </div>

                        <!-- طبقة العواصف الرعدية (OpenWeather) -->
                        <div class="layer-item" id="layer-storms">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-cloud-lightning-rain ms-2" style="color: #800080;"></i>
                                <span>العواصف الرعدية (خريطة حرارية)</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <input type="range" class="layer-opacity-slider" id="storms-opacity"
                                    min="0" max="1" step="0.1" value="0.7">
                                <div class="form-check form-switch d-inline-block m-0">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        id="storms-toggle">
                                </div>
                            </div>
                        </div>

                        <!-- طبقة الغيوم الداكنة (كثيفة) -->
                        <div class="layer-item" id="layer-darkclouds">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-clouds ms-2" style="color: #2c3e50;"></i>
                                <span>الغيوم الداكنة (كثافة عالية)</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <input type="range" class="layer-opacity-slider" id="darkclouds-opacity"
                                    min="0" max="1" step="0.1" value="0.6">
                                <div class="form-check form-switch d-inline-block m-0">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        id="darkclouds-toggle">
                                </div>
                            </div>
                        </div>

                        <!-- طبقة الأعاصير المدارية (Cyclones) -->
                        <div class="layer-item" id="layer-cyclones">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-hurricane ms-2" style="color: #ff6b6b;"></i>
                                <span>الأعاصير المدارية (Zoom Earth)</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <input type="range" class="layer-opacity-slider" id="cyclones-opacity"
                                    min="0" max="1" step="0.1" value="0.8">
                                <div class="form-check form-switch d-inline-block m-0">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        id="cyclones-toggle">
                                </div>
                            </div>
                        </div>

                        <!-- طبقة الرياح (حقيقية من OpenWeather) -->
                        <div class="layer-item" id="layer-wind">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-wind ms-2"></i>
                                <span>رياح (OpenWeather) - خريطة حرارية</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <input type="range" class="layer-opacity-slider" id="wind-opacity" min="0"
                                    max="1" step="0.1" value="0.9">
                                <div class="form-check form-switch d-inline-block m-0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="wind-toggle">
                                </div>
                            </div>
                        </div>

                        <!-- طبقة الغيوم العادية OpenWeather -->
                        <div class="layer-item" id="layer-clouds">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-cloud ms-2"></i>
                                <span>الغيوم (OpenWeather)</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <input type="range" class="layer-opacity-slider" id="clouds-opacity"
                                    min="0" max="1" step="0.1" value="0.6">
                                <div class="form-check form-switch d-inline-block m-0">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        id="clouds-toggle">
                                </div>
                            </div>
                        </div>

                        <!-- طبقة الحرارة OpenWeather -->
                        <div class="layer-item" id="layer-temp">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-thermometer-half ms-2"></i>
                                <span>الحرارة (OpenWeather)</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <input type="range" class="layer-opacity-slider" id="temp-opacity" min="0"
                                    max="1" step="0.1" value="0.5">
                                <div class="form-check form-switch d-inline-block m-0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="temp-toggle">
                                </div>
                            </div>
                        </div>

                        <!-- طبقة الضغط الجوي OpenWeather -->
                        <div class="layer-item" id="layer-pressure">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-speedometer2 ms-2"></i>
                                <span>الضغط الجوي (OpenWeather)</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <input type="range" class="layer-opacity-slider" id="pressure-opacity"
                                    min="0" max="1" step="0.1" value="0.5">
                                <div class="form-check form-switch d-inline-block m-0">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        id="pressure-toggle">
                                </div>
                            </div>
                        </div>

                        <!-- طبقة الثلوج NOAA -->
                        <div class="layer-item" id="layer-snow">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-snow2 ms-2"></i>
                                <span>الثلوج (NOAA)</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <input type="range" class="layer-opacity-slider" id="snow-opacity" min="0"
                                    max="1" step="0.1" value="0.7">
                                <div class="form-check form-switch d-inline-block m-0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="snow-toggle">
                                </div>
                            </div>
                        </div>

                        <!-- طبقة الغطاء النباتي MODIS -->
                        <div class="layer-item" id="layer-vegetation">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-tree ms-2"></i>
                                <span>الغطاء النباتي (MODIS)</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <input type="range" class="layer-opacity-slider" id="veg-opacity" min="0"
                                    max="1" step="0.1" value="0.5">
                                <div class="form-check form-switch d-inline-block m-0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="veg-toggle">
                                </div>
                            </div>
                        </div>

                        <!-- طبقة حرائق الغابات FIRMS -->
                        <div class="layer-item" id="layer-fires">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-fire ms-2"></i>
                                <span>حرائق الغابات (NASA FIRMS)</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <input type="range" class="layer-opacity-slider" id="fires-opacity" min="0"
                                    max="1" step="0.1" value="0.8">
                                <div class="form-check form-switch d-inline-block m-0">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        id="fires-toggle">
                                </div>
                            </div>
                        </div>

                        <!-- طبقة إضافية: الغيوم المرئية من EUMETSAT -->
                        <div class="layer-item" id="layer-eumetsat">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-cloud-sun ms-2"></i>
                                <span>الغيوم المرئية (EUMETSAT)</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <input type="range" class="layer-opacity-slider" id="eumetsat-opacity"
                                    min="0" max="1" step="0.1" value="0.5">
                                <div class="form-check form-switch d-inline-block m-0">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        id="eumetsat-toggle">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- شريط التحكم برادار RainViewer -->
                    <div class="d-flex gap-3 mt-3 justify-content-center flex-wrap align-items-center">
                        <div class="btn-group" role="group" aria-label="التحكم بالرادار">
                            <button class="btn btn-sm btn-outline-info"
                                onclick="if(window.rainViewerControl) rainViewerControl.showFrame(rainViewerControl.currentLayerIndex - 1)">
                                <i class="bi bi-skip-backward"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-info"
                                onclick="if(window.rainViewerControl) rainViewerControl.startAnimation()">
                                <i class="bi bi-play-fill"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-info"
                                onclick="if(window.rainViewerControl) rainViewerControl.stopAnimation()">
                                <i class="bi bi-pause-fill"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-info"
                                onclick="if(window.rainViewerControl) rainViewerControl.showFrame(rainViewerControl.currentLayerIndex + 1)">
                                <i class="bi bi-skip-forward"></i>
                            </button>
                        </div>
                        <span id="rainFrameTime" class="badge bg-dark px-3 py-2">🕒 --:--</span>
                    </div>
                </section>
            @endif
        @else
            <!-- صفحة عدم وجود بيانات -->
            <div class="text-center py-5 animate__animated animate__fadeIn">
                <i class="bi bi-cloud-sun" style="font-size: 5rem; color: var(--accent-primary);"></i>
                <h3 class="mt-3">مرحباً {{ Auth::check() ? Auth::user()->name : '' }} 👋</h3>
                <p class="text-muted">أدخل اسم المدينة لعرض بيانات الطقس</p>
                <div class="mt-4">
                    <form method="GET" action="{{ route('weather.index') }}"
                        class="d-flex justify-content-center gap-2">
                        <input type="text" name="city" class="form-control w-75 w-sm-50"
                            placeholder="مثال: London, Paris, دمشق">
                        <button type="submit" class="btn btn-primary">بحث</button>
                    </form>
                </div>
            </div>
        @endif

        <div class="text-center text-muted small mt-4 footer-note">
            <span>⏱ آخر تحديث: {{ now()->format('H:i:s') }} — بيانات الطقس: OpenWeather, RainViewer, NOAA, NASA FIRMS,
                MODIS, Blitzortung (عبر WebSocket), Zoom Earth | جميع الخدمات مجانية</span>
        </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // تهيئة AOS للتمرير
        AOS.init({
            duration: 800,
            once: true
        });

        // دالة قائمة المستخدم (معدلة لإغلاق القائمة بشكل صحيح)
        function toggleUserMenu(event) {
            event.stopPropagation();
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('show');
        }

        // إغلاق القائمة عند النقر خارجها
        window.onclick = function(event) {
            const dropdown = document.getElementById('userDropdown');
            const avatar = document.querySelector('.user-avatar');
            if (!event.target.closest('.user-dropdown') && !event.target.closest('.user-avatar')) {
                if (dropdown && dropdown.classList.contains('show')) {
                    dropdown.classList.remove('show');
                }
            }
        };

        // دوال شريط التحميل
        function showLoadingBar() {
            document.getElementById('loading-bar').style.width = '60%';
        }

        function hideLoadingBar() {
            document.getElementById('loading-bar').style.width = '100%';
            setTimeout(() => {
                document.getElementById('loading-bar').style.width = '0%';
            }, 300);
        }

        // بيانات الرسم البياني
        let hourlyLabels = [];
        @if (isset($hourlyLabels) && is_array($hourlyLabels))
            hourlyLabels = @json($hourlyLabels);
        @else
            for (let i = 0; i < 24; i++) {
                hourlyLabels.push(i + ':00');
            }
        @endif

        let hourlyTemps = [];
        @if (isset($hourlyTemps) && is_array($hourlyTemps))
            hourlyTemps = @json($hourlyTemps);
        @else
            hourlyTemps = [18, 19, 21, 23, 25, 26, 27, 28, 28, 27, 26, 24, 22, 20, 19, 18, 17, 18, 19, 21, 23, 24, 24, 23];
        @endif

        let chartInstance = null;
        let currentUnit = 'C';

        function renderChart(unit = 'C') {
            const ctx = document.getElementById('hourlyChart')?.getContext('2d');
            if (!ctx) return;

            let data = [...hourlyTemps];
            if (unit === 'F') {
                data = data.map(c => Math.round(c * 1.8 + 32));
            }

            if (chartInstance) chartInstance.destroy();

            chartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: hourlyLabels,
                    datasets: [{
                        label: 'درجة الحرارة (' + (unit === 'C' ? '°C' : '°F') + ')',
                        data: data,
                        borderColor: '#3b9eff',
                        backgroundColor: 'rgba(59, 158, 255, 0.1)',
                        tension: 0.2,
                        pointBackgroundColor: '#6ad4b4',
                        pointBorderColor: '#fff',
                        borderWidth: 3,
                        fill: true,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#102e40',
                            titleColor: '#f0f9ff',
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.raw + '°' + currentUnit;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: '#b0d4f0',
                                maxRotation: 45,
                                minRotation: 40,
                                font: {
                                    size: 10
                                }
                            },
                            grid: {
                                color: 'rgba(255,255,255,0.03)'
                            }
                        },
                        y: {
                            ticks: {
                                color: '#b0d4f0',
                                font: {
                                    size: 10
                                }
                            },
                            grid: {
                                color: 'rgba(255,255,255,0.03)'
                            }
                        }
                    }
                }
            });
        }

        window.setUnit = function(unit) {
            currentUnit = unit;
            document.getElementById('unitC')?.classList.toggle('active', unit === 'C');
            document.getElementById('unitF')?.classList.toggle('active', unit === 'F');
            renderChart(unit);
            const tempBig = document.getElementById('currentTempDisplay');
            if (tempBig) {
                let baseTemp = {{ $weather['main']['temp'] ?? ($dailySummaries[0]['max'] ?? 26) }};
                if (unit === 'F') baseTemp = Math.round(baseTemp * 1.8 + 32);
                tempBig.innerText = baseTemp + '°';
            }
        };

        window.refreshData = function() {
            renderChart(currentUnit);
        };

        window.showDayDetails = function(index) {
            const dailyData = @json($dailySummaries ?? []);
            if (dailyData[index]) {
                const day = dailyData[index];
                alert(
                    '📅 ' + day.label + '\n' +
                    '🌡 درجة الحرارة: ' + day.max + '° / ' + day.min + '°\n' +
                    '💧 رطوبة: ' + day.humidity + '%\n' +
                    '☔️ فرصة أمطار: ' + day.rain_probability + '%\n' +
                    '💨 هبات رياح: ' + day.wind_gust + ' م/ث\n' +
                    '🌧 كمية أمطار: ' + day.rain_volume + ' ملم\n' +
                    '🎯 الضغط: ' + day.pressure + ' hPa\n' +
                    '☁️ غيوم: ' + day.clouds + '%'
                );
            }
        };

        // دوال المفضلة
        window.toggleFavorite = function() {
            @auth
            const cityName = "{{ $displayName ?? ($weather['name'] ?? ($input ?? '')) }}";
            const lat = {{ $coordinates['lat'] ?? 'null' }};
            const lon = {{ $coordinates['lon'] ?? 'null' }};
            const btn = document.getElementById('favoriteBtn');
            if (!btn) return;

            const icon = btn.querySelector('i');
            const text = document.getElementById('favoriteText');

            if (!cityName || !lat || !lon) {
                Swal.fire({
                    icon: 'error',
                    title: 'خطأ',
                    text: 'لا يمكن إضافة هذه المدينة للمفضلة'
                });
                return;
            }

            const isFavorite = icon.classList.contains('bi-star-fill');

            if (isFavorite) {
                @php
                    $currentFavoriteId = null;
                    foreach ($favorites as $fav) {
                        if ($fav->name == ($displayName ?? ($weather['name'] ?? $input))) {
                            $currentFavoriteId = $fav->id;
                            break;
                        }
                    }
                @endphp
                @if ($currentFavoriteId)
                    fetch('{{ route('weather.favorite.remove') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content')
                            },
                            body: JSON.stringify({
                                id: {{ $currentFavoriteId }}
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                icon.className = 'bi bi-star';
                                text.innerText = 'أضف للمفضلة';
                                Swal.fire({
                                    icon: 'success',
                                    title: 'تم',
                                    text: 'تمت الإزالة من المفضلة',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                setTimeout(() => location.reload(), 1500);
                            }
                        });
                @endif
            } else {
                fetch('{{ route('weather.favorite.add') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        },
                        body: JSON.stringify({
                            name: cityName,
                            lat: lat,
                            lon: lon,
                            country: ''
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            icon.className = 'bi bi-star-fill';
                            text.innerText = 'في المفضلة';
                            Swal.fire({
                                icon: 'success',
                                title: 'تم',
                                text: 'تمت الإضافة للمفضلة',
                                timer: 1500,
                                showConfirmButton: false
                            });
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'خطأ',
                                text: data.message || 'حدث خطأ'
                            });
                        }
                    });
            }
        @else
            Swal.fire({
                icon: 'warning',
                title: 'تنبيه',
                text: 'يجب تسجيل الدخول أولاً'
            }).then(() => {
                window.location.href = '{{ route('login') }}';
            });
        @endauth
        };

        window.removeFavorite = function(id) {
            Swal.fire({
                title: 'تأكيد الحذف',
                text: 'هل أنت متأكد من إزالة هذه المدينة من المفضلة؟',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff6b6b',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'نعم، احذف',
                cancelButtonText: 'إلغاء'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('{{ route('weather.favorite.remove') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content')
                            },
                            body: JSON.stringify({
                                id: id
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'تم',
                                    text: 'تمت الإزالة من المفضلة',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                setTimeout(() => location.reload(), 1500);
                            }
                        });
                }
            });
        };

        // البحث المباشر عن المدن
        const searchInput = document.getElementById('citySearch');
        const searchResults = document.getElementById('searchResults');
        let searchTimeout;

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const query = this.value.trim();
                if (query.length < 2 || query.includes(',')) {
                    searchResults.style.display = 'none';
                    return;
                }
                searchTimeout = setTimeout(() => {
                    fetch(`{{ route('weather.search') }}?q=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.length > 0) {
                                searchResults.innerHTML = data.map(item => `
                                    <div class="search-result-item" onclick="window.location.href='{{ route('weather.index') }}?city=${item.lat},${item.lon}'">
                                        <div class="fw-bold">${item.name}</div>
                                        <small class="text-muted">${item.display_name}</small>
                                    </div>
                                `).join('');
                                searchResults.style.display = 'block';
                            } else {
                                searchResults.style.display = 'none';
                            }
                        });
                }, 300);
            });

            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                    searchResults.style.display = 'none';
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            renderChart('C');
            setTimeout(() => {
                document.querySelectorAll('.alert-custom').forEach(el => {
                    el.style.display = 'none';
                });
            }, 3000);
            setTimeout(initWeatherMap, 500);
        });

        document.getElementById('searchForm')?.addEventListener('submit', function() {
            showLoadingBar();
            document.getElementById('searchSpinner')?.classList.remove('d-none');
            document.getElementById('searchText').innerHTML = ' جاري البحث...';
        });

        // ==================== دوال الخريطة الكاملة ====================
        let map, streetMap, satelliteLayer, terrainMap, darkMap;
        let windLayer, rainViewerLayers = [],
            cloudLayer, tempLayer, pressureLayer, snowLayer, vegetationLayer, firesLayer, eumetsatLayer;
        let globalRainLayer;
        let lightningLayer, stormsLayer, darkCloudsLayer, cyclonesLayer;
        let rainViewerAnimationInterval = null;
        let currentRainViewerIndex = 0;
        let currentBaseLayer = 'street';

        // دوال مساعدة
        function showMapTip(message, type = 'info') {
            const tip = document.createElement('div');
            tip.className = `weather-layer-tip ${type}`;
            tip.innerHTML = message;
            document.querySelector('.weather-map-container').appendChild(tip);
            setTimeout(() => tip.remove(), 3000);
        }

        function isInRadarCoverage(lat, lon) {
            const radarZones = [{
                    minLat: 25,
                    maxLat: 70,
                    minLon: -130,
                    maxLon: -60
                },
                {
                    minLat: 35,
                    maxLat: 70,
                    minLon: -10,
                    maxLon: 40
                },
                {
                    minLat: 20,
                    maxLat: 50,
                    minLon: 100,
                    maxLon: 145
                }
            ];
            return radarZones.some(zone =>
                lat >= zone.minLat && lat <= zone.maxLat &&
                lon >= zone.minLon && lon <= zone.maxLon
            );
        }

        function updateStormSeverity() {
            const severityDiv = document.getElementById('stormSeverity');
            if (!severityDiv) return;
            let severity = 0;
            if (document.getElementById('lightning-toggle')?.checked) severity += 2;
            if (document.getElementById('storms-toggle')?.checked) severity += 3;
            if (document.getElementById('darkclouds-toggle')?.checked) severity += 1;
            if (severity > 0) {
                severityDiv.style.display = 'block';
                let text = 'شدة العواصف: ';
                if (severity <= 2) text += 'منخفضة';
                else if (severity <= 4) text += 'متوسطة';
                else text += 'عالية';
                document.getElementById('severityText').innerText = text;
            } else {
                severityDiv.style.display = 'none';
            }
        }

        // طبقة البرق
        function setupLightningLayer(mapInstance) {
            let lightningMarkers = [];
            let socket = null;
            let reconnectTimer = null;
            let currentUrlIndex = 0;
            let isActive = false;

            const wsUrls = [
                'wss://ws.lightningmaps.org',
                'wss://ws1.blitzortung.org',
                'wss://data.lightningmaps.org',
                'wss://live.blitzortung.org'
            ];

            function createLightningIcon(time) {
                const age = Date.now() - (time * 1000);
                let color = '#ffaa00';
                if (age > 60000) color = '#ff5500';
                if (age > 120000) color = '#ff0000';
                return L.divIcon({
                    html: `<div style="font-size: 24px; color: ${color}; text-shadow: 0 0 10px ${color};">⚡</div>`,
                    className: 'lightning-icon',
                    iconSize: [30, 30],
                    iconAnchor: [15, 15],
                    popupAnchor: [0, -15]
                });
            }

            function addStrike(lat, lon, time, strength = 15) {
                const marker = L.marker([lat, lon], {
                    icon: createLightningIcon(time),
                    title: 'صاعقة'
                }).addTo(mapInstance);
                marker.bindPopup(`<b>⚡ صاعقة ⚡</b><br>الوقت: ${new Date(time * 1000).toLocaleTimeString('ar')}`);
                lightningMarkers.push(marker);
                setTimeout(() => {
                    if (mapInstance.hasLayer(marker)) {
                        mapInstance.removeLayer(marker);
                        lightningMarkers = lightningMarkers.filter(m => m !== marker);
                    }
                }, 5 * 60 * 1000);
            }

            function connectWebSocket() {
                if (!isActive || (socket && socket.readyState === WebSocket.OPEN)) return;

                const url = wsUrls[currentUrlIndex];
                console.log(`محاولة الاتصال بـ ${url}`);
                socket = new WebSocket(url);

                socket.onopen = function() {
                    console.log('✅ متصل بـ ' + url);
                    showMapTip('✅ تم الاتصال بخادم البرق المباشر', 'success');
                };

                socket.onmessage = function(event) {
                    try {
                        const data = JSON.parse(event.data);
                        if (Array.isArray(data)) {
                            data.forEach(strike => {
                                if (strike.length >= 3) {
                                    addStrike(
                                        parseFloat(strike[0]),
                                        parseFloat(strike[1]),
                                        parseInt(strike[2]),
                                        strike.length >= 4 ? parseInt(strike[3]) : 15
                                    );
                                }
                            });
                        } else if (data && data.lat && data.lon && data.time) {
                            addStrike(data.lat, data.lon, data.time, data.strength || 15);
                        }
                    } catch (e) {
                        console.log('رسالة غير JSON:', event.data);
                    }
                };

                socket.onerror = function(err) {
                    console.error('❌ خطأ في WebSocket:', err);
                    socket = null;
                    currentUrlIndex = (currentUrlIndex + 1) % wsUrls.length;
                    if (isActive) {
                        reconnectTimer = setTimeout(connectWebSocket, 5000);
                    }
                };

                socket.onclose = function() {
                    console.log('🔌 تم إغلاق WebSocket');
                    if (isActive) {
                        reconnectTimer = setTimeout(connectWebSocket, 5000);
                    }
                };
            }

            document.getElementById('lightning-toggle').addEventListener('change', function(e) {
                isActive = e.target.checked;
                if (isActive) {
                    currentUrlIndex = 0;
                    connectWebSocket();
                    document.getElementById('lightningStatus').style.opacity = '1';
                    showMapTip('⚡ تم تفعيل طبقة البرق المباشر', 'info');
                } else {
                    if (socket) {
                        socket.close();
                        socket = null;
                    }
                    if (reconnectTimer) {
                        clearTimeout(reconnectTimer);
                        reconnectTimer = null;
                    }
                    lightningMarkers.forEach(marker => mapInstance.removeLayer(marker));
                    lightningMarkers = [];
                    document.getElementById('lightningStatus').style.opacity = '0.5';
                }
                updateStormSeverity();
            });

            document.getElementById('lightning-opacity').addEventListener('input', function(e) {
                const opacity = parseFloat(e.target.value);
                lightningMarkers.forEach(marker => {
                    if (marker.getElement()) {
                        marker.getElement().style.opacity = opacity;
                    }
                });
            });
        }

        // طبقة العواصف
        function setupStormsLayer(mapInstance, apiKey) {
            stormsLayer = L.tileLayer(
                `https://tile.openweathermap.org/map/precipitation_new/{z}/{x}/{y}.png?appid=${apiKey}`, {
                    maxZoom: 18,
                    opacity: 0.7,
                    attribution: 'العواصف © OpenWeatherMap'
                });

            document.getElementById('storms-toggle').addEventListener('change', function(e) {
                if (e.target.checked) {
                    if (!mapInstance.hasLayer(stormsLayer)) mapInstance.addLayer(stormsLayer);
                    document.getElementById('stormStatus').style.opacity = '1';
                } else {
                    if (mapInstance.hasLayer(stormsLayer)) mapInstance.removeLayer(stormsLayer);
                    document.getElementById('stormStatus').style.opacity = '0.5';
                }
                updateStormSeverity();
            });

            document.getElementById('storms-opacity').addEventListener('input', function(e) {
                if (stormsLayer) stormsLayer.setOpacity(parseFloat(e.target.value));
            });
        }

        // طبقة الغيوم الداكنة
        function setupDarkCloudsLayer(mapInstance, apiKey) {
            darkCloudsLayer = L.tileLayer(
                `https://tile.openweathermap.org/map/clouds_new/{z}/{x}/{y}.png?appid=${apiKey}`, {
                    maxZoom: 18,
                    opacity: 0.8,
                    attribution: 'الغيوم الداكنة © OpenWeatherMap',
                    className: 'dark-clouds'
                });
            const style = document.createElement('style');
            style.innerHTML = `
                .dark-clouds .leaflet-tile {
                    filter: brightness(0.6) contrast(1.5) invert(0.1);
                }
            `;
            document.head.appendChild(style);

            document.getElementById('darkclouds-toggle').addEventListener('change', function(e) {
                if (e.target.checked) {
                    if (!mapInstance.hasLayer(darkCloudsLayer)) mapInstance.addLayer(darkCloudsLayer);
                    document.getElementById('darkCloudsStatus').style.opacity = '1';
                } else {
                    if (mapInstance.hasLayer(darkCloudsLayer)) mapInstance.removeLayer(darkCloudsLayer);
                    document.getElementById('darkCloudsStatus').style.opacity = '0.5';
                }
                updateStormSeverity();
            });

            document.getElementById('darkclouds-opacity').addEventListener('input', function(e) {
                if (darkCloudsLayer) darkCloudsLayer.setOpacity(parseFloat(e.target.value));
            });
        }

        // طبقة الأعاصير
        function setupCyclonesLayer(mapInstance) {
            cyclonesLayer = L.tileLayer.wms('https://tiles.zoom.earth/wms/cyclones', {
                layers: 'cyclones',
                format: 'image/png',
                transparent: true,
                attribution: 'الأعاصير © Zoom Earth',
                opacity: 0.8,
                version: '1.1.1'
            });

            document.getElementById('cyclones-toggle').addEventListener('change', function(e) {
                if (e.target.checked) {
                    if (!mapInstance.hasLayer(cyclonesLayer)) mapInstance.addLayer(cyclonesLayer);
                } else {
                    if (mapInstance.hasLayer(cyclonesLayer)) mapInstance.removeLayer(cyclonesLayer);
                }
            });

            document.getElementById('cyclones-opacity').addEventListener('input', function(e) {
                if (cyclonesLayer) cyclonesLayer.setOpacity(parseFloat(e.target.value));
            });
        }

        // تحديث رادار RainViewer
        function refreshRainViewer() {
            setInterval(() => {
                if (map && document.getElementById('rainviewer-toggle').checked) {
                    rainViewerLayers.forEach(layer => {
                        if (map.hasLayer(layer)) map.removeLayer(layer);
                    });
                    setupRainViewerLayers(map);
                    showMapTip('تم تحديث الرادار', 'info');
                }
            }, 10 * 60 * 1000);
        }

        function setupRainViewerLayers(mapInstance) {
            const rainViewerApiUrl = 'https://api.rainviewer.com/public/weather-maps.json';

            fetch(rainViewerApiUrl)
                .then(response => response.json())
                .then(data => {
                    const host = data.host;
                    const frames = data.radar.past;

                    if (!frames || frames.length === 0) {
                        console.warn('لا توجد إطارات رادار متاحة من RainViewer');
                        return;
                    }

                    console.log(`✅ تم تحميل ${frames.length} إطار رادار من RainViewer`);

                    rainViewerLayers = frames.map(frame => {
                        const tileUrl = `${host}${frame.path}/256/{z}/{x}/{y}/2/1_1.png`;
                        return L.tileLayer(tileUrl, {
                            maxZoom: 10,
                            maxNativeZoom: 7,
                            opacity: 0.7,
                            attribution: 'الرادار © <a href="https://www.rainviewer.com">RainViewer</a>'
                        });
                    });

                    const showRainViewerFrame = (index) => {
                        if (rainViewerLayers.length === 0) return;
                        if (index >= rainViewerLayers.length) index = 0;
                        if (index < 0) index = rainViewerLayers.length - 1;

                        if (rainViewerLayers[currentRainViewerIndex]) {
                            mapInstance.removeLayer(rainViewerLayers[currentRainViewerIndex]);
                        }

                        if (document.getElementById('rainviewer-toggle').checked) {
                            rainViewerLayers[index].addTo(mapInstance);
                        }

                        currentRainViewerIndex = index;

                        const date = new Date(frames[index].time * 1000);
                        document.getElementById('rainFrameTime').innerHTML =
                            `🕒 ${date.getHours().toString().padStart(2,'0')}:${date.getMinutes().toString().padStart(2,'0')}`;
                    };

                    const startRainViewerAnimation = () => {
                        if (rainViewerAnimationInterval) clearInterval(rainViewerAnimationInterval);
                        rainViewerAnimationInterval = setInterval(() => {
                            showRainViewerFrame(currentRainViewerIndex + 1);
                        }, 500);
                    };

                    const stopRainViewerAnimation = () => {
                        if (rainViewerAnimationInterval) {
                            clearInterval(rainViewerAnimationInterval);
                            rainViewerAnimationInterval = null;
                        }
                    };

                    window.rainViewerControl = {
                        layers: rainViewerLayers,
                        frames: frames,
                        currentLayerIndex: currentRainViewerIndex,
                        showFrame: showRainViewerFrame,
                        startAnimation: startRainViewerAnimation,
                        stopAnimation: stopRainViewerAnimation
                    };

                    document.getElementById('rainviewer-toggle').addEventListener('change', function(e) {
                        if (e.target.checked) {
                            if (!mapInstance.hasLayer(rainViewerLayers[currentRainViewerIndex])) {
                                rainViewerLayers[currentRainViewerIndex].addTo(mapInstance);
                            }
                            startRainViewerAnimation();
                            document.getElementById('rainStatus').style.opacity = '1';
                        } else {
                            if (mapInstance.hasLayer(rainViewerLayers[currentRainViewerIndex])) {
                                mapInstance.removeLayer(rainViewerLayers[currentRainViewerIndex]);
                            }
                            stopRainViewerAnimation();
                            document.getElementById('rainStatus').style.opacity = '0.5';
                        }
                    });

                    document.getElementById('rainviewer-opacity').addEventListener('input', function(e) {
                        const opacity = parseFloat(e.target.value);
                        rainViewerLayers.forEach(layer => {
                            if (layer) layer.setOpacity(opacity);
                        });
                    });

                    showRainViewerFrame(0);
                    startRainViewerAnimation();

                    showMapTip('✅ تم تحميل رادار RainViewer', 'success');
                })
                .catch(error => {
                    console.error('❌ فشل جلب بيانات RainViewer:', error);
                    showMapTip('❌ فشل تحميل الرادار', 'error');
                });
        }

        // دوال تبديل الطبقات الأساسية
        window.switchBaseLayer = function(type) {
            const layers = {
                'street': streetMap,
                'satellite': satelliteLayer,
                'terrain': terrainMap,
                'dark': darkMap
            };

            if (map.hasLayer(streetMap)) map.removeLayer(streetMap);
            if (map.hasLayer(satelliteLayer)) map.removeLayer(satelliteLayer);
            if (map.hasLayer(terrainMap)) map.removeLayer(terrainMap);
            if (map.hasLayer(darkMap)) map.removeLayer(darkMap);

            if (layers[type]) {
                layers[type].addTo(map);
                currentBaseLayer = type;
            }

            ['street', 'satellite', 'terrain', 'dark'].forEach(t => {
                const badge = document.getElementById(`base${t.charAt(0).toUpperCase() + t.slice(1)}`);
                if (badge) {
                    badge.className = t === type ? 'badge bg-primary' : 'badge bg-secondary';
                    badge.innerHTML = t === type ? '✓' : '';
                }
            });
        };

        // دالة تهيئة الخريطة الرئيسية
        function initWeatherMap() {
            @if (isset($coordinates))
                if (typeof L === 'undefined' || !document.getElementById('weatherMap')) {
                    console.error('Leaflet or map element not found');
                    return;
                }

                try {
                    const lat = {{ $coordinates['lat'] }};
                    const lon = {{ $coordinates['lon'] }};
                    const cityName = "{{ $displayName ?? ($input ?? '') }}";
                    const apiKey = "{{ config('services.openweather.key') }}";

                    console.log('Initializing weather map with all free layers...');

                    map = L.map('weatherMap', {
                        center: [lat, lon],
                        zoom: 6,
                        zoomControl: true,
                        fadeAnimation: true,
                        zoomAnimation: true,
                        fullscreenControl: true
                    });

                    // طبقات الأساس
                    streetMap = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                        maxZoom: 19
                    }).addTo(map);

                    satelliteLayer = L.tileLayer(
                        'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                            attribution: 'Tiles &copy; Esri',
                            maxZoom: 18
                        });

                    terrainMap = L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
                        attribution: 'Map data: OpenTopoMap',
                        maxZoom: 17
                    });

                    darkMap = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                        attribution: '&copy; CARTO',
                        maxZoom: 19
                    });

                    // طبقات الطقس من OpenWeather
                    cloudLayer = L.tileLayer(
                        `https://tile.openweathermap.org/map/clouds_new/{z}/{x}/{y}.png?appid=${apiKey}`, {
                            maxZoom: 18,
                            opacity: 0.6,
                            attribution: 'الغيوم © OpenWeatherMap'
                        });

                    tempLayer = L.tileLayer(
                        `https://tile.openweathermap.org/map/temp_new/{z}/{x}/{y}.png?appid=${apiKey}`, {
                            maxZoom: 18,
                            opacity: 0.5,
                            attribution: 'الحرارة © OpenWeatherMap'
                        });

                    pressureLayer = L.tileLayer(
                        `https://tile.openweathermap.org/map/pressure_new/{z}/{x}/{y}.png?appid=${apiKey}`, {
                            maxZoom: 18,
                            opacity: 0.5,
                            attribution: 'الضغط © OpenWeatherMap'
                        });

                    windLayer = L.tileLayer(
                        `https://tile.openweathermap.org/map/wind_new/{z}/{x}/{y}.png?appid=${apiKey}`, {
                            maxZoom: 18,
                            opacity: 0.9,
                            attribution: 'الرياح © OpenWeatherMap'
                        });

                    globalRainLayer = L.tileLayer(
                        `https://tile.openweathermap.org/map/precipitation_new/{z}/{x}/{y}.png?appid=${apiKey}`, {
                            maxZoom: 18,
                            opacity: 0.6,
                            attribution: 'الهطول العالمي © OpenWeatherMap'
                        });

                    // طبقات NOAA و NASA
                    snowLayer = L.tileLayer(
                        'https://nowcoast.noaa.gov/arcgis/services/nowcoast/forecast_meteocean_inshore_snow_offshore_time/MapServer/WmsServer?', {
                            layers: '1',
                            format: 'image/png',
                            transparent: true,
                            opacity: 0.7,
                            attribution: 'الثلوج © NOAA'
                        });

                    vegetationLayer = L.tileLayer(
                        'https://gibs.earthdata.nasa.gov/wmts/epsg3857/best/BlueMarble_NextGeneration/default/2023-01-01/GoogleMapsCompatible_Level9/{z}/{y}/{x}.jpg', {
                            maxZoom: 9,
                            opacity: 0.5,
                            attribution: 'الغطاء النباتي © NASA MODIS'
                        });

                    firesLayer = L.tileLayer(
                        'https://firms.modaps.eosdis.nasa.gov/mapserver/map/wms/tms/1.0.0/viirs_snpp_global/{z}/{x}/{y}.png', {
                            maxZoom: 8,
                            opacity: 0.8,
                            attribution: 'حرائق الغابات © NASA FIRMS'
                        });

                    eumetsatLayer = L.tileLayer(
                        'https://oisstest.eumetsat.int/geoserver/gwc/service/wms?SERVICE=WMS&VERSION=1.1.1&REQUEST=GetMap&FORMAT=image/png&TRANSPARENT=true&LAYERS=msg_iasi&STYLES=&SRS=EPSG:3857&WIDTH=256&HEIGHT=256&BBOX={bbox-epsg-3857}', {
                            maxZoom: 9,
                            opacity: 0.5,
                            attribution: 'الغيوم © EUMETSAT'
                        });

                    // إعداد الطبقات الجديدة
                    setupLightningLayer(map);
                    setupStormsLayer(map, apiKey);
                    setupDarkCloudsLayer(map, apiKey);
                    setupCyclonesLayer(map);

                    // علامة الموقع
                    var marker = L.marker([lat, lon], {
                        title: cityName,
                        riseOnHover: true
                    }).addTo(map);

                    var popupContent = `
                    <div style="text-align: center; direction: rtl; min-width: 200px;">
                        <b style="color: #3b9eff; font-size: 1.2rem;">🏙️ ${cityName}</b><br>
                        <small>خط العرض: ${lat.toFixed(4)}°</small><br>
                        <small>خط الطول: ${lon.toFixed(4)}°</small>
                        <hr style="margin: 5px 0; border-color: rgba(255,255,255,0.1);">
                        <div class="d-flex justify-content-between">
                            <span>🌡️ {{ round($weather['main']['temp'] ?? 26) }}°C</span>
                            <span>💧 {{ $weather['main']['humidity'] ?? 68 }}%</span>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <span>🌀 {{ $weather['wind']['speed'] ?? 4.2 }} م/ث</span>
                            <span>☁️ {{ $weather['clouds']['all'] ?? 50 }}%</span>
                        </div>
                    </div>
                `;
                    marker.bindPopup(popupContent).openPopup();

                    L.circle([lat, lon], {
                        color: '#3b9eff',
                        fillColor: '#3b9eff',
                        fillOpacity: 0.1,
                        radius: 50000,
                        weight: 2
                    }).addTo(map);

                    // التحقق من تغطية الرادار
                    if (!isInRadarCoverage(lat, lon)) {
                        showMapTip('🛰️ منطقتك خارج تغطية الرادار الأرضي. استخدم طبقة "الهطول العالمي" لمشاهدة الأمطار.',
                            'info');
                        document.getElementById('globalrain-toggle').checked = true;
                        globalRainLayer.addTo(map);
                    }

                    // إضافة RainViewer
                    setupRainViewerLayers(map);
                    refreshRainViewer();

                    // ربط أزرار الطبقات
                    document.getElementById('globalrain-toggle').addEventListener('change', function(e) {
                        if (globalRainLayer) {
                            if (e.target.checked) {
                                if (!map.hasLayer(globalRainLayer)) globalRainLayer.addTo(map);
                            } else {
                                if (map.hasLayer(globalRainLayer)) map.removeLayer(globalRainLayer);
                            }
                        }
                    });
                    document.getElementById('globalrain-opacity').addEventListener('input', function(e) {
                        if (globalRainLayer) globalRainLayer.setOpacity(parseFloat(e.target.value));
                    });

                    document.getElementById('wind-toggle').addEventListener('change', function(e) {
                        if (windLayer) {
                            if (e.target.checked) {
                                if (!map.hasLayer(windLayer)) windLayer.addTo(map);
                                document.getElementById('windStatus').style.opacity = '1';
                            } else {
                                if (map.hasLayer(windLayer)) map.removeLayer(windLayer);
                                document.getElementById('windStatus').style.opacity = '0.5';
                            }
                        }
                    });
                    document.getElementById('wind-opacity').addEventListener('input', function(e) {
                        if (windLayer) windLayer.setOpacity(parseFloat(e.target.value));
                    });

                    document.getElementById('clouds-toggle').addEventListener('change', function(e) {
                        if (cloudLayer) {
                            if (e.target.checked) {
                                if (!map.hasLayer(cloudLayer)) cloudLayer.addTo(map);
                                document.getElementById('cloudStatus').style.opacity = '1';
                            } else {
                                if (map.hasLayer(cloudLayer)) map.removeLayer(cloudLayer);
                                document.getElementById('cloudStatus').style.opacity = '0.5';
                            }
                        }
                    });
                    document.getElementById('clouds-opacity').addEventListener('input', function(e) {
                        if (cloudLayer) cloudLayer.setOpacity(parseFloat(e.target.value));
                    });

                    document.getElementById('temp-toggle').addEventListener('change', function(e) {
                        if (tempLayer) {
                            if (e.target.checked) {
                                if (!map.hasLayer(tempLayer)) tempLayer.addTo(map);
                                document.getElementById('tempStatus').style.opacity = '1';
                            } else {
                                if (map.hasLayer(tempLayer)) map.removeLayer(tempLayer);
                                document.getElementById('tempStatus').style.opacity = '0.5';
                            }
                        }
                    });
                    document.getElementById('temp-opacity').addEventListener('input', function(e) {
                        if (tempLayer) tempLayer.setOpacity(parseFloat(e.target.value));
                    });

                    document.getElementById('pressure-toggle').addEventListener('change', function(e) {
                        if (pressureLayer) {
                            if (e.target.checked) {
                                if (!map.hasLayer(pressureLayer)) pressureLayer.addTo(map);
                                document.getElementById('pressureStatus').style.opacity = '1';
                            } else {
                                if (map.hasLayer(pressureLayer)) map.removeLayer(pressureLayer);
                                document.getElementById('pressureStatus').style.opacity = '0.5';
                            }
                        }
                    });
                    document.getElementById('pressure-opacity').addEventListener('input', function(e) {
                        if (pressureLayer) pressureLayer.setOpacity(parseFloat(e.target.value));
                    });

                    document.getElementById('snow-toggle').addEventListener('change', function(e) {
                        if (snowLayer) {
                            if (e.target.checked) {
                                if (!map.hasLayer(snowLayer)) snowLayer.addTo(map);
                            } else {
                                if (map.hasLayer(snowLayer)) map.removeLayer(snowLayer);
                            }
                        }
                    });
                    document.getElementById('snow-opacity').addEventListener('input', function(e) {
                        if (snowLayer) snowLayer.setOpacity(parseFloat(e.target.value));
                    });

                    document.getElementById('veg-toggle').addEventListener('change', function(e) {
                        if (vegetationLayer) {
                            if (e.target.checked) {
                                if (!map.hasLayer(vegetationLayer)) vegetationLayer.addTo(map);
                            } else {
                                if (map.hasLayer(vegetationLayer)) map.removeLayer(vegetationLayer);
                            }
                        }
                    });
                    document.getElementById('veg-opacity').addEventListener('input', function(e) {
                        if (vegetationLayer) vegetationLayer.setOpacity(parseFloat(e.target.value));
                    });

                    document.getElementById('fires-toggle').addEventListener('change', function(e) {
                        if (firesLayer) {
                            if (e.target.checked) {
                                if (!map.hasLayer(firesLayer)) firesLayer.addTo(map);
                            } else {
                                if (map.hasLayer(firesLayer)) map.removeLayer(firesLayer);
                            }
                        }
                    });
                    document.getElementById('fires-opacity').addEventListener('input', function(e) {
                        if (firesLayer) firesLayer.setOpacity(parseFloat(e.target.value));
                    });

                    document.getElementById('eumetsat-toggle').addEventListener('change', function(e) {
                        if (eumetsatLayer) {
                            if (e.target.checked) {
                                if (!map.hasLayer(eumetsatLayer)) eumetsatLayer.addTo(map);
                            } else {
                                if (map.hasLayer(eumetsatLayer)) map.removeLayer(eumetsatLayer);
                            }
                        }
                    });
                    document.getElementById('eumetsat-opacity').addEventListener('input', function(e) {
                        if (eumetsatLayer) eumetsatLayer.setOpacity(parseFloat(e.target.value));
                    });

                    document.getElementById('toggleLayersPanel').addEventListener('click', function() {
                        const panel = document.getElementById('layersPanel');
                        panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
                    });

                    switchBaseLayer('street');

                    console.log('✅ Map initialized successfully with all free layers');
                    showMapTip('✅ تم تحميل جميع طبقات الطقس المجانية', 'success');

                } catch (error) {
                    console.error('❌ Map error:', error);
                    showMapTip('❌ خطأ في تحميل الخريطة: ' + error.message, 'error');
                }
            @endif
        }
    </script>
</body>

</html>
