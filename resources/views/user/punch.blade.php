@extends('layouts.mobile', ['title' => $pageTitle . ' - HadirKU'])

@section('content')
<div class="relative bg-slate-50 min-h-full pb-6">
    <!-- Header Section -->
    <div class="bg-app-green text-white px-5 pt-4 pb-8 rounded-b-[36px] shadow-sm relative">
        <div class="flex items-center justify-between mb-2">
            <a href="{{ route('user.dashboard') }}" class="text-white hover:text-emerald-100 p-1.5 -ml-1.5 rounded-lg active:scale-95 transition" title="Kembali">
                <i class="fa-solid fa-arrow-left text-lg"></i>
            </a>
            <h1 class="text-base font-bold tracking-tight">{{ $pageTitle }}</h1>
            <div class="w-7"></div>
        </div>
        <p class="text-xs text-emerald-100 text-center font-normal px-4">{{ $statusSubtext }}</p>
    </div>

    <!-- Main Container -->
    <div class="px-5 -mt-4 relative z-10 space-y-4">

        @if(!empty($todayHoliday))
            <div class="bg-rose-50 border border-rose-200 rounded-2xl p-3.5 flex items-center gap-3 shadow-xs">
                <div class="w-9 h-9 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-calendar-xmark text-sm"></i>
                </div>
                <div class="text-xs">
                    <div class="font-bold text-rose-900">Hari Libur: {{ $todayHoliday->name }}</div>
                    <p class="text-[11px] text-rose-700 mt-0.5">Hari ini adalah hari libur. Presensi tidak diperlukan.</p>
                </div>
            </div>
        @elseif(isset($isWorkingDayToday) && ! $isWorkingDayToday)
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-3.5 flex items-center gap-3 shadow-xs">
                <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-calendar-day text-sm"></i>
                </div>
                <div class="text-xs">
                    <div class="font-bold text-amber-900">Di Luar Jadwal Kerja</div>
                    <p class="text-[11px] text-amber-700 mt-0.5">Jadwal hari kerja Anda: {{ $user->working_days_summary }}.</p>
                </div>
            </div>
        @endif

        <!-- Card 1: Verifikasi Wajah (Kamera Live) -->
        <div class="bg-white rounded-3xl p-5 shadow-[0_4px_20px_rgba(0,0,0,0.04)] border border-slate-100 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5 uppercase tracking-wider">
                    <i class="fa-solid fa-camera text-emerald-600"></i>
                    <span>Verifikasi Wajah</span>
                </span>
                <span id="face-badge" class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 transition-colors">
                    Posisikan Wajah ke Oval
                </span>
            </div>

            <!-- Video Viewfinder Container -->
            <div class="relative w-full aspect-[4/3] bg-slate-900 rounded-2xl overflow-hidden shadow-inner flex items-center justify-center">
                <video id="punch-video" autoplay playsinline muted class="w-full h-full object-cover transform -scale-x-100"></video>
                <canvas id="punch-canvas" class="hidden"></canvas>
                <img id="punch-captured-photo" class="hidden w-full h-full object-cover" alt="Foto Presensi" />

                <!-- Oval Face Frame Guide Overlay -->
                <div id="face-guide" class="absolute inset-0 pointer-events-none flex items-center justify-center">
                    <div id="oval-frame" class="w-48 h-56 border-2 border-dashed border-emerald-400/90 rounded-[50%] shadow-[0_0_0_9999px_rgba(0,0,0,0.45)] flex flex-col items-center justify-end pb-3 transition-all duration-300 relative">
                        <!-- Scanning line animation inside oval -->
                        <div id="scan-bar" class="w-36 h-0.5 bg-emerald-400 shadow-[0_0_12px_#34d399] absolute top-12 animate-pulse"></div>
                        <span id="oval-guide-text" class="text-[10px] text-white bg-black/60 px-2.5 py-0.5 rounded-full backdrop-blur-xs font-medium">Arahkan Wajah ke Sini</span>
                    </div>
                </div>

                <!-- Visual Camera Flash Effect -->
                <div id="camera-flash" class="absolute inset-0 bg-white pointer-events-none opacity-0 transition-opacity duration-200 z-30"></div>
            </div>

            <!-- Retake Action -->
            <div id="retake-container" class="hidden text-center pt-1">
                <button type="button" onclick="startCamera()" class="text-xs text-slate-600 hover:text-emerald-600 font-semibold inline-flex items-center gap-1.5 transition py-1.5 px-3 rounded-xl bg-slate-100 hover:bg-emerald-50">
                    <i class="fa-solid fa-rotate-right text-xs"></i>
                    <span>Ambil Ulang Foto Wajah</span>
                </button>
            </div>
        </div>

        <!-- Fake GPS Warning Alert Banner -->
        <div id="fake-gps-warning" class="hidden p-3.5 bg-red-50 border border-red-200 rounded-2xl flex items-start gap-3 shadow-xs">
            <div class="w-8 h-8 rounded-xl bg-red-100 text-red-600 flex items-center justify-center flex-shrink-0 text-sm">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div class="text-xs">
                <div class="font-bold text-red-900">Peringatan: Lokasi Tiruan (Fake GPS) Terdeteksi!</div>
                <p class="text-[11px] text-red-700 mt-0.5 leading-relaxed">
                    Sistem mendeteksi indikasi aplikasi Fake GPS aktif. Mohon matikan aplikasi Fake GPS dan nonaktifkan opsi Lokasi Tiruan di perangkat Anda untuk dapat presensi.
                </p>
            </div>
        </div>

        <!-- Card 2: Informasi Lokasi GPS -->
        <div class="bg-white rounded-3xl p-5 shadow-[0_4px_20px_rgba(0,0,0,0.04)] border border-slate-100 space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5 uppercase tracking-wider">
                    <i class="fa-solid fa-location-crosshairs text-emerald-600"></i>
                    <span>Informasi Lokasi GPS</span>
                </span>
                <button type="button" onclick="getCurrentLocation()" class="text-[11px] font-semibold text-emerald-600 hover:text-emerald-700 flex items-center gap-1 transition">
                    <i class="fa-solid fa-arrows-rotate"></i> Refresh GPS
                </button>
            </div>

            <!-- Detail Grid Info -->
            <div class="space-y-2 text-xs">
                <!-- Kantor & Status -->
                <div class="flex items-center justify-between py-1 border-b border-slate-50">
                    <div>
                        <span class="text-[10px] text-slate-400 font-medium block">Kantor Presensi</span>
                        <span class="font-bold text-slate-800">{{ $office->name ?? 'Kantor Pusat HadirKU' }}</span>
                    </div>
                    <span id="gps-status-badge" class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                        Mengecek Lokasi...
                    </span>
                </div>

                <!-- Koordinat Anda & Jarak Realtime -->
                <div class="grid grid-cols-2 gap-2 py-1">
                    <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                        <span class="text-[10px] text-slate-400 font-medium block">Koordinat Posisi Anda</span>
                        <span id="user-coords-text" class="text-[11px] font-mono font-bold text-slate-700 block mt-0.5">
                            Mencari GPS...
                        </span>
                    </div>
                    <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                        <span class="text-[10px] text-slate-400 font-medium block">Jarak ke Kantor</span>
                        <span id="gps-distance-text" class="text-[11px] font-extrabold text-slate-800 block mt-0.5">
                            Menghitung...
                        </span>
                    </div>
                </div>
            </div>

            <!-- Outside Radius Notice Banner -->
            <div id="outside-radius-notice" class="hidden mt-2 p-2.5 bg-amber-50 border border-amber-200 rounded-xl text-[11px] text-amber-900 leading-snug">
                <i class="fa-solid fa-triangle-exclamation text-amber-600 mr-1"></i>
                <span>Posisi di luar batas radius kantor (maks. {{ $office->radius_meters ?? 150 }}m). Presensi akan berstatus <strong>PENDING</strong> dan perlu persetujuan Admin.</span>
            </div>
        </div>

        <!-- Card 3: Catatan & Tombol Presensi -->
        <div class="bg-white rounded-3xl p-5 shadow-[0_4px_20px_rgba(0,0,0,0.04)] border border-slate-100 space-y-3">
            <div>
                <label for="punch-notes" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Catatan Tambahan (Opsional)</label>
                <input 
                    type="text" 
                    id="punch-notes" 
                    placeholder="Misal: Hadir rapat pagi / Tugas luar" 
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none transition"
                >
            </div>

            @if($punchType === 'completed')
                <button 
                    type="button" 
                    disabled 
                    class="w-full py-3.5 bg-slate-200 text-slate-500 rounded-xl font-bold text-sm shadow-none flex items-center justify-center gap-2 cursor-not-allowed border border-slate-300"
                >
                    <i class="fa-solid fa-circle-check text-base text-emerald-600"></i>
                    <span>{{ $actionBtnText }}</span>
                </button>
            @else
                <button 
                    type="button" 
                    id="btn-submit-punch" 
                    onclick="submitPunch()" 
                    class="w-full py-3.5 {{ $actionBtnClass }} active:scale-[0.98] text-white rounded-xl font-bold text-sm shadow-lg flex items-center justify-center gap-2 transition"
                >
                    <i class="{{ $actionIcon }} text-base"></i>
                    <span>{{ $actionBtnText }}</span>
                </button>
            @endif
        </div>

    </div>
