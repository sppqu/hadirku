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
        $office = Office::firstOrCreate(
            ['name' => 'Kantor Pusat HadirKU'],
            [
                'address' => 'Jl. Jend. Sudirman No. 45, Kebayoran Baru, Jakarta Selatan',
                'latitude' => -6.225014,
                'longitude' => 106.809784,
                'radius_meters' => 150,
                'is_active' => true,
            ]
        );

        $branchOffice = Office::firstOrCreate(
            ['name' => 'Kantor Cabang Bandung'],
            [
                'address' => 'Jl. Asia Afrika No. 120, Kota Bandung, Jawa Barat',
                'latitude' => -6.921852,
                'longitude' => 107.611128,
                'radius_meters' => 100,
                'is_active' => true,
            ]
        );

        // 2. Departments
        $deptIT = Department::firstOrCreate(
            ['name' => 'Information Technology'],
            ['description' => 'Divisi Rekayasa Perangkat Lunak, IT Support, dan Infrastruktur']
        );
        $deptHR = Department::firstOrCreate(
            ['name' => 'Human Resource'],
            ['description' => 'Divisi Personalia, Rekrutmen, dan Budaya Kerja']
        );
        $deptFinance = Department::firstOrCreate(
            ['name' => 'Finance & Accounting'],
            ['description' => 'Divisi Keuangan, Akuntansi, dan Penggajian']
        );
        $deptMarketing = Department::firstOrCreate(
            ['name' => 'Marketing & Sales'],
            ['description' => 'Divisi Pemasaran dan Penjualan']
        );

        // 3. Shifts
        $shiftRegular = Shift::firstOrCreate(
            ['name' => 'Shift Regular (08:00 - 17:00)'],
            [
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'grace_period_minutes' => 15,
                'break_start_time' => '12:00:00',
                'break_end_time' => '13:00:00',
            ]
        );

        $shiftMorning = Shift::firstOrCreate(
            ['name' => 'Shift Pagi (07:00 - 15:30)'],
            [
                'start_time' => '07:00:00',
                'end_time' => '15:30:00',
                'grace_period_minutes' => 10,
                'break_start_time' => '11:30:00',
                'break_end_time' => '12:30:00',
            ]
        );

        // 4. Users
        // Admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@hadirku.com'],
            [
                'nik' => 'ADM-001',
                'name' => 'Administrator HadirKU',
                'phone' => '08119876543',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'department_id' => $deptIT->id,
                'shift_id' => $shiftRegular->id,
                'office_id' => $office->id,
                'position' => 'Super Administrator',
                'is_active' => true,
            ]
        );

        // Employee: John Doe (matching demo screenshot)
        $john = User::updateOrCreate(
            ['email' => 'john@hadirku.com'],
            [
                'nik' => 'HK-2024001',
                'name' => 'John Doe',
                'phone' => '081234567890',
                'password' => Hash::make('password'),
                'role' => 'pegawai',
                'department_id' => $deptIT->id,
                'shift_id' => $shiftRegular->id,
                'office_id' => $office->id,
                'position' => 'Lead Software Engineer',
                'is_active' => true,
            ]
        );

        // Additional Employees
        $siti = User::updateOrCreate(
            ['email' => 'siti@hadirku.com'],
            [
                'nik' => 'HK-2024002',
                'name' => 'Siti Nurhaliza',
                'phone' => '081345678901',
                'password' => Hash::make('password'),
                'role' => 'pegawai',
                'department_id' => $deptHR->id,
                'shift_id' => $shiftRegular->id,
                'office_id' => $office->id,
                'position' => 'HR Specialist',
                'is_active' => true,
            ]
        );

        $budi = User::updateOrCreate(
            ['email' => 'budi@hadirku.com'],
            [
                'nik' => 'HK-2024003',
                'name' => 'Budi Santoso',
                'phone' => '081456789012',
                'password' => Hash::make('password'),
                'role' => 'pegawai',
                'department_id' => $deptFinance->id,
                'shift_id' => $shiftRegular->id,
                'office_id' => $office->id,
                'position' => 'Accounting Officer',
                'is_active' => true,
            ]
        );

        // 5. Seed Attendance History for John
        $today = Carbon::today();
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

            $att = Attendance::updateOrCreate(
                [
                    'user_id' => $john->id,
                    'date' => $date->toDateString(),
                ],
                [
                    'office_id' => $office->id,
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
                ]
            );

            AttendanceLog::firstOrCreate(
                [
                    'attendance_id' => $att->id,
                    'user_id' => $john->id,
                    'type' => 'check_in',
                    'date' => $date->toDateString(),
                ],
                [
                    'time' => $checkInStr,
                    'latitude' => -6.225010,
                    'longitude' => 106.809780,
                    'distance_meters' => 15,
                    'location_name' => 'Office',
                    'status' => $isLate ? 'Late' : 'On time',
                ]
            );

            AttendanceLog::firstOrCreate(
                [
                    'attendance_id' => $att->id,
                    'user_id' => $john->id,
                    'type' => 'check_out',
                    'date' => $date->toDateString(),
                ],
                [
                    'time' => $checkOutStr,
                    'latitude' => -6.225015,
                    'longitude' => 106.809790,
                    'distance_meters' => 18,
                    'location_name' => 'Office',
                    'status' => 'Completed',
                ]
            );
        }

        // Today's attendance for John
        $todayAtt = Attendance::updateOrCreate(
            [
                'user_id' => $john->id,
                'date' => $today->toDateString(),
            ],
            [
                'office_id' => $office->id,
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
            ]
        );

        AttendanceLog::firstOrCreate(
            [
                'attendance_id' => $todayAtt->id,
                'user_id' => $john->id,
                'type' => 'check_in',
                'date' => $today->toDateString(),
            ],
            [
                'time' => '09:15:00',
                'latitude' => -6.225012,
                'longitude' => 106.809782,
                'distance_meters' => 12,
                'location_name' => 'Office',
                'status' => 'On time',
            ]
        );

        // Today's attendance for Siti
        Attendance::updateOrCreate(
            [
                'user_id' => $siti->id,
                'date' => $today->toDateString(),
            ],
            [
                'office_id' => $office->id,
                'check_in' => '08:05:00',
                'check_out' => null,
                'check_in_lat' => -6.225014,
                'check_in_lng' => 106.809784,
                'check_in_distance' => 8,
                'check_in_status' => 'on_time',
                'working_minutes' => 180,
                'status' => 'present',
                'face_verified' => true,
            ]
        );

        // Sample Leave request for Budi
        Leave::firstOrCreate(
            [
                'user_id' => $budi->id,
                'start_date' => Carbon::today()->addDays(2)->toDateString(),
            ],
            [
                'type' => 'izin',
                'end_date' => Carbon::today()->addDays(3)->toDateString(),
                'reason' => 'Keperluan keluarga penting di luar kota',
                'status' => 'pending',
            ]
        );

        Leave::firstOrCreate(
            [
                'user_id' => $john->id,
                'start_date' => Carbon::today()->subDays(20)->toDateString(),
            ],
            [
                'type' => 'cuti',
                'end_date' => Carbon::today()->subDays(19)->toDateString(),
                'reason' => 'Cuti Tahunan',
                'status' => 'approved',
                'approved_by' => $admin->id,
                'admin_notes' => 'Disetujui. Selamat berlibur.',
            ]
        );
    }
}
