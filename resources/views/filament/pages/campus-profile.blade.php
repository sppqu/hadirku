<x-filament-panels::page>
    @php
        $profile = \App\Models\CampusProfile::current();
    @endphp

    <div class="space-y-6">
        {{-- Ringkasan Identitas Kampus --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 p-6 text-white shadow-lg">
            <div class="absolute -right-12 -top-12 h-44 w-44 rounded-full bg-white/10 blur-2xl"></div>
            <div class="relative z-10 flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-5">
                    <img
                        src="{{ $profile->logo_url }}"
                        alt="{{ $profile->name }}"
                        class="h-20 w-20 rounded-2xl bg-white p-1.5 object-contain shadow-md ring-2 ring-white/40 flex-shrink-0"
                    />
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-xl font-black tracking-tight sm:text-2xl">{{ $profile->name }}</h2>
                            @if($profile->accreditation)
                                <span class="inline-flex items-center rounded-full bg-white/20 px-3 py-0.5 text-xs font-bold uppercase tracking-wider backdrop-blur-sm border border-white/30">
                                    Akreditasi: {{ $profile->accreditation }}
                                </span>
                            @endif
                        </div>
                        
                        <p class="mt-1 text-sm text-emerald-100">
                            {{ $profile->rector_name ? 'Rektor: ' . $profile->rector_name : 'Sistem Presensi Digital Kampus' }}
                            @if($profile->campus_code)
                                &bull; <span class="font-mono text-xs">Kode PT: {{ $profile->campus_code }}</span>
                            @endif
                        </p>

                        <div class="mt-3 flex flex-wrap items-center gap-4 text-xs text-emerald-100/90">
                            @if($profile->address)
                                <div class="flex items-center gap-1.5">
                                    <x-filament::icon icon="heroicon-m-map-pin" class="h-4 w-4 text-emerald-300" />
                                    <span>{{ $profile->city ? $profile->city . ', ' . $profile->province : $profile->address }}</span>
                                </div>
                            @endif
                            @if($profile->email)
                                <div class="flex items-center gap-1.5">
                                    <x-filament::icon icon="heroicon-m-envelope" class="h-4 w-4 text-emerald-300" />
                                    <span>{{ $profile->email }}</span>
                                </div>
                            @endif
                            @if($profile->phone)
                                <div class="flex items-center gap-1.5">
                                    <x-filament::icon icon="heroicon-m-phone" class="h-4 w-4 text-emerald-300" />
                                    <span>{{ $profile->phone }}</span>
                                </div>
                            @endif
                            @if($profile->website)
                                <a href="{{ $profile->website }}" target="_blank" class="flex items-center gap-1.5 text-white underline hover:text-emerald-200">
                                    <x-filament::icon icon="heroicon-m-globe-alt" class="h-4 w-4 text-emerald-300" />
                                    <span>{{ parse_url($profile->website, PHP_URL_HOST) ?? $profile->website }}</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Form Pengaturan Profil Kampus --}}
        <form wire:submit="save" class="space-y-6">
            {{ $this->form }}

            <div class="flex justify-end">
                <x-filament::button type="submit" size="lg" icon="heroicon-o-check-badge">
                    Simpan Perubahan Profil Kampus
                </x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
