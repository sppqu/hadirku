<style>
    /* Admin Dashboard Stat Cards - Matches Laporan Kehadiran Styling */

    /* 1. Total Pegawai (Indigo) */
    .card-stat-pegawai,
    .fi-wi-stats-overview-stat.card-stat-pegawai {
        border: 1.5px solid #6366f1 !important;
        border-radius: 1rem !important;
        --tw-ring-color: transparent !important;
    }
    .card-stat-pegawai .fi-wi-stats-overview-stat-label,
    .card-stat-pegawai .fi-wi-stats-overview-stat-value,
    .card-stat-pegawai .fi-wi-stats-overview-stat-description,
    .card-stat-pegawai p,
    .card-stat-pegawai span,
    .card-stat-pegawai svg {
        color: #4f46e5 !important;
    }
    .dark .card-stat-pegawai,
    .dark .fi-wi-stats-overview-stat.card-stat-pegawai {
        border-color: #6366f1 !important;
    }
    .dark .card-stat-pegawai .fi-wi-stats-overview-stat-label,
    .dark .card-stat-pegawai .fi-wi-stats-overview-stat-value,
    .dark .card-stat-pegawai .fi-wi-stats-overview-stat-description,
    .dark .card-stat-pegawai p,
    .dark .card-stat-pegawai span,
    .dark .card-stat-pegawai svg {
        color: #818cf8 !important;
    }

    /* 2. Hadir Hari Ini (Emerald) */
    .card-stat-hadir,
    .fi-wi-stats-overview-stat.card-stat-hadir {
        border: 1.5px solid #10b981 !important;
        border-radius: 1rem !important;
        --tw-ring-color: transparent !important;
    }
    .card-stat-hadir .fi-wi-stats-overview-stat-label,
    .card-stat-hadir .fi-wi-stats-overview-stat-value,
    .card-stat-hadir .fi-wi-stats-overview-stat-description,
    .card-stat-hadir p,
    .card-stat-hadir span,
    .card-stat-hadir svg {
        color: #059669 !important;
    }
    .dark .card-stat-hadir,
    .dark .fi-wi-stats-overview-stat.card-stat-hadir {
        border-color: #10b981 !important;
    }
    .dark .card-stat-hadir .fi-wi-stats-overview-stat-label,
    .dark .card-stat-hadir .fi-wi-stats-overview-stat-value,
    .dark .card-stat-hadir .fi-wi-stats-overview-stat-description,
    .dark .card-stat-hadir p,
    .dark .card-stat-hadir span,
    .dark .card-stat-hadir svg {
        color: #34d399 !important;
    }

    /* 3. Terlambat Hari Ini (Rose) */
    .card-stat-terlambat,
    .fi-wi-stats-overview-stat.card-stat-terlambat {
        border: 1.5px solid #f43f5e !important;
        border-radius: 1rem !important;
        --tw-ring-color: transparent !important;
    }
    .card-stat-terlambat .fi-wi-stats-overview-stat-label,
    .card-stat-terlambat .fi-wi-stats-overview-stat-value,
    .card-stat-terlambat .fi-wi-stats-overview-stat-description,
    .card-stat-terlambat p,
    .card-stat-terlambat span,
    .card-stat-terlambat svg {
        color: #e11d48 !important;
    }
    .dark .card-stat-terlambat,
    .dark .fi-wi-stats-overview-stat.card-stat-terlambat {
        border-color: #f43f5e !important;
    }
    .dark .card-stat-terlambat .fi-wi-stats-overview-stat-label,
    .dark .card-stat-terlambat .fi-wi-stats-overview-stat-value,
    .dark .card-stat-terlambat .fi-wi-stats-overview-stat-description,
    .dark .card-stat-terlambat p,
    .dark .card-stat-terlambat span,
    .dark .card-stat-terlambat svg {
        color: #fb7185 !important;
    }

    /* 4. Izin / Cuti Hari Ini (Amber/Warning) */
    .card-stat-izin,
    .fi-wi-stats-overview-stat.card-stat-izin {
        border: 1.5px solid #f59e0b !important;
        border-radius: 1rem !important;
        --tw-ring-color: transparent !important;
    }
    .card-stat-izin .fi-wi-stats-overview-stat-label,
    .card-stat-izin .fi-wi-stats-overview-stat-value,
    .card-stat-izin .fi-wi-stats-overview-stat-description,
    .card-stat-izin p,
    .card-stat-izin span,
    .card-stat-izin svg {
        color: #d97706 !important;
    }
    .dark .card-stat-izin,
    .dark .fi-wi-stats-overview-stat.card-stat-izin {
        border-color: #f59e0b !important;
    }
    .dark .card-stat-izin .fi-wi-stats-overview-stat-label,
    .dark .card-stat-izin .fi-wi-stats-overview-stat-value,
    .dark .card-stat-izin .fi-wi-stats-overview-stat-description,
    .dark .card-stat-izin p,
    .dark .card-stat-izin span,
    .dark .card-stat-izin svg {
        color: #fbbf24 !important;
    }

    /* Value Typography Emphasis */
    .card-stat-pegawai .fi-wi-stats-overview-stat-value,
    .card-stat-hadir .fi-wi-stats-overview-stat-value,
    .card-stat-terlambat .fi-wi-stats-overview-stat-value,
    .card-stat-izin .fi-wi-stats-overview-stat-value {
        font-weight: 800 !important;
        letter-spacing: -0.02em !important;
    }

    .card-stat-pegawai .fi-wi-stats-overview-stat-label,
    .card-stat-hadir .fi-wi-stats-overview-stat-label,
    .card-stat-terlambat .fi-wi-stats-overview-stat-label,
    .card-stat-izin .fi-wi-stats-overview-stat-label {
        font-weight: 600 !important;
        font-size: 0.85rem !important;
        letter-spacing: 0.01em !important;
    }
</style>
