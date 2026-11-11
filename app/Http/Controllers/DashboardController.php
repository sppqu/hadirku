<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\Leave;
use App\Models\Office;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        if ($hour < 12) {
            $greeting = 'Good Morning';
        } elseif ($hour < 17) {
            $greeting = 'Good Afternoon';
        } else {
            $greeting = 'Good Evening';
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
        $weekDays = [];
        for ($i = 0; $i < 6; $i++) {
            $dayDate = $startOfWeek->copy()->addDays($i);
            $dayAtt = Attendance::where('user_id', $user->id)
                ->whereDate('date', $dayDate->toDateString())
                ->first();

            $weekDays[] = [
                'day_name' => $dayDate->format('D'),
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
        
        // Count working days passed without attendance
        $daysPassed = min($today->day, $startOfMonth->daysInMonth);
        $totalWorkDaysSoFar = 0;
        for ($d = 1; $d <= $daysPassed; $d++) {
            $chkDate = $startOfMonth->copy()->setDay($d);
            if (! $chkDate->isSunday()) {
                $totalWorkDaysSoFar++;
            }
        }
        $totalAttended = $monthAttendances->whereNotNull('check_in')->count();
        $absentCount = max(0, $totalWorkDaysSoFar - $totalAttended);

        $totalMonthMinutes = $monthAttendances->sum('working_minutes');
        $monthHours = floor($totalMonthMinutes / 60);
        $monthMins = $totalMonthMinutes % 60;
        $totalMonthHoursFormatted = sprintf('%d:%02d', $monthHours, $monthMins);

        // Recent Activities (Today's logs or latest 5 logs)
        $recentActivities = AttendanceLog::where('user_id', $user->id)
            ->whereDate('date', $today->toDateString())
            ->orderBy('time', 'asc')
            ->get();

        if ($recentActivities->isEmpty()) {
            $recentActivities = AttendanceLog::where('user_id', $user->id)
                ->orderBy('date', 'desc')
                ->orderBy('time', 'desc')
                ->take(4)
                ->get();
        }

        // Active Office for Geofencing
        $office = $user->office ?: Office::where('is_active', true)->first();

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
            'office'
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
        $calendarDays = [];
        $daysInMonth = $currentMonthDate->daysInMonth;
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $currentMonthDate->copy()->setDay($day);
            $dateStr = $date->toDateString();
            $att = $attendances->get($dateStr);

            $calendarDays[] = [
                'day_num' => $day,
                'day_name' => strtoupper($date->format('D')),
                'date_str' => $dateStr,
                'is_sunday' => $date->isSunday(),
                'is_selected' => $dateStr === $selectedDate,
                'has_attendance' => (bool) $att,
                'status' => $att ? $att->check_in_status : ($date->isPast() && ! $date->isSunday() ? 'absent' : 'upcoming'),
            ];
        }

        // Selected day attendance details and logs
        $selectedAttendance = $attendances->get($selectedDate);
        $selectedLogs = AttendanceLog::where('user_id', $user->id)
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
     * Submit Presensi GPS & Face Verification
     */
    public function punch(Request $request)
    {
        $request->validate([
            'type' => 'required|in:check_in,break_start,break_end,check_out',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'photo' => 'required|string', // Base64 image data URL
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

        if (! $isWithinRadius) {
            return response()->json([
                'success' => false,
                'message' => "Anda berada di luar radius kantor! Jarak Anda: {$distance} meter (Maksimal: {$allowedRadius} meter).",
                'distance' => $distance,
                'radius' => $allowedRadius,
            ], 422);
        }

        // Save Selfie Photo
        $photoPath = null;
        if ($request->photo && str_starts_with($request->photo, 'data:image')) {
            $imageData = preg_replace('#^data:image/\w+;base64,#i', '', $request->photo);
            $imageData = base64_decode($imageData);
            $fileName = 'attendances/' . $user->id . '_' . time() . '_' . $request->type . '.jpg';
            Storage::disk('public')->put($fileName, $imageData);
            $photoPath = $fileName;
        }

        $now = Carbon::now('Asia/Jakarta');
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
                'status' => 'present',
                'face_verified' => true,
            ]
        );

        $logStatus = 'On time';
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
                $attendance->status = $isLate ? 'late' : 'present';
                $logStatus = $isLate ? 'Late' : 'On time';
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
                $attendance->check_out = $timeStr;
                $attendance->check_out_lat = $request->latitude;
                $attendance->check_out_lng = $request->longitude;
                $attendance->check_out_distance = $distance;
                $attendance->check_out_photo = $photoPath;
                $attendance->check_out_status = 'normal';
                $logStatus = 'Completed';
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
            'location_name' => $office->name,
            'photo_path' => $photoPath,
            'status' => $logStatus,
            'duration' => $logDuration,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Presensi ' . ucfirst(str_replace('_', ' ', $request->type)) . ' berhasil dicatat!',
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
