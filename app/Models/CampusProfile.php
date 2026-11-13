<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CampusProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'foundation',
        'employee_id_label',
        'campus_code',
        'rector_name',
        'accreditation',
        'email',
        'phone',
        'website',
        'address',
        'city',
        'province',
        'postal_code',
        'logo',
        'favicon',
        'description',
        'vision',
        'mission',
    ];

    /**
     * Singleton helper to get or initialize the campus profile.
     */
    public static function current(): self
    {
        return static::firstOrCreate(
            ['id' => 1],
            [
                'name' => 'Universitas HadirKU',
                'foundation' => null,
                'employee_id_label' => 'NIK',
                'campus_code' => '041001',
                'rector_name' => 'Prof. Dr. H. Irwan Santoso, M.Kom.',
                'accreditation' => 'Unggul',
                'email' => 'info@universitas.ac.id',
                'phone' => '021-78901234',
                'website' => 'https://hadirku.ac.id',
                'address' => 'Jl. Kampus Merdeka No. 100, Jakarta Selatan',
                'city' => 'Jakarta Selatan',
                'province' => 'DKI Jakarta',
                'postal_code' => '12190',
                'description' => 'Sistem Informasi Presensi & Manajemen Kehadiran Akademik Universitas HadirKU.',
                'vision' => 'Menjadi perguruan tinggi unggul bereputasi nasional dan internasional yang berakar pada integritas serta inovasi teknologi.',
                'mission' => "1. Menyelenggarakan pendidikan akademik yang berkualitas tinggi.\n2. Mengembangkan penelitian dan inovasi yang berdampak nyata bagi masyarakat dan industri.\n3. Mengimplementasikan tata kelola perguruan tinggi modern berbasis teknologi digital.",
            ]
        );
    }

    public function getLogoUrlAttribute(): string
    {
        if ($this->logo && Storage::disk('public')->exists($this->logo)) {
            return asset('storage/' . $this->logo);
        }
        if (file_exists(public_path('images/logo.png'))) {
            return asset('images/logo.png');
        }
        return asset('images/logo.jpg');
    }

    public function getYayasanAttribute(): ?string
    {
        return $this->foundation;
    }

    /**
     * Dapatkan istilah nomor identitas pegawai yang dikonfigurasi (misal: NIP, NIK, NIDN, NUPTK)
     */
    public static function getEmployeeIdLabel(): string
    {
        try {
            $profile = static::first();
            if ($profile && !empty($profile->employee_id_label)) {
                return trim($profile->employee_id_label);
            }
        } catch (\Throwable $e) {
            // Fallback jika belum migrasi
        }
        return 'NIK';
    }
}
