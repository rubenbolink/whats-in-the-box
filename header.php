<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#121212" id="themeColorMeta">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Box-Zolder</title>
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/png" href="assets/icon_v3.png">
    
    <!-- Fonts: Inter (UI) & Outfit (Headings) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700&display=swap" rel="stylesheet">

    <style>
        :root {
            /* Palette: Modern Earth Tones */
            --primary: #8D6E4B; /* Refined Cardboard Brown */
            --primary-dark: #5C4632;
            --accent: #E67E22; /* Vivid Orange for actions */
            
            /* Light Theme (Apple-esque) */
            --bg: #F2F2F7;
            --surface: #FFFFFF;
            --surface-secondary: #FFFFFF;
            --border: rgba(0, 0, 0, 0.08); /* Subtle separator */
            --text-main: #1C1C1E;
            --text-sub: #8E8E93;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.08);
            
            /* Layout */
            --radius-lg: 20px;
            --radius-md: 14px;
            --header-height: 60px;
            --safe-area-top: env(safe-area-inset-top);
        }

        [data-theme="dark"] {
            /* Dark Theme (OLED / Deep Material) */
            --bg: #000000;
            --surface: #1C1C1E;
            --surface-secondary: #2C2C2E;
            --border: rgba(255, 255, 255, 0.12);
            --text-main: #FFFFFF;
            --text-sub: #98989D;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.2);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.3);
        }

        * { -webkit-tap-highlight-color: transparent; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text-main);
            margin: 0;
            padding-top: calc(var(--header-height) + var(--safe-area-top));
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            transition: background 0.3s ease, color 0.3s ease;
        }

        a { text-decoration: none; color: inherit; }

        /* --- Global Header --- */
        .navbar {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: calc(var(--header-height) + var(--safe-area-top));
            padding-top: var(--safe-area-top);
            background: rgba(var(--surface), 0.85); /* Failed var reference fix below */
            background: var(--surface);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-left: 20px;
            padding-right: 20px;
            transition: all 0.3s ease;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--text-main);
        }

        .nav-brand img {
            width: 38px;
            height: 38px;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        }

        .nav-brand span {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 1.25rem;
            letter-spacing: -0.5px;
        }

        .theme-toggle {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: var(--surface-secondary);
            border: 1px solid var(--border);
            color: var(--text-main);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            font-size: 1.1rem;
            transition: transform 0.2s;
        }
        .theme-toggle:active { transform: scale(0.92); }

        /* --- Layout --- */
        .container {
            max-width: 640px;
            margin: 0 auto;
            padding: 24px 16px 100px 16px; /* Updated mobile padding */
            animation: fadeEnter 0.4s cubic-bezier(0.2, 0.8, 0.2, 1);
        }

        @keyframes fadeEnter { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* --- Components --- */
        .page-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 24px;
        }

        .page-title {
            font-family: 'Outfit'; font-weight: 700; font-size: 1.75rem;
            margin: 0; color: var(--text-main);
        }

        .back-btn {
            color: var(--primary);
            font-weight: 500;
            text-decoration: none;
            display: flex; align-items: center; gap: 4px;
            font-size: 1rem;
        }

        .card {
            background: var(--surface);
            border-radius: var(--radius-lg);
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
            position: relative;
            overflow: hidden;
            transition: all 0.2s cubic-bezier(0.2, 0.8, 0.2, 1);
        }

        .card.clickable:active {
            transform: scale(0.98);
            background: var(--surface-secondary);
        }

        .input-group { margin-bottom: 16px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.9rem; color: var(--text-sub); }
        
        input, select, textarea {
            width: 100%;
            padding: 16px;
            border-radius: var(--radius-md);
            border: 1px solid var(--border);
            background: var(--bg); /* Slight contrast from card */
            color: var(--text-main);
            font-size: 1rem; font-family: inherit;
            appearance: none; -webkit-appearance: none;
            transition: all 0.2s;
        }
        
        input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(141, 110, 75, 0.15); background: var(--surface); }

        .btn-primary {
            display: block; width: 100%;
            padding: 16px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            font-weight: 600; font-size: 1.05rem;
            text-align: center; text-decoration: none;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(141, 110, 75, 0.3);
            transition: all 0.2s;
        }
        .btn-primary:active { transform: scale(0.98); opacity: 0.9; }

        .btn-text {
            background: none; border: none; padding: 10px;
            color: var(--primary); font-weight: 600; cursor: pointer;
        }

        /* --- Utilities --- */
        .flex-between { display: flex; justify-content: space-between; align-items: center; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .text-muted { color: var(--text-sub); }
        .text-center { text-align: center; }

        /* --- Print --- */
        @media print {
            .navbar, .theme-toggle, .no-print { display: none !important; }
            body { padding-top: 0; background: white; }
            .card { border: none; box-shadow: none; padding: 0; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="nav-brand">
            <img src="assets/icon_v3.png" alt="Box-Zolder">
            <span>Zolder</span>
        </a>
        <div style="display:flex; gap: 10px; align-items: center;">
             <!-- Debug/Reset Button -->
             <button onclick="forceReset()" style="background:none; border:none; font-size:1.2rem; cursor:pointer;" title="Hard Reset App">🔄</button>
             <button class="theme-toggle" id="themeToggle">☀️</button>
        </div>
    </nav>
    
    <div id="updateToast" style="display:none; position: fixed; top: 80px; left: 50%; transform: translateX(-50%); background: var(--primary); color: white; padding: 12px 24px; border-radius: 50px; box-shadow: 0 4px 20px rgba(0,0,0,0.3); z-index: 2000; font-weight: 600; font-size: 0.9rem; cursor: pointer;" onclick="window.location.reload()">
        Nieuwe versie beschikbaar! Klik om te updaten.
    </div>

    <div id="offlineToast" style="display:none; position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%); background: rgba(0,0,0,0.8); color: white; padding: 12px 24px; border-radius: 50px; backdrop-filter: blur(10px); z-index: 2000; font-weight: 500; font-size: 0.9rem;">
        Offline Modus
    </div>

    <!-- DB Scripts -->
    <script src="assets/js/offline-db.js?v=2.1"></script>
    <script>
        // Design Logic 2.0
        const toggle = document.getElementById('themeToggle');
        const meta = document.getElementById('themeColorMeta');
        
        function setTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
            toggle.textContent = theme === 'dark' ? '☀️' : '🌙';
            meta.setAttribute('content', theme === 'dark' ? '#000000' : '#F2F2F7');
        }

        const pref = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        setTheme(pref);
        
        toggle.addEventListener('click', () => {
            const current = document.documentElement.getAttribute('data-theme');
            setTheme(current === 'dark' ? 'light' : 'dark');
        });

        window.addEventListener('offline', () => document.getElementById('offlineToast').style.display = 'block');
        window.addEventListener('online', () => document.getElementById('offlineToast').style.display = 'none');
        window.addEventListener('pageshow', (event) => { if (event.persisted) window.location.reload(); });

        // FORCE RESET FUNCTION
        async function forceReset() {
            if(confirm('App verversen? Dit leegt de cache en herlaadt de pagina.')) {
                if ('serviceWorker' in navigator) {
                    const registrations = await navigator.serviceWorker.getRegistrations();
                    for(let registration of registrations) {
                        await registration.unregister();
                    }
                }
                caches.keys().then(names => {
                    for (let name of names) caches.delete(name);
                });
                window.location.reload(true);
            }
        }
        
        // Service Worker Logic
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('sw.js').then(reg => {
                reg.onupdatefound = () => {
                    const installingWorker = reg.installing;
                    installingWorker.onstatechange = () => {
                        if (installingWorker.state === 'installed') {
                            if (navigator.serviceWorker.controller) {
                                document.getElementById('updateToast').style.display = 'block';
                            }
                        }
                    };
                };
            });
            // Force check
            navigator.serviceWorker.ready.then(r => r.update());
        }
    </script>
