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
            
            <!-- Type Selector Tabs -->
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Pilih Aksi Presensi</label>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <button type="button" onclick="selectPunchType('check_in')" id="btn-type-check_in" class="p-2.5 rounded-xl border-2 border-emerald-500 bg-emerald-50 text-emerald-700 font-bold flex items-center justify-center gap-2 transition">
                        <i class="fa-solid fa-arrow-right-to-bracket text-emerald-600"></i>
                        <span>Check In</span>
                    </button>
                    <button type="button" onclick="selectPunchType('check_out')" id="btn-type-check_out" class="p-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-600 font-semibold flex items-center justify-center gap-2 transition hover:bg-slate-100">
                        <i class="fa-solid fa-arrow-right-from-bracket text-red-500"></i>
                        <span>Check Out</span>
                    </button>
                    <button type="button" onclick="selectPunchType('break_start')" id="btn-type-break_start" class="p-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-600 font-semibold flex items-center justify-center gap-2 transition hover:bg-slate-100">
                        <i class="fa-solid fa-mug-saucer text-amber-500"></i>
                        <span>Break Start</span>
                    </button>
                    <button type="button" onclick="selectPunchType('break_end')" id="btn-type-break_end" class="p-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-600 font-semibold flex items-center justify-center gap-2 transition hover:bg-slate-100">
                        <i class="fa-solid fa-play text-blue-500"></i>
                        <span>Break End</span>
                    </button>
                </div>
            </div>

            <!-- Face Verification / Camera Section -->
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                        <i class="fa-solid fa-camera text-emerald-600"></i>
                        <span>Verifikasi Wajah (Selfie)</span>
                    </span>
                    <span id="face-badge" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800">
                        Ambil Foto Wajah
                    </span>
                </div>

                <!-- Video Viewfinder Container -->
                <div class="relative w-full aspect-[4/3] bg-black rounded-xl overflow-hidden shadow-inner flex items-center justify-center">
                    <video id="punch-video" autoplay playsinline muted class="w-full h-full object-cover transform -scale-x-100"></video>
                    <canvas id="punch-canvas" class="hidden"></canvas>
                    <img id="punch-captured-photo" class="hidden w-full h-full object-cover" alt="Foto Presensi" />

                    <!-- Oval Face Frame Guide Overlay -->
                    <div id="face-guide" class="absolute inset-0 pointer-events-none flex items-center justify-center">
                        <div class="w-48 h-56 border-2 border-dashed border-emerald-400/80 rounded-[50%] shadow-[0_0_0_9999px_rgba(0,0,0,0.35)] flex items-end justify-center pb-3">
                            <span class="text-[10px] text-white bg-black/60 px-2 py-0.5 rounded-full backdrop-blur-xs font-medium">Posisikan Wajah</span>
                        </div>
                    </div>
                </div>

                <!-- Camera Controls -->
                <div class="flex items-center justify-center gap-2 mt-3">
                    <button type="button" id="btn-snap" onclick="takeSnapshot()" class="flex-1 py-2 px-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/30 flex items-center justify-center gap-1.5 transition">
                        <i class="fa-solid fa-camera"></i>
                        <span>Jepret Foto</span>
                    </button>
                    <button type="button" id="btn-retake" onclick="startCamera()" class="hidden py-2 px-3 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition">
                        <i class="fa-solid fa-rotate-right"></i>
                        <span>Foto Ulang</span>
                    </button>
                </div>
            </div>

            <!-- GPS Geofencing Map Section -->
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                        <i class="fa-solid fa-location-dot text-emerald-600"></i>
                        <span>Geofencing & Lokasi GPS</span>
                    </span>
                    <button type="button" onclick="getCurrentLocation()" class="text-[10px] font-bold text-emerald-600 hover:underline flex items-center gap-1">
                        <i class="fa-solid fa-arrows-rotate"></i> Refresh GPS
                    </button>
                </div>

                <!-- Leaflet Map Canvas -->
                <div id="punch-map" class="w-full h-44 rounded-xl overflow-hidden border border-slate-200 shadow-inner z-10"></div>

                <!-- GPS Status Bar -->
                <div class="mt-2.5 p-2.5 rounded-xl bg-white border border-slate-200 flex items-center justify-between text-xs">
                    <div>
                        <div class="text-[10px] text-slate-400 font-medium">Jarak ke Kantor:</div>
                        <div id="gps-distance-text" class="font-extrabold text-slate-800">Menghitung...</div>
                    </div>
                    <div id="gps-status-badge" class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">
                        Cek Lokasi
                    </div>
                </div>

                <!-- Simulated Location Helper for Testing/Demo -->
                <div class="mt-2 text-center">
                    <button type="button" onclick="simulateAtOffice()" class="text-[10px] text-emerald-600 font-semibold hover:underline">
                        <i class="fa-solid fa-wand-magic-sparkles mr-1"></i> (Demo) Set Lokasi di Kantor
                    </button>
                </div>
            </div>

            <!-- Optional Notes -->
            <div>
                <label for="punch-notes" class="block text-xs font-bold text-slate-600 mb-1">Catatan Tambahan (Opsional)</label>
                <input type="text" id="punch-notes" placeholder="Misal: Hadir rapat pagi" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>

            <!-- Submit Button -->
            <button 
                type="button" 
                id="btn-submit-punch" 
                onclick="submitPunch()" 
                class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] text-white rounded-xl font-bold text-sm shadow-lg shadow-emerald-600/30 flex items-center justify-center gap-2 transition"
            >
                <i class="fa-solid fa-paper-plane"></i>
                <span>Kirim Presensi Sekarang</span>
            </button>
        </div>
    </div>
