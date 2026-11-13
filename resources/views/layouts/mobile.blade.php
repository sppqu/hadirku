<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'HadirKU - E-Presensi' }}</title>

    <!-- App Favicon & Icons -->
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.jpg') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#059669">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="HadirKU">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0fdf9',
                            100: '#ccfbf1',
                            500: '#14b8a6',
                            600: '#0d9488',
                            700: '#0f766e',
                            800: '#115e59',
                            900: '#134e4a',
                        },
                        app: {
                            green: '#059669',
                            lightgreen: '#10B981',
                            darkgreen: '#047857',
                        }
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- Google Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- SweetAlert2 for polished mobile alerts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-tap-highlight-color: transparent;
        }
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        /* Mobile phone simulation container on desktop */
        .mobile-shell {
            width: 100%;
            max-width: 440px;
            min-height: 100vh;
            margin: 0 auto;
            position: relative;
            background: #F8FAFC;
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }
        @media (min-width: 640px) {
            body {
                background: #0f172a;
                padding: 24px 0;
            }
            .mobile-shell {
                border-radius: 40px;
                overflow: hidden;
                border: 8px solid #1e293b;
                min-height: 880px;
                max-height: 92vh;
                overflow-y: auto;
            }
        }
        .punch-btn-shadow {
            box-shadow: 0 10px 25px -3px rgba(5, 150, 105, 0.4), 0 4px 6px -4px rgba(5, 150, 105, 0.2);
        }
        .safe-bottom {
            padding-bottom: max(0.5rem, env(safe-area-inset-bottom, 0.5rem));
        }
    </style>
</head>
<body class="bg-slate-900 text-slate-800 antialiased selection:bg-brand-500 selection:text-white">

    <div class="mobile-shell flex flex-col hide-scrollbar">
        <!-- Main Content Area -->
        <main class="flex-1 pb-24">
            @yield('content')
        </main>

        <!-- Floating Bottom Navigation Bar (Sticky to viewport) -->
        <nav class="sticky bottom-0 left-0 right-0 w-full max-w-[440px] mx-auto bg-white border-t border-slate-100 px-4 py-2 safe-bottom flex items-center justify-between z-30 shadow-[0_-4px_20px_rgba(0,0,0,0.06)] sm:rounded-b-[32px]">
            <!-- Dashboard -->
            <a href="{{ route('user.dashboard') }}" class="flex flex-col items-center justify-center flex-1 py-1 transition {{ request()->routeIs('user.dashboard') ? 'text-app-green font-bold' : 'text-slate-400 hover:text-slate-600' }}">
                <i class="fa-solid fa-house text-lg mb-1"></i>
                <span class="text-[11px]">Beranda</span>
            </a>

            <!-- History -->
            <a href="{{ route('user.history') }}" class="flex flex-col items-center justify-center flex-1 py-1 transition {{ request()->routeIs('user.history') ? 'text-app-green font-bold' : 'text-slate-400 hover:text-slate-600' }}">
                <i class="fa-regular fa-clock text-lg mb-1"></i>
                <span class="text-[11px]">Riwayat</span>
            </a>

            <!-- Floating Center Punch Button -->
            <div class="flex flex-col items-center justify-center flex-1 -mt-7">
                <a href="{{ route('user.punch') }}" class="w-14 h-14 rounded-full {{ request()->routeIs('user.punch*') ? 'bg-emerald-700 ring-4 ring-emerald-300' : 'bg-app-green hover:bg-app-darkgreen' }} text-white flex items-center justify-center punch-btn-shadow transition-transform active:scale-95 border-4 border-white">
                    <i class="fa-solid fa-fingerprint text-2xl"></i>
                </a>
                <span class="text-[11px] font-semibold {{ request()->routeIs('user.punch*') ? 'text-emerald-700 font-bold' : 'text-app-green' }} mt-1">Presensi</span>
            </div>

            <!-- Reports / Leaves -->
            <a href="{{ route('user.leaves') }}" class="flex flex-col items-center justify-center flex-1 py-1 transition {{ request()->routeIs('user.leaves') ? 'text-app-green font-bold' : 'text-slate-400 hover:text-slate-600' }}">
                <i class="fa-solid fa-chart-simple text-lg mb-1"></i>
                <span class="text-[11px]">Izin & Cuti</span>
            </a>

            <!-- Profile -->
            <a href="{{ route('user.profile') }}" class="flex flex-col items-center justify-center flex-1 py-1 transition {{ request()->routeIs('user.profile') ? 'text-app-green font-bold' : 'text-slate-400 hover:text-slate-600' }}">
                <i class="fa-regular fa-user text-lg mb-1"></i>
                <span class="text-[11px]">Profil</span>
            </a>
        </nav>
    </div>

    <!-- Flash message alerts -->
    @if(session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: "{{ session('success') }}",
                confirmButtonColor: '#059669',
                timer: 3000
            });
        </script>
    @endif

    @if(session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Perhatian',
                text: "{{ session('error') }}",
                confirmButtonColor: '#EF4444',
            });
        </script>
    @endif

    <!-- PWA Install Prompt Banner -->
    <div id="pwa-install-banner" class="fixed bottom-20 left-4 right-4 max-w-[408px] mx-auto bg-slate-900 text-white p-3.5 rounded-2xl shadow-2xl z-40 hidden items-center justify-between border border-emerald-500/30">
        <div class="flex items-center space-x-3">
            <img src="{{ asset('images/logo.jpg') }}" alt="Logo" class="w-10 h-10 rounded-xl object-cover shadow-sm">
            <div>
                <h4 class="font-bold text-xs">Pasang HadirKU</h4>
                <p class="text-[10px] text-slate-300">Tambahkan aplikasi ke layar utama</p>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            <button type="button" onclick="dismissPwaPrompt()" class="text-xs text-slate-400 hover:text-white px-2 py-1">Nanti</button>
            <button type="button" onclick="installPwa()" class="bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs px-3 py-1.5 rounded-xl shadow-md transition">Pasang</button>
        </div>
    </div>

    <script>
        // Register Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(err => {
                    console.log('SW registration error:', err);
                });
            });
        }

        // PWA Install prompt handling
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            const banner = document.getElementById('pwa-install-banner');
            if (banner && !sessionStorage.getItem('pwa_prompt_dismissed')) {
                banner.classList.remove('hidden');
                banner.classList.add('flex');
            }
        });

        function installPwa() {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('User accepted HadirKU install prompt');
                    }
                    deferredPrompt = null;
                    dismissPwaPrompt();
                });
            }
        }

        function dismissPwaPrompt() {
            const banner = document.getElementById('pwa-install-banner');
            if (banner) {
                banner.classList.add('hidden');
                banner.classList.remove('flex');
            }
            sessionStorage.setItem('pwa_prompt_dismissed', 'true');
        }
    </script>

    @stack('scripts')
</body>
</html>
