<?php
// app/views/pages/maintenance.php

// Auto detect BASE_URL jika belum diset
if (!isset($BASE)) {
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $serverName = $_SERVER['SERVER_NAME'] ?? '';
    $httpHost = $_SERVER['HTTP_HOST'] ?? '';

    $isLocalhost = (
        strpos($serverName, 'localhost') !== false ||
        strpos($serverName, '127.0.0.1') !== false ||
        strpos($httpHost, 'localhost') !== false ||
        strpos($requestUri, '/rekap-konten/public') !== false ||
        strpos($scriptName, '/rekap-konten/public') !== false
    );

    $BASE = $isLocalhost ? (defined('BASE_URL') ? BASE_URL : '/rekap-konten/public') : '';
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Website Dalam Pemeliharaan - KEMENKUM SULSEL</title>
    <link rel="icon" type="image/jpeg" href="<?= $BASE ?>/Images/lamaccalogonobg.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        :root {
            --bg-color: #0F172A;
            --card-bg: rgba(30, 41, 59, 0.75);
            --border-color: rgba(255, 255, 255, 0.1);
            --text-color: #F8FAFC;
            --text-muted: #94A3B8;
            --primary: #3B82F6;
            --primary-hover: #2563EB;
            --accent: #F59E0B;
        }

        body.light {
            --bg-color: #F1F5F9;
            --card-bg: rgba(255, 255, 255, 0.85);
            --border-color: rgba(0, 0, 0, 0.08);
            --text-color: #0F172A;
            --text-muted: #64748B;
            --primary: #0E4BF1;
            --primary-hover: #0A3BC7;
            --accent: #D97706;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            min-height: 100vh;
            background-color: var(--bg-color);
            color: var(--text-color);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow-x: hidden;
            position: relative;
            transition: background-color 0.3s ease;
        }

        /* Animated Background Gradients */
        .bg-glow {
            position: absolute;
            border-radius: 50%;
            filter: blur(100px);
            z-index: 0;
            pointer-events: none;
        }

        .glow-1 {
            width: 350px;
            height: 350px;
            background: rgba(14, 75, 241, 0.25);
            top: -50px;
            left: -50px;
        }

        .glow-2 {
            width: 300px;
            height: 300px;
            background: rgba(245, 158, 11, 0.2);
            bottom: -50px;
            right: -50px;
        }

        /* Main Container */
        .maintenance-card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 650px;
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 45px 35px;
            text-align: center;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Logo Area */
        .brand-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 25px;
        }

        .brand-logo img {
            width: 48px;
            height: 48px;
            object-fit: contain;
        }

        .brand-name {
            font-size: 1.2rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            color: var(--text-color);
        }

        /* Animated Icon */
        .icon-container {
            position: relative;
            width: 100px;
            height: 100px;
            margin: 0 auto 30px auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .icon-bg {
            width: 85px;
            height: 85px;
            background: rgba(245, 158, 11, 0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent);
            font-size: 38px;
            box-shadow: 0 0 30px rgba(245, 158, 11, 0.2);
        }

        .gear-spin {
            animation: spin 10s linear infinite;
        }

        @keyframes spin {
            100% {
                transform: rotate(360deg);
            }
        }

        /* Status Badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 16px;
            background: rgba(245, 158, 11, 0.15);
            border: 1px solid rgba(245, 158, 11, 0.3);
            border-radius: 20px;
            color: var(--accent);
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 20px;
        }

        .status-badge .dot {
            width: 8px;
            height: 8px;
            background-color: var(--accent);
            border-radius: 50%;
            box-shadow: 0 0 8px var(--accent);
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.4;
            }
        }

        /* Content Text */
        h1 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 15px;
            line-height: 1.3;
        }

        p.message {
            font-size: 15px;
            color: var(--text-muted);
            line-height: 1.7;
            margin-bottom: 30px;
            padding: 0 10px;
        }

        /* Actions */
        .action-group {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: var(--primary);
            color: #FFFFFF;
            box-shadow: 0 4px 15px rgba(14, 75, 241, 0.3);
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
        }

        .btn-outline {
            background: transparent;
            color: var(--text-color);
            border: 1px solid var(--border-color);
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: var(--text-muted);
        }

        /* Theme Switcher Float */
        .theme-toggle {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            color: var(--text-color);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .theme-toggle:hover {
            transform: scale(1.1);
        }

        /* Footer Info */
        .footer-text {
            margin-top: 35px;
            font-size: 12px;
            color: var(--text-muted);
            border-top: 1px solid var(--border-color);
            padding-top: 20px;
        }

        @media (max-width: 576px) {
            .maintenance-card {
                padding: 35px 20px;
                border-radius: 18px;
            }

            h1 {
                font-size: 1.4rem;
            }

            p.message {
                font-size: 13.5px;
            }

            .action-group {
                flex-direction: column;
                width: 100%;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="bg-glow glow-1"></div>
    <div class="bg-glow glow-2"></div>

    <button class="theme-toggle" id="btnThemeToggle" title="Ganti Mode Terang/Gelap">
        <i class="fas fa-moon"></i>
    </button>

    <div class="maintenance-card">
        <div class="brand-logo">
            <img src="<?= $BASE ?>/Images/LOGO KEMENKUM.jpeg" alt="Kemenkum Sulsel Logo">
            <span class="brand-name">KANWIL KEMENKUM SULSEL</span>
        </div>

        <div class="status-badge">
            <span class="dot"></span>
            Mode Maintenance Aktif
        </div>

        <div class="icon-container">
            <div class="icon-bg">
                <i class="fas fa-cog gear-spin"></i>
            </div>
        </div>

        <h1>Sistem Dalam Pemeliharaan</h1>

        <p class="message">
            Website ini sedang maintenance, mohon maaf atas ketidaknyamanan anda, tetap pantau situs kami secara
            berkala.
        </p>

        <div class="action-group">
            <button onclick="window.location.reload();" class="btn btn-primary">
                <i class="fas fa-sync-alt"></i> Segarkan Halaman
            </button>
            <a href="<?= $BASE ?>/index.php?page=login&logout_first=1" class="btn btn-outline">
                <i class="fas fa-user-shield"></i> Login Administrator
            </a>
        </div>

        <div class="footer-text">
            &copy; <?= date('Y') ?> Kantor Wilayah Kementerian Hukum Sulawesi Selatan
        </div>
    </div>

    <script>
        // Check dark/light mode
        const btnThemeToggle = document.getElementById('btnThemeToggle');
        const icon = btnThemeToggle.querySelector('i');

        let savedTheme = localStorage.getItem('maintenance_theme') || (window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark');

        function applyTheme(theme) {
            if (theme === 'light') {
                document.body.classList.add('light');
                icon.className = 'fas fa-sun';
            } else {
                document.body.classList.remove('light');
                icon.className = 'fas fa-moon';
            }
            localStorage.setItem('maintenance_theme', theme);
        }

        applyTheme(savedTheme);

        btnThemeToggle.addEventListener('click', () => {
            const isLight = document.body.classList.contains('light');
            applyTheme(isLight ? 'dark' : 'light');
        });
    </script>
</body>

</html>