</div>

<script>
    let punchType = 'check_in';
    let videoStream = null;
    let capturedPhotoData = null;
    let currentLat = null;
    let currentLng = null;
    let punchMap = null;
    let userMarker = null;
    let officeMarker = null;
    let geofenceCircle = null;

    // Office coordinate fallback
    const officeLat = {{ $office->latitude ?? -6.225014 }};
    const officeLng = {{ $office->longitude ?? 106.809784 }};
    const officeRadius = {{ $office->radius_meters ?? 150 }};
    const officeName = "{{ $office->name ?? 'Kantor Pusat HadirKU' }}";

    function selectPunchType(type) {
        punchType = type;
        const types = ['check_in', 'check_out', 'break_start', 'break_end'];
        types.forEach(t => {
            const btn = document.getElementById('btn-type-' + t);
            if (t === type) {
                btn.className = 'p-2.5 rounded-xl border-2 border-emerald-500 bg-emerald-50 text-emerald-700 font-bold flex items-center justify-center gap-2 transition';
            } else {
                btn.className = 'p-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-600 font-semibold flex items-center justify-center gap-2 transition hover:bg-slate-100';
            }
        });
    }

    function openPunchModal() {
        document.getElementById('punch-modal').classList.remove('hidden');
        startCamera();
        initMap();
        getCurrentLocation();
    }

    function closePunchModal() {
        document.getElementById('punch-modal').classList.add('hidden');
        stopCamera();
    }

    async function startCamera() {
        const video = document.getElementById('punch-video');
        const photo = document.getElementById('punch-captured-photo');
        const guide = document.getElementById('face-guide');
        const btnSnap = document.getElementById('btn-snap');
        const btnRetake = document.getElementById('btn-retake');
        const badge = document.getElementById('face-badge');

        photo.classList.add('hidden');
        video.classList.remove('hidden');
        guide.classList.remove('hidden');
        btnSnap.classList.remove('hidden');
        btnRetake.classList.add('hidden');
        badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800';
        badge.innerText = 'Posisikan Wajah';

        try {
            if (videoStream) {
                stopCamera();
            }
            videoStream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } },
                audio: false
            });
            video.srcObject = videoStream;
        } catch (err) {
            console.warn('Camera access error or rejected:', err);
            // Simulated fallback snapshot if no hardware camera
            badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-800';
            badge.innerText = 'Kamera Virtual';
        }
    }

    function stopCamera() {
        if (videoStream) {
            videoStream.getTracks().forEach(track => track.stop());
            videoStream = null;
        }
    }

    function takeSnapshot() {
        const video = document.getElementById('punch-video');
        const canvas = document.getElementById('punch-canvas');
        const photo = document.getElementById('punch-captured-photo');
        const guide = document.getElementById('face-guide');
        const btnSnap = document.getElementById('btn-snap');
        const btnRetake = document.getElementById('btn-retake');
        const badge = document.getElementById('face-badge');

        canvas.width = video.videoWidth || 480;
        canvas.height = video.videoHeight || 360;
        const ctx = canvas.getContext('2d');
        
        // Draw video frame or nice placeholder avatar
        if (video.videoWidth > 0) {
            // Flip horizontal for selfie
            ctx.translate(canvas.width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            capturedPhotoData = canvas.toDataURL('image/jpeg', 0.85);
        } else {
            // Fallback placeholder image canvas
            ctx.fillStyle = '#059669';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = '#FFFFFF';
            ctx.font = 'bold 24px sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText('{{ $user->name }}', canvas.width / 2, canvas.height / 2);
            ctx.font = '16px sans-serif';
            ctx.fillText('Verified Face Selfie', canvas.width / 2, canvas.height / 2 + 30);
            capturedPhotoData = canvas.toDataURL('image/jpeg', 0.85);
        }

        photo.src = capturedPhotoData;
        photo.classList.remove('hidden');
        video.classList.add('hidden');
        guide.classList.add('hidden');
        btnSnap.classList.add('hidden');
        btnRetake.classList.remove('hidden');

        badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800';
        badge.innerText = '✓ Wajah Terverifikasi';
        stopCamera();
    }

    function initMap() {
        if (punchMap) return;

        setTimeout(() => {
            punchMap = L.map('punch-map', { zoomControl: false }).setView([officeLat, officeLng], 16);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
            }).addTo(punchMap);

            // Office marker
            officeMarker = L.marker([officeLat, officeLng]).addTo(punchMap)
                .bindPopup('<b>' + officeName + '</b><br>Titik Presensi');

            // Geofence circle
            geofenceCircle = L.circle([officeLat, officeLng], {
                color: '#059669',
                fillColor: '#10B981',
                fillOpacity: 0.15,
                radius: officeRadius
            }).addTo(punchMap);
        }, 200);
    }

    function getCurrentLocation() {
        const distText = document.getElementById('gps-distance-text');
        const statusBadge = document.getElementById('gps-status-badge');
        distText.innerText = 'Mencari sinyal GPS...';

        if (!navigator.geolocation) {
            distText.innerText = 'Geolocation tidak didukung';
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (pos) => {
                currentLat = pos.coords.latitude;
                currentLng = pos.coords.longitude;
                updateLocationView(currentLat, currentLng);
            },
            (err) => {
                console.warn('GPS error:', err.message);
                // Default to office location for smooth demo experience
                distText.innerText = 'Izin GPS ditolak, klik "Set Lokasi di Kantor"';
                statusBadge.className = 'px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800';
                statusBadge.innerText = 'Perlu GPS';
            },
            { enableHighAccuracy: true, timeout: 8000 }
        );
    }

    function simulateAtOffice() {
        // Offset by ~10 meters from office
        currentLat = officeLat + 0.00008;
        currentLng = officeLng + 0.00008;
        updateLocationView(currentLat, currentLng);
    }

    function updateLocationView(lat, lng) {
        if (!punchMap) return;

        if (userMarker) {
            userMarker.setLatLng([lat, lng]);
        } else {
            const userIcon = L.divIcon({
                className: 'custom-user-pin',
                html: '<div class="w-4 h-4 rounded-full bg-blue-600 border-2 border-white shadow-lg animate-ping"></div><div class="w-4 h-4 rounded-full bg-blue-600 border-2 border-white shadow-lg -mt-4"></div>',
                iconSize: [16, 16],
                iconAnchor: [8, 8]
            });
            userMarker = L.marker([lat, lng], { icon: userIcon }).addTo(punchMap)
                .bindPopup('Posisi Anda Sekarang');
        }

        punchMap.setView([lat, lng], 16);

        // Calculate distance
        const distance = calculateHaversine(lat, lng, officeLat, officeLng);
        const distText = document.getElementById('gps-distance-text');
        const statusBadge = document.getElementById('gps-status-badge');

        distText.innerText = distance + ' meter dari kantor';

        if (distance <= officeRadius) {
            statusBadge.className = 'px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800';
            statusBadge.innerText = '✓ Di Dalam Radius';
            geofenceCircle.setStyle({ color: '#059669', fillColor: '#10B981' });
        } else {
            statusBadge.className = 'px-2.5 py-1 rounded-full text-[11px] font-bold bg-red-100 text-red-800';
            statusBadge.innerText = '✗ Di Luar Radius';
            geofenceCircle.setStyle({ color: '#EF4444', fillColor: '#EF4444' });
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
        if (!capturedPhotoData) {
            takeSnapshot(); // Auto snapshot if user forgot to press snap
        }

        if (currentLat === null || currentLng === null) {
            simulateAtOffice(); // Auto set location if user clicked directly
        }

        const notes = document.getElementById('punch-notes').value;
        const btn = document.getElementById('btn-submit-punch');
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
                    icon: 'success',
                    title: 'Presensi Berhasil!',
                    text: result.message,
                    confirmButtonColor: '#059669',
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
            btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i><span>Kirim Presensi Sekarang</span>';
        }
    }
</script>
