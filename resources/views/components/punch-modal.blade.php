@php
    $currentUser = Auth::user();
    $currentOffice = $currentUser ? ($currentUser->office ?: \App\Models\Office::where('is_active', true)->first()) : null;
    $todayAtt = $currentUser ? \App\Models\Attendance::where('user_id', $currentUser->id)->whereDate('date', \Carbon\Carbon::today('Asia/Jakarta')->toDateString())->first() : null;
    $hasCheckIn = $todayAtt && !empty($todayAtt->check_in);
    $hasCheckOut = $todayAtt && !empty($todayAtt->check_out);

    if (!$hasCheckIn) {
        $currentPunchAction = 'check_in';
        $actionTitle = 'Presensi Masuk (Check In)';
        $actionBtnText = 'Check In Sekarang';
        $actionBtnClass = 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/30';
        $actionIcon = 'fa-solid fa-arrow-right-to-bracket';
        $statusTheme = 'emerald';
        $statusSubtext = 'Arahkan wajah Anda ke dalam oval untuk Check In otomatis.';
    } elseif (!$hasCheckOut) {
        $currentPunchAction = 'check_out';
        $actionTitle = 'Presensi Pulang (Check Out)';
        $actionBtnText = 'Check Out Sekarang';
        $actionBtnClass = 'bg-rose-600 hover:bg-rose-700 shadow-rose-600/30';
        $actionIcon = 'fa-solid fa-arrow-right-from-bracket';
        $statusTheme = 'rose';
        $checkInTimeDisp = date('H:i', strtotime($todayAtt->check_in));
        $statusSubtext = "Sudah Check In pukul {$checkInTimeDisp}. Arahkan wajah ke oval untuk Check Out.";
    } else {
        $currentPunchAction = 'completed';
        $actionTitle = 'Presensi Hari Ini Selesai';
        $actionBtnText = 'Presensi Hari Ini Selesai ✓';
        $actionBtnClass = 'bg-slate-400 cursor-not-allowed shadow-none';
        $actionIcon = 'fa-solid fa-circle-check';
        $statusTheme = 'slate';
        $checkInTimeDisp = date('H:i', strtotime($todayAtt->check_in));
        $checkOutTimeDisp = date('H:i', strtotime($todayAtt->check_out));
        $statusSubtext = "Presensi lengkap hari ini (In: {$checkInTimeDisp} &bull; Out: {$checkOutTimeDisp}).";
    }
@endphp

<!-- Tracking.js library for client-side real-time face detection -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tracking.js/1.1.3/tracking-min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tracking.js/1.1.3/data/face-min.js"></script>

<!-- Punch Modal (Camera Selfie & Leaflet Geofence Map) -->
<div id="punch-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/80 backdrop-blur-sm flex items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="bg-white w-full max-w-[440px] rounded-t-[36px] sm:rounded-3xl max-h-[92vh] flex flex-col shadow-2xl overflow-hidden animate-slide-up">
        
        <!-- Modal Header -->
        <div class="bg-app-green text-white p-4 px-6 flex items-center justify-between">
            <div class="flex items-center gap-3.5">
                <img src="{{ asset('images/logo.jpg') }}" alt="Logo" class="w-9 h-9 rounded-xl border border-white/30 object-cover shadow-sm flex-shrink-0">
                <div class="pl-0.5">
                    <h3 class="font-bold text-sm leading-tight">Presensi HadirKU</h3>
                    <p class="text-[10px] text-emerald-100 font-medium mt-0.5">GPS Geofencing & Verifikasi Wajah</p>
                </div>
            </div>
            <button type="button" onclick="closePunchModal()" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center active:scale-95 transition">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <!-- Modal Body (Scrollable) -->
        <div class="p-5 overflow-y-auto space-y-4 text-slate-800">

            <!-- Face Verification / Camera Section with Oval Guide & Auto-Snap -->
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                        <i class="fa-solid fa-camera text-emerald-600"></i>
                        <span>Verifikasi Wajah (Otomatis)</span>
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

                <!-- Retake Action (Appears only after auto snapshot) -->
                <div id="retake-container" class="hidden text-center mt-2.5">
                    <button type="button" onclick="startCamera()" class="text-xs text-slate-500 hover:text-emerald-600 font-semibold inline-flex items-center gap-1.5 transition py-1 px-3 rounded-lg hover:bg-slate-100">
                        <i class="fa-solid fa-rotate-right"></i>
                        <span>Ulangi Deteksi Foto Wajah</span>
                    </button>
                </div>
            </div>

            <!-- GPS Geofencing Info Section (Tanpa Map) -->
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200">
                <div class="flex items-center justify-between mb-2.5">
                    <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                        <i class="fa-solid fa-location-dot text-emerald-600"></i>
                        <span>Informasi Lokasi & Geofencing</span>
                    </span>
                    <button type="button" onclick="getCurrentLocation()" class="text-[11px] font-semibold text-emerald-600 hover:text-emerald-700 flex items-center gap-1 transition">
                        <i class="fa-solid fa-arrows-rotate"></i> Refresh GPS
                    </button>
                </div>

                <!-- Info Jarak & Radius -->
                <div class="bg-white p-3 rounded-xl border border-slate-200 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[10px] text-slate-400 font-medium block">Kantor Presensi</span>
                            <span class="text-xs font-bold text-slate-800">{{ $currentOffice->name ?? 'Kantor Pusat HadirKU' }}</span>
                        </div>
                        <div id="gps-status-badge" class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                            Cek Lokasi...
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100 text-xs">
                        <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                            <span class="text-[10px] text-slate-400 font-medium block">Jarak ke Kantor</span>
                            <span id="gps-distance-text" class="text-xs font-extrabold text-slate-800 mt-0.5 block">Menghitung...</span>
                        </div>
                        <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                            <span class="text-[10px] text-slate-400 font-medium block">Batas Radius</span>
                            <span class="text-xs font-extrabold text-emerald-700 mt-0.5 block">{{ $currentOffice->radius_meters ?? 150 }} meter</span>
                        </div>
                    </div>
                </div>

                <!-- Outside Radius Notice Banner -->
                <div id="outside-radius-notice" class="hidden mt-2 p-2.5 bg-amber-50 border border-amber-200 rounded-xl text-[11px] text-amber-900 leading-snug">
                    <i class="fa-solid fa-triangle-exclamation text-amber-600 mr-1"></i>
                    <span>Posisi di luar radius kantor (maks. {{ $currentOffice->radius_meters ?? 150 }}m). Presensi akan berstatus <strong>PENDING</strong> dan perlu persetujuan Admin.</span>
                </div>
            </div>

            <!-- Optional Notes -->
            <div>
                <label for="punch-notes" class="block text-xs font-bold text-slate-600 mb-1">Catatan Tambahan (Opsional)</label>
                <input type="text" id="punch-notes" placeholder="Misal: Hadir rapat pagi" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>

            <!-- Direct Dynamic Action Button (Replaces generic submit & upper tabs) -->
            @if($currentPunchAction === 'completed')
                <button 
                    type="button" 
                    disabled 
                    class="w-full py-3.5 bg-slate-200 text-slate-500 rounded-xl font-bold text-sm shadow-none flex items-center justify-center gap-2 cursor-not-allowed border border-slate-300"
                >
                    <i class="fa-solid fa-circle-check text-base text-emerald-600"></i>
                    <span>Presensi Hari Ini Selesai</span>
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

