<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Masuk Pegawai - HadirKU</title>

    <!-- App Favicon & PWA Icons -->
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.jpg') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#059669">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="HadirKU">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .mobile-shell {
            max-width: 440px;
            min-height: 100vh;
            margin: 0 auto;
            position: relative;
            background: #FFFFFF;
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
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
                min-height: 850px;
            }
        }
    </style>
</head>
<body class="bg-slate-900 text-slate-800 antialiased">
    <div class="mobile-shell flex flex-col justify-between">
        <!-- Header Illustration & Branding -->
        <div class="bg-gradient-to-b from-emerald-600 to-emerald-700 text-white px-8 pt-8 pb-14 rounded-b-[40px] text-center shadow-lg relative overflow-hidden">
            <!-- Decorative circle -->
            <div class="absolute -top-12 -right-12 w-40 h-40 bg-white/10 rounded-full blur-xl"></div>
            <div class="absolute -bottom-8 -left-8 w-32 h-32 bg-emerald-500/30 rounded-full blur-lg"></div>

            <div class="relative z-10 flex flex-col items-center">
                <img src="{{ asset('images/logo.jpg') }}" alt="Logo HadirKU" class="w-24 h-24 rounded-3xl shadow-xl border-4 border-white/30 object-cover mb-5 transform hover:scale-105 transition-transform duration-300">
                <h1 class="text-2xl font-black tracking-tight text-white mb-1">HAdirKu</h1>
                <p class="text-[11px] text-emerald-100 font-semibold uppercase tracking-wider">Presensi &bull; Mudah &bull; Terpercaya</p>
            </div>
        </div>

        <!-- Login Form Card -->
        <div class="flex-1 px-8 -mt-6 z-10">
            <div class="bg-white rounded-3xl p-6 shadow-xl border border-slate-100">
                <h2 class="text-xl font-bold text-slate-900 mb-1">Selamat Datang! 👋</h2>
                <p class="text-xs text-slate-500 mb-6">Silakan masuk menggunakan Email atau {{ \App\Models\CampusProfile::getEmployeeIdLabel() }} pegawai Anda.</p>

                @if($errors->any())
                    <div class="mb-4 p-3 bg-red-50 border-l-4 border-red-500 rounded-r-lg">
                        <div class="flex items-center text-red-700 text-xs font-semibold">
                            <i class="fa-solid fa-circle-exclamation mr-2"></i>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    </div>
                @endif

                <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="login" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email atau {{ \App\Models\CampusProfile::getEmployeeIdLabel() }}</label>
                        <div class="relative rounded-xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-id-card text-sm"></i>
                            </div>
                            <input 
                                type="text" 
                                name="login" 
                                id="login" 
                                value="{{ old('login') }}"
                                required 
                                placeholder="Email atau {{ \App\Models\CampusProfile::getEmployeeIdLabel() }}"
                                class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Kata Sandi</label>
                        <div class="relative rounded-xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-lock text-sm"></i>
                            </div>
                            <input 
                                type="password" 
                                name="password" 
                                id="password" 
                                required 
                                placeholder="••••••••"
                                class="w-full pl-10 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition"
                            >
                            <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600">
                                <i class="fa-regular fa-eye text-sm" id="eye-icon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs pt-1">
                        <label class="flex items-center space-x-2 text-slate-600 cursor-pointer">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                            <span>Ingat saya</span>
                        </label>
                        <a href="javascript:void(0)" onclick="alert('Hubungi admin IT atau HR untuk reset password NIK Anda.')" class="text-emerald-600 font-semibold hover:underline">Lupa sandi?</a>
                    </div>

                    <button 
                        type="submit" 
                        class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] text-white font-bold rounded-xl shadow-lg shadow-emerald-600/30 transition flex items-center justify-center space-x-2 text-sm"
                    >
                        <span>Masuk ke Presensi</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                </form>
            </div>

            <!-- Footer di Bawah Card Login -->
            <div class="mt-6 mb-6 text-center text-xs text-slate-400 space-y-1">
                <p>&copy; {{ date('Y') }} HadirKU. All rights reserved.</p>
                <p class="text-[11px] text-slate-400">Powered by <span class="font-bold text-emerald-600">Gemasoft</span></p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
