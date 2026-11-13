@php
    $campusProfile = null;
    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('campus_profiles')) {
            $campusProfile = \App\Models\CampusProfile::first();
        }
    } catch (\Throwable $e) {
        $campusProfile = null;
    }
@endphp
<div class="flex items-center gap-3 py-1.5 px-1">
    <img src="{{ $campusProfile ? $campusProfile->logo_url : asset('images/logo.jpg') }}" alt="{{ $campusProfile ? $campusProfile->name : 'HadirKU' }}" class="h-10 w-10 rounded-xl object-contain bg-white shadow-sm border border-emerald-500/30 flex-shrink-0" />
    <div class="flex flex-col text-left pl-0.5 min-w-0">
        <span class="text-sm font-black tracking-tight text-gray-900 dark:text-white leading-tight truncate">{{ $campusProfile && $campusProfile->name ? $campusProfile->name : 'HAdirKu' }}</span>
        <span class="text-[9px] font-bold text-emerald-600 dark:text-emerald-400 tracking-wider uppercase mt-0.5">PRESENSI KAMPUS</span>
    </div>
</div>