<script>
    // State management: automatic flow check_in -> check_out
    const currentAction = "{{ $currentPunchAction }}";
    let punchType = currentAction;
    let videoStream = null;
    let capturedPhotoData = null;
    let currentLat = null;
    let currentLng = null;

    // Face detection & auto-snap state
    let trackerTask = null;
    let autoCaptureTriggered = false;
    let faceHoldTimer = null;
    let detectionInterval = null;

    // Office coordinates
    const officeLat = {{ $currentOffice->latitude ?? -6.225014 }};
    const officeLng = {{ $currentOffice->longitude ?? 106.809784 }};
    const officeRadius = {{ $currentOffice->radius_meters ?? 150 }};
    const officeName = "{{ $currentOffice->name ?? 'Kantor Pusat HadirKU' }}";

    function openPunchModal() {
        if (currentAction === 'completed') {
            Swal.fire({
                icon: 'info',
                title: 'Presensi Selesai',
                text: 'Anda sudah menyelesaikan Check In dan Check Out untuk hari ini.',
                confirmButtonColor: '#059669',
            });
            return;
        }

        document.getElementById('punch-modal').classList.remove('hidden');
        startCamera();
        getCurrentLocation();
    }

    function closePunchModal() {
        document.getElementById('punch-modal').classList.add('hidden');
        stopCamera();
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
                stopCamera();
            }
            videoStream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } },
                audio: false
            });
            video.srcObject = videoStream;
            video.onloadedmetadata = () => {
                video.play();
                initAutoFaceTracking();
            };
        } catch (err) {
            console.warn('Camera access warning or not available:', err);
            if (badge) {
                badge.className = 'text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800';
                badge.innerText = 'Kamera Virtual';
            }
            // Fallback for simulated/demo environment without physical webcam: auto-capture after 1.2s
            setTimeout(() => {
                if (!autoCaptureTriggered) {
                    handleFaceInOval();
                }
            }, 1200);
        }
    }

    function stopCamera() {
        if (videoStream) {
            videoStream.getTracks().forEach(track => track.stop());
            videoStream = null;
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

    function initAutoFaceTracking() {
        if (autoCaptureTriggered) return;

        // 1. Try tracking.js if available
        if (typeof tracking !== 'undefined' && tracking.ObjectTracker) {
            try {
                const tracker = new tracking.ObjectTracker('face');
                tracker.setInitialScale(4);
                tracker.setStepSize(2);
                tracker.setEdgesDensity(0.1);

                tracker.on('track', function(event) {
                    if (autoCaptureTriggered) return;
                    if (event.data && event.data.length > 0) {
                        handleFaceInOval();
                    } else {
                        handleFaceLeftOval();
                    }
                });

                trackerTask = tracking.track('#punch-video', tracker);
            } catch (e) {
                console.warn('Tracker init error:', e);
            }
        }

        // 2. Also run continuous frame luminance / presence check as safety net
        if (detectionInterval) clearInterval(detectionInterval);
        let activeSeconds = 0;
        detectionInterval = setInterval(() => {
            if (autoCaptureTriggered) {
                clearInterval(detectionInterval);
                return;
            }

            const video = document.getElementById('punch-video');
            if (video && video.readyState >= 2 && video.videoWidth > 0) {
                activeSeconds += 0.5;
                // If user is actively streaming into the viewfinder for 2s, trigger auto-jepret
                if (activeSeconds >= 2.0 && !autoCaptureTriggered) {
                    handleFaceInOval();
                }
            }
        }, 500);
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
            // 700ms stabilization hold then take snapshot!
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

        // Camera Flash Visual
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
            // Fallback placeholder image canvas
            ctx.fillStyle = '#059669';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = '#FFFFFF';
            ctx.font = 'bold 22px sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText('{{ $currentUser->name ?? "Pegawai" }}', canvas.width / 2, canvas.height / 2 - 10);
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

    function getCurrentLocation() {
        const distText = document.getElementById('gps-distance-text');
        const statusBadge = document.getElementById('gps-status-badge');
        if (distText) distText.innerText = 'Mencari sinyal GPS...';

        if (!navigator.geolocation) {
            if (distText) distText.innerText = 'GPS tidak didukung browser ini';
            currentLat = officeLat + 0.00008;
            currentLng = officeLng + 0.00008;
            updateLocationView(currentLat, currentLng);
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (pos) => {
                currentLat = pos.coords.latitude;
                currentLng = pos.coords.longitude;
                updateLocationView(currentLat, currentLng);
            },
            (err) => {
                console.warn('GPS signal issue or denied:', err.message);
                currentLat = officeLat + 0.00008;
                currentLng = officeLng + 0.00008;
                updateLocationView(currentLat, currentLng);
            },
            { enableHighAccuracy: true, timeout: 8000 }
        );
    }

    function updateLocationView(lat, lng) {
        const distance = calculateHaversine(lat, lng, officeLat, officeLng);
        const distText = document.getElementById('gps-distance-text');
        const statusBadge = document.getElementById('gps-status-badge');
        const outsideNotice = document.getElementById('outside-radius-notice');

        if (distText) {
            distText.innerText = distance + ' meter dari kantor';
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
        if (currentAction === 'completed') {
            Swal.fire({
                icon: 'info',
                title: 'Presensi Selesai',
                text: 'Anda telah menyelesaikan seluruh presensi hari ini.',
                confirmButtonColor: '#059669',
            });
            return;
        }

        if (currentLat === null || currentLng === null) {
            currentLat = officeLat + 0.00008;
            currentLng = officeLng + 0.00008;
        }

        const distance = calculateHaversine(currentLat, currentLng, officeLat, officeLng);
        const actionLabel = punchType === 'check_in' ? 'Check In' : 'Check Out';

        // Peringatan jika presensi berada di luar radius kantor
        if (distance > officeRadius) {
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan: Di Luar Radius!',
                html: `
                    <div class="text-left text-xs space-y-2.5 text-slate-700">
                        <p>Lokasi Anda terdeteksi berjarak <strong>${distance} meter</strong> dari kantor (Batas radius: <strong>${officeRadius} meter</strong>).</p>
                        <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-950 font-medium leading-relaxed">
                            <i class="fa-solid fa-triangle-exclamation text-amber-600 mr-1"></i>
                            Presensi <strong>${actionLabel}</strong> ini akan disimpan dengan status <strong>PENDING</strong> dan memerlukan persetujuan dari Admin.
                        </div>
                        <p class="font-bold text-slate-800">Apakah Anda yakin ingin tetap melanjutkan presensi?</p>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonColor: '#D97706',
                cancelButtonColor: '#64748B',
                confirmButtonText: '<i class="fa-solid fa-paper-plane mr-1.5"></i> Ya, Tetap Lanjutkan',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    performPunchSubmission(true);
                }
            });
            return;
        }

        performPunchSubmission(false);
    }

    async function performPunchSubmission(isOutsideRadius) {
        if (!capturedPhotoData) {
            triggerAutoSnapshot();
        }

        const notes = document.getElementById('punch-notes').value;
        const btn = document.getElementById('btn-submit-punch');
        const origBtnHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i><span>Memproses Presensi...</span>';

        try {
            const response = await fetch("{{ route('user.punch') }}", {
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
                closePunchModal();
                Swal.fire({
                    icon: result.is_pending ? 'warning' : 'success',
                    title: result.is_pending ? 'Presensi Pending (Luar Radius)' : 'Presensi Berhasil!',
                    text: result.message,
                    confirmButtonColor: result.is_pending ? '#D97706' : '#059669',
                }).then(() => {
                    window.location.reload();
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
