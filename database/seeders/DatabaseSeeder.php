<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\Department;
use App\Models\Leave;
use App\Models\Office;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Offices
        $office = Office::create([
            'name' => 'Kantor Pusat HadirKU',
            'address' => 'Jl. Jend. Sudirman No. 45, Kebayoran Baru, Jakarta Selatan',
            'latitude' => -6.225014,
            'longitude' => 106.809784,
            'radius_meters' => 150,
            'is_active' => true,
        ]);

        $branchOffice = Office::create([
            'name' => 'Kantor Cabang Bandung',
            'address' => 'Jl. Asia Afrika No. 120, Kota Bandung, Jawa Barat',
            'latitude' => -6.921852,
            'longitude' => 107.611128,
            'radius_meters' => 100,
            'is_active' => true,
        ]);

        // 2. Departments
        $deptIT = Department::create([
            'name' => 'Information Technology',
            'description' => 'Divisi Rekayasa Perangkat Lunak, IT Support, dan Infrastruktur',
        ]);
        $deptHR = Department::create([
            'name' => 'Human Resource',
            'description' => 'Divisi Personalia, Rekrutmen, dan Budaya Kerja',
        ]);
        $deptFinance = Department::create([
            'name' => 'Finance & Accounting',
            'description' => 'Divisi Keuangan, Akuntansi, dan Penggajian',
        ]);
        $deptMarketing = Department::create([
            'name' => 'Marketing & Sales',
            'description' => 'Divisi Pemasaran dan Penjualan',
        ]);

        // 3. Shifts
        $shiftRegular = Shift::create([
            'name' => 'Shift Regular (08:00 - 17:00)',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'grace_period_minutes' => 15,
            'break_start_time' => '12:00:00',
            'break_end_time' => '13:00:00',
        ]);

        $shiftMorning = Shift::create([
            'name' => 'Shift Pagi (07:00 - 15:30)',
            'start_time' => '07:00:00',
            'end_time' => '15:30:00',
            'grace_period_minutes' => 10,
            'break_start_time' => '11:30:00',
            'break_end_time' => '12:30:00',
        ]);

        // 4. Users
        // Admin
        $admin = User::create([
            'nik' => 'ADM-001',
            'name' => 'Administrator HadirKU',
            'email' => 'admin@hadirku.com',
            'phone' => '08119876543',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'department_id' => $deptIT->id,
            'shift_id' => $shiftRegular->id,
            'office_id' => $office->id,
            'position' => 'Super Administrator',
            'is_active' => true,
        ]);

        // Employee: John Doe (matching demo screenshot)
        $john = User::create([
            'nik' => 'HK-2024001',
            'name' => 'John Doe',
            'email' => 'john@hadirku.com',
            'phone' => '081234567890',
            'password' => Hash::make('password'),
            'role' => 'pegawai',
            'department_id' => $deptIT->id,
            'shift_id' => $shiftRegular->id,
            'office_id' => $office->id,
            'position' => 'Lead Software Engineer',
            'is_active' => true,
        ]);

        // Additional Employees
        $siti = User::create([
            'nik' => 'HK-2024002',
            'name' => 'Siti Nurhaliza',
            'email' => 'siti@hadirku.com',
            'phone' => '081345678901',
            'password' => Hash::make('password'),
            'role' => 'pegawai',
            'department_id' => $deptHR->id,
            'shift_id' => $shiftRegular->id,
            'office_id' => $office->id,
            'position' => 'HR Specialist',
            'is_active' => true,
        ]);

        $budi = User::create([
            'nik' => 'HK-2024003',
            'name' => 'Budi Santoso',
            'email' => 'budi@hadirku.com',
            'phone' => '081456789012',
            'password' => Hash::make('password'),
            'role' => 'pegawai',
            'department_id' => $deptFinance->id,
            'shift_id' => $shiftRegular->id,
            'office_id' => $office->id,
            'position' => 'Accounting Officer',
            'is_active' => true,
        ]);

        // 5. Seed Attendance History for John (matching screenshot data: past days check-in times & today)
        $today = Carbon::today();
        // Let's seed past 14 days
        for ($i = 14; $i >= 1; $i--) {
            $date = Carbon::today()->subDays($i);
            // Skip Sundays
            if ($date->isSunday()) {
                continue;
            }

            $checkInHour = 8;
            $checkInMin = rand(45, 59); // on time
            $isLate = false;
            if ($i == 3) {
                // simulate one late
                $checkInHour = 9;
                $checkInMin = 20;
                $isLate = true;
            } elseif ($i == 7) {
                // simulate one absent
                continue;
            }

            $checkInStr = sprintf('%02d:%02d:00', $checkInHour, $checkInMin);
            $checkOutStr = '17:15:00';
            $workingMinutes = 8 * 60 + rand(10, 30);

            $att = Attendance::create([
                'user_id' => $john->id,
                'office_id' => $office->id,
                'date' => $date->toDateString(),
                'check_in' => $checkInStr,
                'check_out' => $checkOutStr,
                'break_start' => '12:00:00',
                'break_end' => '12:45:00',
                'check_in_lat' => -6.225010,
                'check_in_lng' => 106.809780,
                'check_in_distance' => 15,
                'check_in_status' => $isLate ? 'late' : 'on_time',
                'check_out_lat' => -6.225015,
                'check_out_lng' => 106.809790,
                'check_out_distance' => 18,
                'check_out_status' => 'normal',
                'working_minutes' => $workingMinutes,
                'break_minutes' => 45,
                'status' => $isLate ? 'late' : 'present',
                'face_verified' => true,
            ]);

            AttendanceLog::create([
                'attendance_id' => $att->id,
                'user_id' => $john->id,
                'type' => 'check_in',
                'date' => $date->toDateString(),
                'time' => $checkInStr,
                'latitude' => -6.225010,
                'longitude' => 106.809780,
                'distance_meters' => 15,
                'location_name' => 'Office',
                'status' => $isLate ? 'Late' : 'On time',
            ]);
            AttendanceLog::create([
                'attendance_id' => $att->id,
                'user_id' => $john->id,
                'type' => 'break_start',
                'date' => $date->toDateString(),
                'time' => '12:00:00',
                'latitude' => -6.225010,
                'longitude' => 106.809780,
                'distance_meters' => 15,
                'location_name' => 'Office',
                'duration' => '00:45',
            ]);
            AttendanceLog::create([
                'attendance_id' => $att->id,
                'user_id' => $john->id,
                'type' => 'break_end',
                'date' => $date->toDateString(),
                'time' => '12:45:00',
                'latitude' => -6.225010,
                'longitude' => 106.809780,
                'distance_meters' => 15,
                'location_name' => 'Office',
            ]);
            AttendanceLog::create([
                'attendance_id' => $att->id,
                'user_id' => $john->id,
                'type' => 'check_out',
                'date' => $date->toDateString(),
                'time' => $checkOutStr,
                'latitude' => -6.225015,
                'longitude' => 106.809790,
                'distance_meters' => 18,
                'location_name' => 'Office',
                'status' => 'Completed',
            ]);
        }

        // Today's attendance for John (matching screenshot: Check in 09:15 AM, Break 00:45, Working Hours 03:45, Check out pending)
        $todayAtt = Attendance::create([
            'user_id' => $john->id,
            'office_id' => $office->id,
            'date' => $today->toDateString(),
            'check_in' => '09:15:00',
            'check_out' => null,
            'break_start' => '13:15:00',
            'break_end' => '14:00:00',
            'check_in_lat' => -6.225012,
            'check_in_lng' => 106.809782,
            'check_in_distance' => 12,
            'check_in_status' => 'on_time',
            'working_minutes' => 225, // 3 hours 45 mins
            'break_minutes' => 45,
            'status' => 'present',
            'face_verified' => true,
        ]);

        AttendanceLog::create([
            'attendance_id' => $todayAtt->id,
            'user_id' => $john->id,
            'type' => 'check_in',
            'date' => $today->toDateString(),
            'time' => '09:15:00',
            'latitude' => -6.225012,
            'longitude' => 106.809782,
            'distance_meters' => 12,
            'location_name' => 'Office',
            'status' => 'On time',
        ]);
        AttendanceLog::create([
            'attendance_id' => $todayAtt->id,
            'user_id' => $john->id,
            'type' => 'break_start',
            'date' => $today->toDateString(),
            'time' => '13:15:00',
            'latitude' => -6.225012,
            'longitude' => 106.809782,
            'distance_meters' => 12,
            'location_name' => 'Office',
            'duration' => '00:45',
        ]);
        AttendanceLog::create([
            'attendance_id' => $todayAtt->id,
            'user_id' => $john->id,
            'type' => 'break_end',
            'date' => $today->toDateString(),
            'time' => '14:00:00',
            'latitude' => -6.225012,
            'longitude' => 106.809782,
            'distance_meters' => 12,
            'location_name' => 'Office',
        ]);

        // Today's attendance for Siti
        Attendance::create([
            'user_id' => $siti->id,
            'office_id' => $office->id,
            'date' => $today->toDateString(),
            'check_in' => '08:05:00',
            'check_out' => null,
            'check_in_lat' => -6.225014,
            'check_in_lng' => 106.809784,
            'check_in_distance' => 8,
            'check_in_status' => 'on_time',
            'working_minutes' => 180,
            'status' => 'present',
            'face_verified' => true,
        ]);

        // Sample Leave request for Budi
        Leave::create([
            'user_id' => $budi->id,
            'type' => 'izin',
            'start_date' => Carbon::today()->addDays(2)->toDateString(),
            'end_date' => Carbon::today()->addDays(3)->toDateString(),
            'reason' => 'Keperluan keluarga penting di luar kota',
            'status' => 'pending',
        ]);

        Leave::create([
            'user_id' => $john->id,
            'type' => 'cuti',
            'start_date' => Carbon::today()->subDays(20)->toDateString(),
            'end_date' => Carbon::today()->subDays(19)->toDateString(),
            'reason' => 'Cuti Tahunan',
            'status' => 'approved',
            'approved_by' => $admin->id,
            'admin_notes' => 'Disetujui. Selamat berlibur.',
        ]);
    }
}
