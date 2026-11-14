<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\Office;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    /**
     * User Mobile Dashboard (Matches UI Screen 1)
     */
    public function index()
    {
        $user = Auth::user()->load(['department', 'shift', 'office']);
        $today = Carbon::today('Asia/Jakarta');

        // Greeting
        $hour = Carbon::now('Asia/Jakarta')->hour;
        if ($hour < 11) {
            $greeting = 'Selamat Pagi';
        } elseif ($hour < 15) {
            $greeting = 'Selamat Siang';
        } elseif ($hour < 18) {
            $greeting = 'Selamat Sore';
        } else {
            $greeting = 'Selamat Malam';
        }

        // Today's Attendance
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today->toDateString())
            ->first();

        // Calculate active working hours & percentage
        $workingHoursFormatted = '00:00';
        $workingProgressPercent = 0;
        $punchInTime = '--:-- --';
        $punchOutTime = '--:-- --';
        $breakDurationFormatted = '00:00';

        if ($todayAttendance) {
            if ($todayAttendance->check_in) {
                $punchInTime = Carbon::parse($todayAttendance->check_in)->format('h:i A');
                
                // Recalculate working minutes live
                $start = Carbon::parse($todayAttendance->check_in);
                $end = $todayAttendance->check_out ? Carbon::parse($todayAttendance->check_out) : Carbon::now('Asia/Jakarta');
                $totalMins = max(0, $start->diffInMinutes($end));
                $workingMins = max(0, $totalMins - ($todayAttendance->break_minutes ?: 0));
                
                $hours = floor($workingMins / 60);
                $mins = $workingMins % 60;
                $workingHoursFormatted = sprintf('%02d:%02d', $hours, $mins);
                $workingProgressPercent = min(100, round(($workingMins / 480) * 100)); // 480 mins = 8 hrs
            }

            if ($todayAttendance->check_out) {
                $punchOutTime = Carbon::parse($todayAttendance->check_out)->format('h:i A');
            }

            if ($todayAttendance->break_minutes) {
                $bHours = floor($todayAttendance->break_minutes / 60);
                $bMins = $todayAttendance->break_minutes % 60;
                $breakDurationFormatted = sprintf('%02d:%02d', $bHours, $bMins);
            }
        }

        // Attendance Overview (Current Week: Mon - Sat)
        $startOfWeek = $today->copy()->startOfWeek(Carbon::MONDAY);
        $dayNamesIndo = [
            'Mon' => 'Sen',
            'Tue' => 'Sel',
            'Wed' => 'Rab',
            'Thu' => 'Kam',
            'Fri' => 'Jum',
            'Sat' => 'Sab',
            'Sun' => 'Min',
        ];
        $weekDays = [];
        for ($i = 0; $i < 6; $i++) {
            $dayDate = $startOfWeek->copy()->addDays($i);
            $dayAtt = Attendance::where('user_id', $user->id)
                ->whereDate('date', $dayDate->toDateString())
                ->first();

            $weekDays[] = [
                'day_name' => $dayNamesIndo[$dayDate->format('D')] ?? $dayDate->format('D'),
                'day_number' => $dayDate->format('d'),
                'date_str' => $dayDate->toDateString(),
                'is_today' => $dayDate->isSameDay($today),
                'has_checked_in' => $dayAtt && $dayAtt->check_in,
                'check_in_time' => $dayAtt && $dayAtt->check_in ? Carbon::parse($dayAtt->check_in)->format('H:i') : '--:--',
                'status' => $dayAtt ? $dayAtt->status : 'absent',
            ];
        }

        // This Month Summary
        $startOfMonth = $today->copy()->startOfMonth();
        $endOfMonth = $today->copy()->endOfMonth();
        $monthAttendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get();

        $presentCount = $monthAttendances->whereNotNull('check_in')->where('check_in_status', 'on_time')->count();
        $lateCount = $monthAttendances->where('check_in_status', 'late')->count();
        $absentCount = 0; // can be computed against working days passed
        
        // Count working days passed without attendance (berdasarkan jadwal hari kerja pegawai & hari libur)
        $holidayDates = Holiday::getActiveDatesBetween($startOfMonth, $endOfMonth);
        $daysPassed = min($today->day, $startOfMonth->daysInMonth);
        $totalWorkDaysSoFar = 0;
        for ($d = 1; $d <= $daysPassed; $d++) {
            $chkDate = $startOfMonth->copy()->setDay($d);
            if ($user->isWorkingDay($chkDate) && ! in_array($chkDate->toDateString(), $holidayDates, true)) {
                $totalWorkDaysSoFar++;
            }
        }
        $totalAttended = $monthAttendances->whereNotNull('check_in')->count();
        $absentCount = max(0, $totalWorkDaysSoFar - $totalAttended);

        $totalMonthMinutes = $monthAttendances->sum('working_minutes');
        $monthHours = floor($totalMonthMinutes / 60);
        $monthMins = $totalMonthMinutes % 60;
        $totalMonthHoursFormatted = sprintf('%d:%02d', $monthHours, $monthMins);

        // Recent Activities (Today's logs or latest 4 logs) - Tanpa Break Start dan Break End
        $recentActivities = AttendanceLog::where('user_id', $user->id)
            ->whereNotIn('type', ['break_start', 'break_end'])
            ->whereDate('date', $today->toDateString())
            ->orderBy('time', 'asc')
            ->get();

        if ($recentActivities->isEmpty()) {
            $recentActivities = AttendanceLog::where('user_id', $user->id)
                ->whereNotIn('type', ['break_start', 'break_end'])
                ->orderBy('date', 'desc')
                ->orderBy('time', 'desc')
                ->take(4)
                ->get();
        }

        // Active Office for Geofencing
        $office = $user->office ?: Office::where('is_active', true)->first();

        // Status hari libur & hari kerja pegawai hari ini
        $todayHoliday = Holiday::getHolidayForDate($today);
        $isWorkingDayToday = $user->isWorkingDay($today);

        return view('user.dashboard', compact(
            'user',
            'greeting',
            'today',
            'todayAttendance',
            'punchInTime',
            'punchOutTime',
            'workingHoursFormatted',
            'workingProgressPercent',
            'breakDurationFormatted',
            'weekDays',
            'presentCount',
            'absentCount',
            'lateCount',
            'totalMonthHoursFormatted',
            'recentActivities',
            'office',
            'todayHoliday',
            'isWorkingDayToday'
        ));
    }

    /**
     * Attendance History Screen (Matches UI Screen 2)
     */
    public function history(Request $request)
    {
        $user = Auth::user();
        $month = $request->input('month', Carbon::now('Asia/Jakarta')->month);
        $year = $request->input('year', Carbon::now('Asia/Jakarta')->year);

        $currentMonthDate = Carbon::createFromDate($year, $month, 1, 'Asia/Jakarta');
        $selectedDate = $request->input('date', Carbon::today('Asia/Jakarta')->toDateString());
        $selectedCarbon = Carbon::parse($selectedDate, 'Asia/Jakarta');

        // Attendances in this month
        $attendances = Attendance::where('user_id', $user->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get()
            ->keyBy(fn ($item) => $item->date->format('Y-m-d'));

        // Monthly Summary
        $presentCount = $attendances->whereNotNull('check_in')->where('check_in_status', 'on_time')->count();
        $lateCount = $attendances->where('check_in_status', 'late')->count();
        $totalMinutes = $attendances->sum('working_minutes');
        $totalHours = sprintf('%d:%02d', floor($totalMinutes / 60), $totalMinutes % 60);

        // Days in Month for calendar picker
        $holidayDates = Holiday::getActiveDatesBetween(
            $currentMonthDate->copy()->startOfMonth(),
            $currentMonthDate->copy()->endOfMonth()
        );
        $calendarDays = [];
        $daysInMonth = $currentMonthDate->daysInMonth;
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $currentMonthDate->copy()->setDay($day);
            $dateStr = $date->toDateString();
            $att = $attendances->get($dateStr);
            $isHoliday = in_array($dateStr, $holidayDates, true);
            $isWorkDay = $user->isWorkingDay($date) && ! $isHoliday;

            $calendarDays[] = [
                'day_num' => $day,
                'day_name' => strtoupper($date->format('D')),
                'date_str' => $dateStr,
                'is_sunday' => $date->isSunday(),
                'is_holiday' => $isHoliday,
                'is_working_day' => $isWorkDay,
                'is_selected' => $dateStr === $selectedDate,
                'has_attendance' => (bool) $att,
                'status' => $att ? $att->check_in_status : ($date->isPast() && $isWorkDay ? 'absent' : ($isHoliday ? 'holiday' : 'upcoming')),
            ];
        }

        // Selected day attendance details and logs
        $selectedAttendance = $attendances->get($selectedDate);
        $selectedLogs = AttendanceLog::where('user_id', $user->id)
            ->whereNotIn('type', ['break_start', 'break_end'])
            ->whereDate('date', $selectedDate)
            ->orderBy('time', 'asc')
            ->get();

        return view('user.history', compact(
            'user',
            'currentMonthDate',
            'selectedCarbon',
            'selectedDate',
            'calendarDays',
            'selectedAttendance',
            'selectedLogs',
            'presentCount',
            'lateCount',
            'totalHours'
        ));
    }

    /**
     * User Mobile Punch Page (Standalone View)
     */
    public function punchPage()
    {
        $user = Auth::user()->load(['office', 'shift', 'department']);
        $office = $user->office ?: Office::where('is_active', true)->first();
        $today = Carbon::today('Asia/Jakarta');

        $todayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today->toDateString())
            ->first();

        $hasCheckIn = $todayAttendance && ! empty($todayAttendance->check_in);
        $hasCheckOut = $todayAttendance && ! empty($todayAttendance->check_out);

        if (! $hasCheckIn) {
            $punchType = 'check_in';
            $pageTitle = 'Presensi Masuk';
            $actionBtnText = 'Presensi Masuk Sekarang';
            $actionBtnClass = 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/30';
            $actionIcon = 'fa-solid fa-arrow-right-to-bracket';
            $statusSubtext = 'Posisikan wajah Anda pada lingkaran oval kamera untuk verifikasi.';
        } elseif (! $hasCheckOut) {
            $punchType = 'check_out';
            $pageTitle = 'Presensi Pulang';
            $actionBtnText = 'Presensi Pulang Sekarang';
            $actionBtnClass = 'bg-rose-600 hover:bg-rose-700 shadow-rose-600/30';
            $actionIcon = 'fa-solid fa-arrow-right-from-bracket';
            $checkInTimeDisp = date('H:i', strtotime($todayAttendance->check_in));
            $statusSubtext = "Sudah presensi masuk pukul {$checkInTimeDisp} WIB. Lakukan verifikasi wajah untuk presensi pulang.";
        } else {
            $punchType = 'completed';
            $pageTitle = 'Presensi Hari Ini Selesai';
            $actionBtnText = 'Presensi Hari Ini Selesai ✓';
            $actionBtnClass = 'bg-slate-400 cursor-not-allowed shadow-none';
            $actionIcon = 'fa-solid fa-circle-check';
            $checkInTimeDisp = date('H:i', strtotime($todayAttendance->check_in));
            $checkOutTimeDisp = date('H:i', strtotime($todayAttendance->check_out));
            $statusSubtext = "Presensi lengkap hari ini (Masuk: {$checkInTimeDisp} WIB &bull; Pulang: {$checkOutTimeDisp} WIB).";
        }

        $todayHoliday = Holiday::getHolidayForDate($today);
        $isWorkingDayToday = $user->isWorkingDay($today);

        return view('user.punch', compact(
            'user',
            'office',
            'today',
            'todayAttendance',
            'hasCheckIn',
            'hasCheckOut',
            'punchType',
            'pageTitle',
            'actionBtnText',
            'actionBtnClass',
            'actionIcon',
            'statusSubtext',
            'todayHoliday',
            'isWorkingDayToday'
        ));
    }

    /**
     * Submit Presensi GPS & Face Verification
     */
    public function punch(Request $request)
    {
        $request->validate([
            'type' => 'required|in:check_in,check_out',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'photo' => 'required|string|max:7000000', // Base64 image data URL (max ~5MB)
            'notes' => 'nullable|string',
        ]);

        $user = Auth::user()->load(['office', 'shift']);
        $office = $user->office ?: Office::where('is_active', true)->first();

        if (! $office) {
            return response()->json([
                'success' => false,
                'message' => 'Titik lokasi kantor belum dikonfigurasi oleh admin.',
            ], 422);
        }

        // Calculate distance using Haversine formula
        $distance = $this->calculateDistance(
            $request->latitude,
            $request->longitude,
            $office->latitude,
            $office->longitude
        );

        $allowedRadius = $office->radius_meters ?: 100;
        $isWithinRadius = $distance <= $allowedRadius;
        $isPendingApproval = ! $isWithinRadius;

        // Save Selfie Photo
        $photoPath = null;
        if ($request->photo && str_starts_with($request->photo, 'data:image')) {
            $imageData = preg_replace('#^data:image/\w+;base64,#i', '', $request->photo);
            $imageData = base64_decode($imageData);

            // Validasi byte gambar asli (bukan file berbahaya yang disamarkan)
            if (! $imageData || ! @getimagesizefromstring($imageData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Berkas foto tidak valid atau rusak.',
                ], 422);
            }

            // Gunakan string acak agar nama berkas tidak mudah ditebak
            $fileName = 'attendances/' . $user->id . '_' . \Illuminate\Support\Str::random(24) . '_' . $request->type . '.jpg';
            Storage::disk('public')->put($fileName, $imageData);
            $photoPath = $fileName;
        }

        $now = Carbon::now('Asia/Jakarta');

        if ($request->type === 'check_in') {
            $todayHoliday = Holiday::getHolidayForDate($now);
            if ($todayHoliday) {
                return response()->json([
                    'success' => false,
                    'message' => "Hari ini adalah Hari Libur ({$todayHoliday->name}). Presensi masuk tidak diperlukan.",
                ], 422);
            }

            if (! $user->isWorkingDay($now)) {
                return response()->json([
                    'success' => false,
                    'message' => "Hari ini bukan jadwal hari kerja Anda ({$user->working_days_summary}).",
                ], 422);
            }
        }

        $todayStr = $now->toDateString();
        $timeStr = $now->toTimeString();

        // Find or create today's primary attendance record
        $attendance = Attendance::firstOrCreate(
            [
                'user_id' => $user->id,
                'date' => $todayStr,
            ],
            [
                'office_id' => $office->id,
                'status' => $isPendingApproval ? 'pending' : 'present',
                'face_verified' => true,
            ]
        );

        $logStatus = $isPendingApproval ? 'Pending (Luar Radius)' : 'On time';
        $logDuration = null;

        switch ($request->type) {
            case 'check_in':
                if ($attendance->check_in) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda sudah melakukan Check In hari ini pada pukul ' . date('H:i', strtotime($attendance->check_in)),
                    ], 422);
                }

                // Check lateness against shift
                $shift = $user->shift;
                $isLate = false;
                if ($shift && $shift->start_time) {
                    $shiftStart = Carbon::parse($todayStr . ' ' . $shift->start_time, 'Asia/Jakarta');
                    $graceLimit = $shiftStart->copy()->addMinutes($shift->grace_period_minutes ?: 15);
                    if ($now->greaterThan($graceLimit)) {
                        $isLate = true;
                    }
                }

                $attendance->check_in = $timeStr;
                $attendance->check_in_lat = $request->latitude;
                $attendance->check_in_lng = $request->longitude;
                $attendance->check_in_distance = $distance;
                $attendance->check_in_photo = $photoPath;
                $attendance->check_in_status = $isLate ? 'late' : 'on_time';
                $attendance->status = $isPendingApproval ? 'pending' : ($isLate ? 'late' : 'present');
                $logStatus = $isPendingApproval ? 'Pending (Luar Radius)' : ($isLate ? 'Late' : 'On time');
                break;

            case 'break_start':
                if (! $attendance->check_in) {
                    return response()->json(['success' => false, 'message' => 'Silakan Check In terlebih dahulu sebelum mulai istirahat.'], 422);
                }
                $attendance->break_start = $timeStr;
                $logStatus = 'Break Started';
                break;

            case 'break_end':
                if (! $attendance->break_start) {
                    return response()->json(['success' => false, 'message' => 'Anda belum mencatat Mulai Istirahat.'], 422);
                }
                $attendance->break_end = $timeStr;
                if ($attendance->break_start) {
                    $diffMins = Carbon::parse($attendance->break_start)->diffInMinutes($now);
                    $attendance->break_minutes = $diffMins;
                    $logDuration = sprintf('%02d:%02d', floor($diffMins / 60), $diffMins % 60);
                }
                $logStatus = 'Break Ended';
                break;

            case 'check_out':
                if (! $attendance->check_in) {
                    return response()->json(['success' => false, 'message' => 'Silakan Check In terlebih dahulu sebelum Check Out.'], 422);
                }
                if ($attendance->check_out) {
                    return response()->json(['success' => false, 'message' => 'Anda sudah melakukan Check Out hari ini pada pukul ' . date('H:i', strtotime($attendance->check_out))], 422);
                }
                $attendance->check_out = $timeStr;
                $attendance->check_out_lat = $request->latitude;
                $attendance->check_out_lng = $request->longitude;
                $attendance->check_out_distance = $distance;
                $attendance->check_out_photo = $photoPath;
                $attendance->check_out_status = 'normal';
                
                if ($isPendingApproval) {
                    $attendance->status = 'pending';
                    $logStatus = 'Completed (Pending Approval)';
                } else {
                    if ($attendance->status !== 'pending') {
                        $attendance->status = 'present';
                    }
                    $logStatus = 'Completed';
                }
                break;
        }

        $attendance->notes = $request->notes ?: $attendance->notes;
        $attendance->save();
        $attendance->recalculateDurations();

        // Create log entry
        AttendanceLog::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'type' => $request->type,
            'date' => $todayStr,
            'time' => $timeStr,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'distance_meters' => $distance,
            'location_name' => $isPendingApproval ? 'Luar Radius (' . $distance . 'm)' : ($office->name ?: 'Office'),
            'photo_path' => $photoPath,
            'status' => $logStatus,
            'duration' => $logDuration,
            'notes' => $request->notes,
        ]);

        $actionLabel = $request->type === 'check_in' ? 'Check In' : 'Check Out';
        $message = $isPendingApproval
            ? "Presensi {$actionLabel} berhasil dicatat di luar radius ({$distance} meter)! Status saat ini PENDING dan sedang menunggu persetujuan Admin."
            : "Presensi {$actionLabel} berhasil dicatat!";

        return response()->json([
            'success' => true,
            'message' => $message,
            'is_pending' => $isPendingApproval,
            'type' => $request->type,
            'time' => date('h:i A', strtotime($timeStr)),
            'distance' => $distance,
        ]);
    }

    /**
     * Leaves & Reports Screen
     */
    public function leaves()
    {
        $user = Auth::user();
        $leaves = Leave::where('user_id', $user->id)->latest()->get();

        return view('user.leaves', compact('user', 'leaves'));
    }

    public function storeLeave(Request $request)
    {
        $request->validate([
            'type' => 'required|in:izin,sakit,cuti',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:4096',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('leaves', 'public');
        }

        Leave::create([
            'user_id' => Auth::id(),
            'type' => $request->type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'attachment' => $attachmentPath,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Pengajuan ' . strtoupper($request->type) . ' berhasil diajukan dan sedang menunggu persetujuan admin.');
    }

    /**
     * User Profile Screen
     */
    public function profile()
    {
        $user = Auth::user()->load(['department', 'shift', 'office']);
        return view('user.profile', compact('user'));
    }

    /**
     * Update User Profile Photo / Avatar
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ], [
            'avatar.required' => 'Silakan pilih file foto terlebih dahulu.',
            'avatar.image' => 'File harus berupa gambar.',
            'avatar.mimes' => 'Format foto harus berupa JPG, PNG, atau WEBP.',
            'avatar.max' => 'Ukuran foto maksimal 5 MB.',
        ]);

        $user = Auth::user();

        // Delete old avatar if exists in public storage
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Store new avatar
        $path = $request->file('avatar')->store('avatars', 'public');
        $user->avatar = $path;
        $user->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Foto profil berhasil diperbarui!',
                'avatar_url' => $user->avatar_url,
            ]);
        }

        return back()->with('success', 'Foto profil berhasil diperbarui!');
    }

    /**
     * Update User Password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        $user = Auth::user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Kata sandi saat ini yang Anda masukkan salah.'])->withInput();
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with('success', 'Kata sandi berhasil diperbarui! Silakan gunakan kata sandi baru saat login.');
    }

    /**
     * Register or Update Reference Face for Biometric Matching
     * (Alur persetujuan admin: status pending -> diverifikasi admin -> dikunci)
     */
    public function registerFace(Request $request)
    {
        $user = Auth::user();

        // Validasi izin registrasi oleh admin
        if (! $user->allow_face_registration) {
            return response()->json([
                'success' => false,
                'message' => 'Fitur registrasi wajah saat ini terkunci. Silakan ajukan permohonan ke Admin untuk membuka izin pendaftaran wajah.',
            ], 403);
        }

        $request->validate([
            'photo' => 'required|string|max:7000000',
        ], [
            'photo.required' => 'Foto wajah harus diambil terlebih dahulu.',
            'photo.max' => 'Ukuran foto terlalu besar (maksimal ~5MB).',
        ]);

        if (! str_starts_with($request->photo, 'data:image')) {
            return response()->json([
                'success' => false,
                'message' => 'Format foto wajah tidak valid.',
            ], 422);
        }

        $imageData = preg_replace('#^data:image/\w+;base64,#i', '', $request->photo);
        $imageData = base64_decode($imageData);

        if (! $imageData || ! @getimagesizefromstring($imageData)) {
            return response()->json([
                'success' => false,
                'message' => 'Berkas gambar wajah tidak valid atau rusak.',
            ], 422);
        }

        // Hapus pengajuan pending sebelumnya jika ada
        if ($user->pending_face_reference && Storage::disk('public')->exists($user->pending_face_reference)) {
            Storage::disk('public')->delete($user->pending_face_reference);
        }

        // Simpan foto pengajuan wajah ke disk public dengan nama acak
        $fileName = 'faces/' . $user->id . '_pending_' . \Illuminate\Support\Str::random(24) . '.jpg';
        Storage::disk('public')->put($fileName, $imageData);

        // Update status menjadi pending, dan KUNCI izin registrasi sampai admin memverifikasi
        $user->pending_face_reference = $fileName;
        $user->face_reference_status = 'pending';
        $user->face_rejection_reason = null;
        $user->allow_face_registration = false; // Registrasi otomatis terkunci
        $user->face_unlock_requested = false;
        $user->save();

        return response()->json([
            'success' => true,
            'status' => 'pending',
            'message' => 'Foto wajah berhasil dikirim dan saat ini Menunggu Persetujuan Admin. Admin akan memeriksa apakah akun tersebut benar wajah milik Anda sebelum disetujui.',
            'face_url' => asset('storage/' . $fileName),
        ]);
    }

    /**
     * Request Admin to Unlock Face Registration
     */
    public function requestFaceUnlock(Request $request)
    {
        $user = Auth::user();

        if ($user->face_reference_status === 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Pendaftaran wajah Anda masih dalam antrean verifikasi oleh Admin.',
            ], 422);
        }

        $user->face_unlock_requested = true;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Permintaan buka kunci pendaftaran wajah telah dikirimkan ke Admin.',
        ]);
    }

    /**
     * Haversine formula to compute distance in meters
     */
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): int
    {
        $earthRadius = 6371000; // in meters
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return (int) round($earthRadius * $c);
    }
}
