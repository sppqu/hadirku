@extends('layouts.mobile', ['title' => 'Profil Pegawai - HadirKU'])

@section('content')
<div class="relative bg-slate-50 min-h-full">
    <!-- Header Section -->
    <div class="bg-app-green text-white px-5 pt-5 pb-16 rounded-b-[36px] shadow-sm relative text-center">
        <div class="flex items-center justify-between mb-4">
            <a href="{{ route('user.dashboard') }}" class="text-white hover:text-emerald-100 p-1.5 -ml-1.5 rounded-lg active:scale-95 transition">
                <i class="fa-solid fa-arrow-left text-lg"></i>
            </a>
            <h1 class="text-base font-bold tracking-tight">Profil Pegawai</h1>
            <div class="w-7"></div>
        </div>

        <!-- Avatar in Header with Change Photo Action -->
        <div class="inline-block relative">
            <img id="user-avatar-img" src="{{ $user->avatar_url }}" alt="Profile" class="w-20 h-20 rounded-full border-4 border-white shadow-lg object-cover mx-auto">
            <label for="avatar-input" class="absolute bottom-0 right-0 w-7 h-7 bg-white text-emerald-600 rounded-full border-2 border-emerald-500 flex items-center justify-center shadow-md cursor-pointer hover:bg-emerald-50 active:scale-95 transition" title="Ubah Foto Profil">
                <i class="fa-solid fa-camera text-[11px]"></i>
            </label>
            <input type="file" id="avatar-input" accept="image/jpeg,image/png,image/jpg,image/webp" class="hidden" onchange="handleAvatarSelected(this)">
        </div>

        <h2 class="text-base font-bold text-white mt-2">{{ $user->name }}</h2>
        <p class="text-xs text-emerald-100 font-mono">{{ $user->nik ?? 'HK-PEG' }} &bull; {{ $user->position ?? 'Pegawai' }}</p>

        <!-- Quick Change Avatar Button -->
        <button type="button" onclick="document.getElementById('avatar-input').click()" class="mt-2 text-[11px] font-semibold text-white/90 bg-white/20 hover:bg-white/30 backdrop-blur-xs px-3 py-1 rounded-full transition inline-flex items-center gap-1.5 active:scale-95">
            <i class="fa-solid fa-camera text-[10px]"></i>
            <span>Ubah Foto Profil</span>
        </button>
    </div>

    <!-- Main Container -->
    <div class="px-5 -mt-8 relative z-10 space-y-4 pb-6">
        
        <!-- Info Pegawai Card -->
        <div class="bg-white rounded-3xl p-5 shadow-[0_4px_20px_rgba(0,0,0,0.04)] border border-slate-100 space-y-3">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                <i class="fa-solid fa-id-badge text-emerald-600"></i>
                <span>Informasi Data Diri</span>
            </h3>

            <div class="space-y-2.5 text-xs">
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-400">Email</span>
                    <span class="font-semibold text-slate-700">{{ $user->email }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-400">Nomor HP/WA</span>
                    <span class="font-semibold text-slate-700">{{ $user->phone ?? '-' }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-400">Departemen</span>
                    <span class="font-semibold text-slate-700">{{ $user->department->name ?? '-' }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-400">Jam Kerja (Shift)</span>
                    <span class="font-semibold text-slate-700">{{ $user->shift->name ?? 'Shift Regular' }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-400">Jadwal Hari Kerja</span>
                    <span class="font-semibold text-slate-700">{{ $user->working_days_summary }}</span>
                </div>
                <div class="flex justify-between py-1.5">
                    <span class="text-slate-400">Lokasi Kantor Presensi</span>
                    <span class="font-semibold text-emerald-600">{{ $user->office->name ?? 'Kantor Pusat' }}</span>
                </div>
            </div>
        </div>

        <!-- Registrasi Wajah Biometrik Card (Alur Persetujuan Admin & Kunci) -->
        <div class="bg-white rounded-3xl p-5 shadow-[0_4px_20px_rgba(0,0,0,0.04)] border border-slate-100 space-y-3.5">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-2xl {{ $user->face_reference_status === 'approved' ? 'bg-emerald-50 text-emerald-600' : ($user->face_reference_status === 'pending' ? 'bg-amber-50 text-amber-600' : ($user->face_reference_status === 'rejected' ? 'bg-red-50 text-red-600' : 'bg-slate-100 text-slate-500')) }} flex items-center justify-center text-lg">
                        <i class="fa-solid fa-id-card-clip"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-800">Registrasi Wajah Biometrik</h4>
                        <span class="text-[11px] text-slate-400">Pencocokan Presensi Pegawai</span>
                    </div>
                </div>

                @if($user->face_reference_status === 'pending')
                    <span class="text-[10px] font-bold px-2.5 py-1 bg-amber-100 text-amber-800 rounded-full flex items-center gap-1">
                        <i class="fa-solid fa-clock text-[9px] animate-pulse"></i> Menunggu Persetujuan
                    </span>
                @elseif($user->face_reference_status === 'approved')
                    <span class="text-[10px] font-bold px-2.5 py-1 bg-emerald-100 text-emerald-800 rounded-full flex items-center gap-1">
                        <i class="fa-solid fa-shield-check text-[9px]"></i> Wajah Terverifikasi
                    </span>
                @elseif($user->face_reference_status === 'rejected')
                    <span class="text-[10px] font-bold px-2.5 py-1 bg-red-100 text-red-800 rounded-full flex items-center gap-1">
                        <i class="fa-solid fa-circle-xmark text-[9px]"></i> Ditolak Admin
                    </span>
                @else
                    <span class="text-[10px] font-bold px-2.5 py-1 bg-slate-100 text-slate-600 rounded-full flex items-center gap-1">
                        <i class="fa-solid fa-minus text-[9px]"></i> Belum Terdaftar
                    </span>
                @endif
            </div>

            <!-- Preview Foto Sesuai Status -->
            @if($user->face_reference_status === 'pending')
                <div class="flex items-center gap-3 p-3.5 bg-amber-50/70 rounded-2xl border border-amber-200">
                    <div class="relative flex-shrink-0">
                        <img src="{{ $user->pending_face_reference_url }}" alt="Wajah Diajukan" class="w-14 h-14 rounded-xl object-cover border-2 border-amber-400 shadow-xs">
                        <span class="absolute -bottom-1 -right-1 w-5 h-5 bg-amber-500 text-white rounded-full flex items-center justify-center text-[10px] shadow-xs">
                            <i class="fa-solid fa-clock"></i>
                        </span>
                    </div>
                    <div class="overflow-hidden space-y-0.5">
                        <span class="text-[10px] uppercase font-extrabold text-amber-700 tracking-wider block">Pengajuan Sedang Ditinjau</span>
                        <h5 class="text-xs font-bold text-slate-800 truncate">Menunggu Persetujuan Admin</h5>
                        <p class="text-[10px] text-amber-900/80 leading-snug">Admin akan memverifikasi kesesuaian wajah Anda sebelum diaktifkan untuk presensi.</p>
                    </div>
                </div>
            @elseif($user->face_reference_status === 'approved' && $user->face_reference)
                <div class="flex items-center gap-3 p-3.5 bg-emerald-50/60 rounded-2xl border border-emerald-200">
                    <div class="relative flex-shrink-0">
                        <img id="current-face-img" src="{{ $user->face_reference_url }}" alt="Wajah Referensi" class="w-14 h-14 rounded-xl object-cover border-2 border-emerald-500 shadow-xs">
                        <span class="absolute -bottom-1 -right-1 w-5 h-5 bg-emerald-600 text-white rounded-full flex items-center justify-center text-[10px] shadow-xs">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                    </div>
                    <div class="overflow-hidden space-y-0.5">
                        <span class="text-[10px] uppercase font-extrabold text-emerald-700 tracking-wider block">Foto Referensi Aktif</span>
                        <h5 class="text-xs font-bold text-slate-800 truncate">Wajah Terverifikasi & Terkunci</h5>
                        <p class="text-[10px] text-slate-500 leading-snug">Wajah ini digunakan untuk validasi biometrik otomatis saat presensi masuk & pulang.</p>
                    </div>
                </div>
            @elseif($user->face_reference_status === 'rejected')
                <div class="flex items-center gap-3 p-3.5 bg-red-50/70 rounded-2xl border border-red-200">
                    <div class="w-14 h-14 rounded-xl bg-red-100 text-red-600 flex items-center justify-center text-2xl flex-shrink-0">
                        <i class="fa-solid fa-circle-exclamation"></i>
                    </div>
                    <div class="overflow-hidden space-y-0.5">
                        <span class="text-[10px] uppercase font-extrabold text-red-700 tracking-wider block">Pengajuan Ditolak</span>
                        <h5 class="text-xs font-bold text-slate-800 truncate">Perlu Foto Ulang</h5>
                        <p class="text-[10px] text-red-600 leading-snug">{{ $user->face_rejection_reason ?: 'Foto tidak memenuhi syarat atau buram.' }}</p>
                    </div>
                </div>
            @else
                <div class="flex items-center gap-3 p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                    <div class="w-14 h-14 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center text-2xl flex-shrink-0">
                        <i class="fa-solid fa-user-gear"></i>
                    </div>
                    <div class="overflow-hidden space-y-0.5">
                        <span class="text-[10px] uppercase font-extrabold text-slate-500 tracking-wider block">Wajah Belum Terdaftar</span>
                        <h5 class="text-xs font-bold text-slate-800 truncate">Perlu Registrasi Wajah</h5>
                        <p class="text-[10px] text-slate-400 leading-snug">Silakan daftarkan wajah agar sistem dapat mencocokkan wajah saat presensi.</p>
                    </div>
                </div>
            @endif

            <!-- Status Izin & Tombol Interaksi -->
            @if($user->face_reference_status === 'pending')
                <!-- Status PENDING: Registrasi terkunci, menunggu approval admin -->
                <div class="p-3 bg-amber-50 border border-amber-200 rounded-2xl text-[11px] text-amber-900 space-y-1">
                    <div class="flex items-center gap-1.5 font-bold text-amber-800">
                        <i class="fa-solid fa-hourglass-half text-amber-600"></i>
                        <span>Proses Verifikasi Oleh Administrator</span>
                    </div>
                    <p class="text-amber-800/80 text-[10px] leading-relaxed">
                        Data foto wajah Anda sedang diperiksa oleh Administrator kantor. Registrasi wajah dikunci sementara sampai proses verifikasi selesai.
                    </p>
                </div>

                <div class="w-full py-2.5 bg-slate-100 text-slate-400 font-bold text-xs rounded-xl border border-slate-200 flex items-center justify-center gap-2 cursor-not-allowed">
                    <i class="fa-solid fa-lock text-xs"></i>
                    <span>Terkunci - Menunggu Persetujuan Admin</span>
                </div>

            @elseif($user->allow_face_registration)
                <!-- Diizinkan oleh Admin (Buka Kunci Aktif) -->
                <div class="p-3 bg-emerald-50/70 border border-emerald-200/80 rounded-2xl text-[11px] text-emerald-950 space-y-1">
                    <div class="flex items-center gap-1.5 font-bold text-emerald-800">
                        <i class="fa-solid fa-lock-open text-emerald-600"></i>
                        <span>Izin Registrasi Wajah Dibuka Admin</span>
                    </div>
                    <p class="text-emerald-800/80 text-[10px] leading-relaxed">
                        Administrator telah mengizinkan Anda untuk mendaftarkan atau memperbarui wajah. Setelah foto dikirim, status akan menjadi <em>Menunggu Persetujuan Admin</em> dan terkunci kembali.
                    </p>
                </div>

                <button type="button" onclick="openFaceRegistrationModal()" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center justify-center gap-2 active:scale-98">
                    <i class="fa-solid fa-expand text-sm"></i>
                    <span>{{ $user->face_reference ? 'Pindai & Ajukan Perbaruan Wajah' : 'Pindai & Daftarkan Wajah Sekarang' }}</span>
                </button>

            @else
                <!-- TERKUNCI (Perlu Ajukan ke Admin untuk Ubah) -->
                @if($user->face_unlock_requested)
                    <div class="p-3 bg-blue-50 border border-blue-200 rounded-2xl text-[11px] text-blue-950 space-y-1">
                        <div class="flex items-center gap-1.5 font-bold text-blue-800">
                            <i class="fa-solid fa-paper-plane text-blue-600"></i>
                            <span>Permintaan Buka Kunci Terkirim</span>
                        </div>
                        <p class="text-blue-800/80 text-[10px] leading-relaxed">
                            Permohonan untuk membuka izin pendaftaran wajah telah dikirimkan ke Admin. Harap tunggu admin memberikan izin melalui panel admin.
                        </p>
                    </div>

                    <div class="w-full py-2.5 bg-slate-100 text-slate-400 font-bold text-xs rounded-xl border border-slate-200 flex items-center justify-center gap-2 cursor-not-allowed">
                        <i class="fa-solid fa-clock text-xs"></i>
                        <span>Menunggu Admin Membuka Izin</span>
                    </div>
                @else
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-2xl text-[11px] text-slate-600 space-y-1">
                        <div class="flex items-center gap-1.5 font-bold text-slate-700">
                            <i class="fa-solid fa-lock text-slate-400"></i>
                            <span>Registrasi Wajah Terkunci</span>
                        </div>
                        <p class="text-slate-500 text-[10px] leading-relaxed">
                            {{ $user->face_reference_status === 'approved' ? 'Registrasi wajah terkunci setelah diverifikasi. Jika ingin mengubah foto wajah, silakan ajukan permohonan ke admin.' : 'Fitur registrasi wajah mandiri terkunci. Silakan ajukan permohonan ke admin untuk membuka izin pendaftaran.' }}
                        </p>
                    </div>

                    <button type="button" onclick="confirmRequestFaceUnlock()" class="w-full py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center justify-center gap-2 active:scale-98">
                        <i class="fa-solid fa-key text-xs"></i>
                        <span>{{ $user->face_reference_status === 'approved' ? 'Ajukan Perubahan Wajah ke Admin' : 'Minta Izin Registrasi ke Admin' }}</span>
                    </button>
                @endif
            @endif
        </div>

        <!-- Ubah Kata Sandi Card -->
        <div class="bg-white rounded-3xl p-5 shadow-[0_4px_20px_rgba(0,0,0,0.04)] border border-slate-100 space-y-4">
            <div class="flex items-center justify-between cursor-pointer" onclick="togglePasswordCard()">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-key"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-800">Keamanan Akun</h4>
                        <span class="text-[11px] text-slate-400">Ubah Kata Sandi Login</span>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 text-slate-400 text-xs">
                    <span id="pwd-status-text" class="text-[10px] font-semibold text-emerald-600">{{ $errors->has('current_password') || $errors->has('password') ? 'Tutup' : 'Ubah' }}</span>
                    <i id="pwd-chevron" class="fa-solid fa-chevron-down text-xs transition-transform duration-200 {{ $errors->has('current_password') || $errors->has('password') ? 'rotate-180' : '' }}"></i>
                </div>
            </div>

            <form id="password-form" action="{{ route('user.profile.password') }}" method="POST" class="space-y-3.5 {{ $errors->has('current_password') || $errors->has('password') ? '' : 'hidden' }} pt-3 border-t border-slate-100">
                @csrf
                <!-- Current Password -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Kata Sandi Saat Ini</label>
                    <div class="relative">
                        <input type="password" name="current_password" id="current_password" required placeholder="Masukkan kata sandi lama" class="w-full text-xs px-3.5 py-2.5 rounded-xl border {{ $errors->has('current_password') ? 'border-red-400 bg-red-50/30' : 'border-slate-200' }} focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 pr-10">
                        <button type="button" onclick="togglePasswordVisibility('current_password', this)" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 text-xs">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                    @error('current_password')
                        <p class="text-[11px] text-red-500 mt-1 font-semibold flex items-center gap-1">
                            <i class="fa-solid fa-circle-exclamation text-[10px]"></i> {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- New Password -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Kata Sandi Baru</label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required minlength="6" placeholder="Minimal 6 karakter" class="w-full text-xs px-3.5 py-2.5 rounded-xl border {{ $errors->has('password') ? 'border-red-400 bg-red-50/30' : 'border-slate-200' }} focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 pr-10">
                        <button type="button" onclick="togglePasswordVisibility('password', this)" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 text-xs">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-[11px] text-red-500 mt-1 font-semibold flex items-center gap-1">
                            <i class="fa-solid fa-circle-exclamation text-[10px]"></i> {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Confirm New Password -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Konfirmasi Kata Sandi Baru</label>
                    <div class="relative">
                        <input type="password" name="password_confirmation" id="password_confirmation" required minlength="6" placeholder="Ulangi kata sandi baru" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 pr-10">
                        <button type="button" onclick="togglePasswordVisibility('password_confirmation', this)" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 text-xs">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center justify-center gap-1.5 active:scale-98">
                    <i class="fa-solid fa-check"></i>
                    <span>Simpan Kata Sandi Baru</span>
                </button>
            </form>
        </div>

        <!-- GPS Geofencing Status Card -->
        <div class="bg-white rounded-3xl p-5 border border-slate-100 shadow-sm flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-location-crosshairs"></i>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-800">Radius Presensi GPS</h4>
                    <span class="text-[11px] text-slate-400">{{ $user->office->radius_meters ?? 150 }} Meter Dari Kantor</span>
                </div>
            </div>
            <span class="text-[11px] font-bold px-2.5 py-1 bg-blue-100 text-blue-800 rounded-full">
                ✓ Geofence On
            </span>
        </div>

        <!-- Switch to Admin button (if admin) -->
        @if($user->role === 'admin')
            <a href="/admin" class="w-full flex items-center justify-center space-x-2 py-3 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 rounded-2xl font-bold text-xs transition">
                <i class="fa-solid fa-shield-halved"></i>
                <span>Masuk ke Panel Admin HadirKU</span>
            </a>
        @endif

        <!-- Logout Button -->
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="w-full flex items-center justify-center space-x-2 py-3.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-2xl font-bold text-xs transition">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                <span>Keluar Akun (Logout)</span>
            </button>
        </form>

    </div>
</div>

<!-- Face Registration Scanner Modal -->
<div id="face-reg-modal" class="fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4 hidden backdrop-blur-xs">
    <div class="bg-white w-full max-w-sm rounded-[32px] overflow-hidden shadow-2xl flex flex-col max-h-[92vh] border border-slate-100">
        <!-- Modal Header -->
        <div class="px-5 pt-4 pb-3 flex items-center justify-between border-b border-slate-100">
            <div>
                <h3 class="text-sm font-extrabold text-slate-800">Registrasi Wajah Biometrik</h3>
                <p class="text-[10px] text-slate-400">Posisikan wajah tegak di dalam lingkaran oval</p>
            </div>
            <button type="button" onclick="closeFaceRegistrationModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <!-- Camera Viewfinder -->
        <div class="relative bg-slate-950 aspect-[3/4] overflow-hidden flex items-center justify-center">
            <!-- Live Video -->
            <video id="face-reg-video" autoplay playsinline muted class="w-full h-full object-cover transform -scale-x-100"></video>

            <!-- Captured Preview Image -->
            <img id="face-reg-preview" class="w-full h-full object-cover transform -scale-x-100 hidden" alt="Captured Face">

            <!-- Oval Viewfinder Overlay -->
            <div id="face-reg-oval" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <div class="w-48 h-60 border-2 border-dashed border-emerald-400 rounded-[50%] shadow-[0_0_0_9999px_rgba(0,0,0,0.55)] flex flex-col items-center justify-end pb-3 relative">
                    <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-transparent via-emerald-400 to-transparent animate-pulse"></div>
                    <span id="face-reg-guide-text" class="text-[10px] text-white bg-black/60 px-2.5 py-0.5 rounded-full backdrop-blur-xs font-medium">
                        Arahkan Wajah ke Sini
                    </span>
                </div>
            </div>

            <!-- Top Tip Badge -->
            <div class="absolute top-3 left-3 right-3 flex justify-between items-center z-10 pointer-events-none">
                <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-emerald-500/90 text-white backdrop-blur-xs">
                    <i class="fa-solid fa-camera mr-1"></i> Kamera Depan
                </span>
                <span id="face-reg-tip" class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-black/60 text-white backdrop-blur-xs">
                    Pencahayaan Terang
                </span>
            </div>
        </div>

        <!-- Modal Controls -->
        <div class="p-4 bg-white space-y-2">
            <!-- Initial State: Snap Button -->
            <div id="face-reg-action-snap" class="space-y-2">
                <button type="button" onclick="snapFacePhoto()" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-2xl shadow-lg transition flex items-center justify-center gap-2 active:scale-98">
                    <i class="fa-solid fa-camera text-sm"></i>
                    <span>Ambil Foto Wajah</span>
                </button>
                <p class="text-[10px] text-slate-400 text-center">Pastikan wajah tidak tertutup masker, kacamata hitam, atau topi.</p>
            </div>

            <!-- Confirm State: Retake or Save Button -->
            <div id="face-reg-action-confirm" class="hidden flex gap-2">
                <button type="button" onclick="retakeFacePhoto()" class="flex-1 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-2xl transition flex items-center justify-center gap-1.5 active:scale-98">
                    <i class="fa-solid fa-rotate-left"></i>
                    <span>Foto Ulang</span>
                </button>
                <button type="button" id="btn-save-face-ref" onclick="submitFaceRegistration()" class="flex-[1.5] py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-2xl shadow-md transition flex items-center justify-center gap-1.5 active:scale-98">
                    <i class="fa-solid fa-check"></i>
                    <span>Simpan Wajah</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function togglePasswordVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'fa-regular fa-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'fa-regular fa-eye';
        }
    }

    function togglePasswordCard() {
        const form = document.getElementById('password-form');
        const chevron = document.getElementById('pwd-chevron');
        const statusText = document.getElementById('pwd-status-text');
        form.classList.toggle('hidden');
        if (form.classList.contains('hidden')) {
            chevron.classList.remove('rotate-180');
            statusText.innerText = 'Ubah';
        } else {
            chevron.classList.add('rotate-180');
            statusText.innerText = 'Tutup';
            document.getElementById('current_password').focus();
        }
    }

    function handleAvatarSelected(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];

        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            Swal.fire({
                icon: 'error',
                title: 'Format Tidak Didukung',
                text: 'Hanya file gambar JPG, PNG, atau WEBP yang diperbolehkan.',
                confirmButtonColor: '#059669'
            });
            input.value = '';
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            Swal.fire({
                icon: 'error',
                title: 'Ukuran Terlalu Besar',
                text: 'Ukuran foto maksimal 5 MB.',
                confirmButtonColor: '#059669'
            });
            input.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            Swal.fire({
                title: 'Perbarui Foto Profil',
                html: `
                    <div class="text-center space-y-3">
                        <img src="${e.target.result}" class="w-24 h-24 rounded-full border-4 border-emerald-500 shadow-md object-cover mx-auto">
                        <p class="text-xs text-slate-600 font-medium">Apakah Anda yakin ingin menggunakan foto ini sebagai foto profil baru?</p>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonColor: '#059669',
                cancelButtonColor: '#64748B',
                confirmButtonText: '<i class="fa-solid fa-cloud-arrow-up mr-1"></i> Simpan Foto',
                cancelButtonText: 'Batal',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    const formData = new FormData();
                    formData.append('avatar', file);
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                    return fetch("{{ route('user.profile.avatar') }}", {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(err => { throw new Error(err.message || 'Gagal mengunggah foto.'); });
                        }
                        return response.json();
                    })
                    .catch(error => {
                        Swal.showValidationMessage(`Gagal: ${error.message}`);
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed && result.value && result.value.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: result.value.message,
                        confirmButtonColor: '#059669'
                    }).then(() => {
                        window.location.reload();
                    });
                }
                input.value = '';
            });
        };
        reader.readAsDataURL(file);
    }

    // --- FITUR REGISTRASI WAJAH BIOMETRIK ---
    let faceRegStream = null;
    let capturedFaceData = null;

    function confirmRequestFaceUnlock() {
        Swal.fire({
            title: 'Ajukan ke Admin?',
            text: 'Apakah Anda ingin mengajukan permohonan kepada Administrator untuk membuka izin registrasi/pembaruan wajah?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Ya, Ajukan Sekarang',
            cancelButtonText: 'Batal'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    Swal.fire({
                        title: 'Mengirim Pengajuan...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    const response = await fetch("{{ route('user.profile.face_unlock_request') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Pengajuan Berhasil Dikirim!',
                            text: data.message,
                            confirmButtonColor: '#059669',
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Pengajuan Gagal',
                            text: data.message || 'Terjadi kesalahan.',
                            confirmButtonColor: '#EF4444',
                        });
                    }
                } catch (err) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Koneksi Gagal',
                        text: 'Gagal menghubungi server.',
                        confirmButtonColor: '#EF4444',
                    });
                }
            }
        });
    }

    function showLockedFaceAlert() {
        confirmRequestFaceUnlock();
    }

    async function openFaceRegistrationModal() {
        const modal = document.getElementById('face-reg-modal');
        modal.classList.remove('hidden');

        document.getElementById('face-reg-video').classList.remove('hidden');
        document.getElementById('face-reg-preview').classList.add('hidden');
        document.getElementById('face-reg-oval').classList.remove('hidden');
        document.getElementById('face-reg-action-snap').classList.remove('hidden');
        document.getElementById('face-reg-action-confirm').classList.add('hidden');
        capturedFaceData = null;

        try {
            if (faceRegStream) {
                stopFaceRegCamera();
            }
            faceRegStream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } },
                audio: false
            });
            const video = document.getElementById('face-reg-video');
            video.srcObject = faceRegStream;
            video.play();
        } catch (err) {
            console.warn('Camera error:', err);
            // Fallback for simulated/demo environment without physical webcam
            const video = document.getElementById('face-reg-video');
            video.poster = "https://ui-avatars.com/api/?name=Face+Scan&size=400&background=022c22&color=10b981";
        }
    }

    function stopFaceRegCamera() {
        if (faceRegStream) {
            faceRegStream.getTracks().forEach(t => t.stop());
            faceRegStream = null;
        }
    }

    function closeFaceRegistrationModal() {
        stopFaceRegCamera();
        document.getElementById('face-reg-modal').classList.add('hidden');
    }

    function snapFacePhoto() {
        const video = document.getElementById('face-reg-video');
        const preview = document.getElementById('face-reg-preview');
        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth || 480;
        canvas.height = video.videoHeight || 640;
        const ctx = canvas.getContext('2d');

        if (video.videoWidth > 0) {
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            capturedFaceData = canvas.toDataURL('image/jpeg', 0.9);
        } else {
            // Demo fallback capture
            ctx.fillStyle = '#064e3b';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = '#ffffff';
            ctx.font = '24px sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText('Simulasi Wajah Referensi', canvas.width / 2, canvas.height / 2);
            capturedFaceData = canvas.toDataURL('image/jpeg', 0.9);
        }

        preview.src = capturedFaceData;
        preview.classList.remove('hidden');
        video.classList.add('hidden');
        document.getElementById('face-reg-oval').classList.add('hidden');

        document.getElementById('face-reg-action-snap').classList.add('hidden');
        document.getElementById('face-reg-action-confirm').classList.remove('hidden');

        stopFaceRegCamera();
    }

    function retakeFacePhoto() {
        openFaceRegistrationModal();
    }

    async function submitFaceRegistration() {
        if (!capturedFaceData) return;

        const btn = document.getElementById('btn-save-face-ref');
        const origHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i><span>Menyimpan...</span>';

        try {
            const response = await fetch("{{ route('user.profile.face_register') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    photo: capturedFaceData
                })
            });

            const result = await response.json();

            if (result.success) {
                closeFaceRegistrationModal();
                Swal.fire({
                    icon: 'success',
                    title: 'Wajah Berhasil Diajukan!',
                    text: result.message,
                    confirmButtonColor: '#059669',
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Registrasi Ditolak',
                    text: result.message || 'Terjadi kesalahan saat mendaftarkan wajah.',
                    confirmButtonColor: '#EF4444',
                });
                btn.disabled = false;
                btn.innerHTML = origHtml;
            }
        } catch (e) {
            Swal.fire({
                icon: 'error',
                title: 'Koneksi Gagal',
                text: 'Gagal menghubungi server.',
                confirmButtonColor: '#EF4444',
            });
            btn.disabled = false;
            btn.innerHTML = origHtml;
        }
    }
</script>
@endsection