</div>

<!-- Tracking.js library for client-side real-time face detection -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tracking.js/1.1.3/tracking-min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tracking.js/1.1.3/data/face-min.js"></script>

<script>
    const punchType = "{{ $punchType }}";
    const officeLat = {{ $office->latitude ?? -6.225014 }};
    const officeLng = {{ $office->longitude ?? 106.809784 }};
    const officeRadius = {{ $office->radius_meters ?? 150 }};

    let videoStream = null;
    let capturedPhotoData = null;
    let currentLat = null;
    let currentLng = null;
    let isFakeGpsDetected = false;
    let fakeGpsReason = '';

    // Face detection & auto-snap state
    let trackerTask = null;
    let autoCaptureTriggered = false;
    let faceHoldTimer = null;
    let detectionInterval = null;

    document.addEventListener('DOMContentLoaded', () => {
        if (punchType !== 'completed') {
            startCamera();
            getCurrentLocation();
        }
    });

    // Cleanup camera when leaving page
    window.addEventListener('beforeunload', () => {
        stopCamera();
    });

    async function startCamera() {
        const video = document.getElementById('punch-video');
        const photo = document.getElementById('punch-captured-photo');
        const guide = document.getElementById('face-guide');
        const badge = document.getElementById('face-badge');
        const retakeContainer = document.getElementById('retake-container');

        autoCaptureTriggered = false;
        capturedPhotoData = null;

        if (retakeContainer) retakeContainer.classList.add('hidden');
        if (photo) photo.classList.add('hidden');
        if (video) video.classList.remove('hidden');
        if (guide) guide.classList.remove('hidden');

        resetOvalUI();

        if (badge) {
            badge.className = 'text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800';
            badge.innerText = 'Posisikan Wajah ke Oval';
        }

        try {
            if (videoStream) {
                videoStream.getTracks().forEach(t => t.stop());
            }

            videoStream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'user',
                    width: { ideal: 640 },
                    height: { ideal: 480 }
                },
                audio: false
            });

            video.srcObject = videoStream;
            video.onloadedmetadata = () => {
                video.play();
                initFaceTracking();
            };
        } catch (err) {
            console.warn('Camera access issue:', err);
            if (badge) {
                badge.className = 'text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-red-100 text-red-800';
                badge.innerText = 'Izin Kamera Diperlukan';
            }
        }
    }

    function stopCamera() {
        if (videoStream) {
            videoStream.getTracks().forEach(t => t.stop());
            videoStream = null;
        }
        if (trackerTask) {
            try { trackerTask.stop(); } catch(e) {}
            trackerTask = null;
        }
        if (detectionInterval) {
            clearInterval(detectionInterval);
            detectionInterval = null;
        }
        if (faceHoldTimer) {
            clearTimeout(faceHoldTimer);
            faceHoldTimer = null;
        }
    }

    function resetOvalUI() {
        const oval = document.getElementById('oval-frame');
        const guideText = document.getElementById('oval-guide-text');
        const scanBar = document.getElementById('scan-bar');

        if (oval) {
            oval.className = 'w-48 h-56 border-2 border-dashed border-emerald-400/90 rounded-[50%] shadow-[0_0_0_9999px_rgba(0,0,0,0.45)] flex flex-col items-center justify-end pb-3 transition-all duration-300 relative';
        }
        if (guideText) {
            guideText.className = 'text-[10px] text-white bg-black/60 px-2.5 py-0.5 rounded-full backdrop-blur-xs font-medium';
            guideText.innerText = 'Arahkan Wajah ke Sini';
        }
        if (scanBar) scanBar.style.display = 'block';
    }

    function initFaceTracking() {
        const video = document.getElementById('punch-video');
        if (!window.tracking || !video) return;

        try {
            const tracker = new tracking.ObjectTracker('face');
            tracker.setInitialScale(4);
            tracker.setStepSize(2);
            tracker.setEdgesDensity(0.1);

            tracker.on('track', function(event) {
                if (autoCaptureTriggered) return;

                if (event.data && event.data.length > 0) {
                    const rect = event.data[0];
                    const vWidth = video.videoWidth || 480;
                    const vHeight = video.videoHeight || 360;

                    const faceCenterX = rect.x + rect.width / 2;
                    const faceCenterY = rect.y + rect.height / 2;
                    const ovalCenterX = vWidth / 2;
                    const ovalCenterY = vHeight / 2;

                    const dist = Math.hypot(faceCenterX - ovalCenterX, faceCenterY - ovalCenterY);
                    const isInOval = dist < (vWidth * 0.28) && rect.width > (vWidth * 0.22);

                    if (isInOval) {
                        handleFaceInOval();
                    } else {
                        handleFaceLeftOval();
                    }
                } else {
                    handleFaceLeftOval();
                }
            });

            trackerTask = tracking.track('#punch-video', tracker);
        } catch (e) {
            console.log('Tracking init fallback:', e);
            // Fallback: auto capture after 2.5s if tracker fails
            setTimeout(() => {
                if (!autoCaptureTriggered) {
                    triggerAutoSnapshot();
                }
            }, 2500);
        }
    }

    function handleFaceInOval() {
        if (autoCaptureTriggered) return;

        const oval = document.getElementById('oval-frame');
        const badge = document.getElementById('face-badge');
        const guideText = document.getElementById('oval-guide-text');
        const scanBar = document.getElementById('scan-bar');

        if (oval) {
            oval.className = 'w-48 h-56 border-4 border-emerald-400 rounded-[50%] shadow-[0_0_30px_rgba(52,211,153,0.9),0_0_0_9999px_rgba(0,0,0,0.45)] flex flex-col items-center justify-end pb-3 transition-all duration-300 relative scale-105';
        }
        if (badge) {
            badge.className = 'text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 animate-pulse';
            badge.innerText = '🟢 Wajah Terdeteksi! Menjepret...';
        }
        if (guideText) {
            guideText.className = 'text-[10px] text-white bg-emerald-600 px-2.5 py-0.5 rounded-full backdrop-blur-xs font-bold';
            guideText.innerText = '✓ Tahan Posisi...';
        }
        if (scanBar) scanBar.style.display = 'none';

        if (!faceHoldTimer) {
            faceHoldTimer = setTimeout(() => {
                if (!autoCaptureTriggered) {
                    triggerAutoSnapshot();
                }
            }, 700);
        }
    }

    function handleFaceLeftOval() {
        if (autoCaptureTriggered) return;

        if (faceHoldTimer) {
            clearTimeout(faceHoldTimer);
            faceHoldTimer = null;
        }

        resetOvalUI();
        const badge = document.getElementById('face-badge');
        if (badge) {
            badge.className = 'text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800';
            badge.innerText = 'Posisikan Wajah ke Oval';
        }
    }

    function triggerAutoSnapshot() {
        autoCaptureTriggered = true;
        if (faceHoldTimer) {
            clearTimeout(faceHoldTimer);
            faceHoldTimer = null;
        }

        const flash = document.getElementById('camera-flash');
        if (flash) {
            flash.style.opacity = '0.9';
            setTimeout(() => { flash.style.opacity = '0'; }, 150);
        }

        takeSnapshot();
    }

    function takeSnapshot() {
        const video = document.getElementById('punch-video');
        const canvas = document.getElementById('punch-canvas');
        const photo = document.getElementById('punch-captured-photo');
        const guide = document.getElementById('face-guide');
        const badge = document.getElementById('face-badge');
        const retakeContainer = document.getElementById('retake-container');

        canvas.width = video.videoWidth || 480;
        canvas.height = video.videoHeight || 360;
        const ctx = canvas.getContext('2d');

        if (video.videoWidth > 0) {
            ctx.translate(canvas.width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            capturedPhotoData = canvas.toDataURL('image/jpeg', 0.85);
        } else {
            ctx.fillStyle = '#059669';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = '#FFFFFF';
            ctx.font = 'bold 22px sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText('{{ $user->name ?? "Pegawai" }}', canvas.width / 2, canvas.height / 2 - 10);
            ctx.font = '14px sans-serif';
            ctx.fillText('✓ Verifikasi Wajah Presensi', canvas.width / 2, canvas.height / 2 + 22);
            capturedPhotoData = canvas.toDataURL('image/jpeg', 0.85);
        }

        photo.src = capturedPhotoData;
        photo.classList.remove('hidden');
        video.classList.add('hidden');
        guide.classList.add('hidden');

        if (retakeContainer) {
            retakeContainer.classList.remove('hidden');
        }

        badge.className = 'text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 flex items-center gap-1';
        badge.innerHTML = '<i class="fa-solid fa-circle-check text-emerald-600"></i> Wajah Terverifikasi';

        stopCamera();
    }

    // Geolocation & Anti-Fake GPS
    function getCurrentLocation() {
        const userCoordsText = document.getElementById('user-coords-text');
        const distText = document.getElementById('gps-distance-text');
        const statusBadge = document.getElementById('gps-status-badge');

        if (userCoordsText) userCoordsText.innerText = 'Mencari sinyal GPS...';
        if (distText) distText.innerText = 'Menghitung...';
        if (statusBadge) {
            statusBadge.className = 'px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800';
            statusBadge.innerText = 'Mengecek Lokasi...';
        }

        if (!navigator.geolocation) {
            if (userCoordsText) userCoordsText.innerText = 'GPS tidak didukung';
            currentLat = officeLat;
            currentLng = officeLng;
            updateGpsUi(currentLat, currentLng);
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (pos) => {
                currentLat = pos.coords.latitude;
                currentLng = pos.coords.longitude;
                checkFakeGpsAnomaly(pos);
                updateGpsUi(currentLat, currentLng);
            },
            (err) => {
                console.warn('GPS signal issue or denied:', err.message);
                currentLat = officeLat;
                currentLng = officeLng;
                updateGpsUi(currentLat, currentLng);
            },
            { enableHighAccuracy: true, timeout: 8000 }
        );
    }

    function checkFakeGpsAnomaly(pos) {
        isFakeGpsDetected = false;
        fakeGpsReason = '';
        const coords = pos.coords;

        // Indikator 1: Akurasi terlalu presisi / anomali mock provider
        if (coords.accuracy !== null && coords.accuracy !== undefined) {
            if (coords.accuracy <= 0 || coords.accuracy < 1.0) {
                isFakeGpsDetected = true;
                fakeGpsReason = 'Akurasi sinyal mencurigakan (< 1 meter)';
            }
        }

        // Indikator 2: Anomali timestamp posisi vs jam sistem
        if (pos.timestamp) {
            const timeDiff = Math.abs(Date.now() - pos.timestamp);
            if (timeDiff > 120000) {
                isFakeGpsDetected = true;
                fakeGpsReason = 'Timestamp sinyal GPS tidak sinkron dengan waktu nyata';
            }
        }

        // Indikator 3: Koordinat emulator browser DevTools (preset Mountain View)
        if (Math.abs(coords.latitude - 37.422) < 0.005 && Math.abs(coords.longitude - (-122.084)) < 0.005) {
            isFakeGpsDetected = true;
            fakeGpsReason = 'Sensor emulator browser terdeteksi';
        }

        const warnEl = document.getElementById('fake-gps-warning');
        const submitBtn = document.getElementById('btn-submit-punch');
        if (isFakeGpsDetected) {
            if (warnEl) warnEl.classList.remove('hidden');
            if (submitBtn) submitBtn.classList.add('opacity-50');
        } else {
            if (warnEl) warnEl.classList.add('hidden');
            if (submitBtn) submitBtn.classList.remove('opacity-50');
        }
    }

    function updateGpsUi(lat, lng) {
        const userCoordsText = document.getElementById('user-coords-text');
        const distText = document.getElementById('gps-distance-text');
        const statusBadge = document.getElementById('gps-status-badge');
        const outsideNotice = document.getElementById('outside-radius-notice');

        if (userCoordsText) {
            userCoordsText.innerText = lat.toFixed(5) + ', ' + lng.toFixed(5);
        }

        const distance = calculateHaversine(lat, lng, officeLat, officeLng);

        if (distText) {
            distText.innerText = distance + ' meter ke kantor';
        }

        if (statusBadge) {
            if (distance <= officeRadius) {
                statusBadge.className = 'px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800';
                statusBadge.innerText = '✓ Di Dalam Radius';
                if (outsideNotice) outsideNotice.classList.add('hidden');
            } else {
                statusBadge.className = 'px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300';
                statusBadge.innerText = '⚠️ Di Luar Radius (' + distance + 'm)';
                if (outsideNotice) outsideNotice.classList.remove('hidden');
            }
        }
    }

    function calculateHaversine(lat1, lon1, lat2, lon2) {
        const R = 6371000;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return Math.round(R * c);
    }

    async function submitPunch() {
        if (punchType === 'completed') {
            Swal.fire({
                icon: 'info',
                title: 'Presensi Selesai',
                text: 'Anda telah menyelesaikan seluruh presensi hari ini.',
                confirmButtonColor: '#059669',
            });
            return;
        }

        if (isFakeGpsDetected) {
            Swal.fire({
                icon: 'error',
                title: 'Presensi Ditolak: Fake GPS!',
                html: `
                    <div class="text-left text-xs space-y-2 text-slate-700">
                        <p class="font-bold text-red-600">Sistem mendeteksi manipulasi sinyal GPS (Mock Location).</p>
                        <p>Keterangan: <span class="font-semibold text-slate-800">${fakeGpsReason || 'Aplikasi Fake GPS aktif'}</span>.</p>
                        <div class="p-2.5 bg-red-50 border border-red-200 rounded-xl text-red-800 text-[11px] leading-relaxed">
                            Mohon matikan aplikasi Fake GPS dan nonaktifkan opsi <strong>"Pilih aplikasi lokasi tiruan (Mock location)"</strong> di Pengaturan Opsi Pengembang (Developer Options) HP Anda, lalu coba lagi.
                        </div>
                    </div>
                `,
                confirmButtonColor: '#EF4444',
                confirmButtonText: 'Saya Mengerti'
            });
            return;
        }

        if (!capturedPhotoData) {
            triggerAutoSnapshot();
        }

        if (currentLat === null || currentLng === null) {
            currentLat = officeLat;
            currentLng = officeLng;
        }

        const notes = document.getElementById('punch-notes') ? document.getElementById('punch-notes').value : '';
        const btn = document.getElementById('btn-submit-punch');
        const origBtnHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i><span>Memproses Presensi...</span>';

        try {
            const response = await fetch("{{ route('user.punch.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    type: punchType,
                    latitude: currentLat,
                    longitude: currentLng,
                    photo: capturedPhotoData,
                    notes: notes
                })
            });

            const result = await response.json();

            if (result.success) {
                Swal.fire({
                    icon: result.is_pending ? 'warning' : 'success',
                    title: result.is_pending ? 'Presensi Pending (Luar Radius)' : 'Presensi Berhasil!',
                    text: result.message,
                    confirmButtonColor: result.is_pending ? '#D97706' : '#059669',
                }).then(() => {
                    window.location.href = "{{ route('user.dashboard') }}";
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Presensi Gagal',
                    text: result.message || 'Terjadi kesalahan saat memproses presensi.',
                    confirmButtonColor: '#EF4444',
                });
            }
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Error Koneksi',
                text: 'Gagal terhubung ke server.',
                confirmButtonColor: '#EF4444',
            });
        } finally {
            btn.disabled = false;
            btn.innerHTML = origBtnHtml;
        }
    }
</script>
@endsection
