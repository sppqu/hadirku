<x-filament-panels::page>
    @php
        $profile = \App\Models\CampusProfile::current();
    @endphp

    <div class="space-y-6">
        {{-- Ringkasan Identitas Kampus --}}
        <div class="relative overflow-hidden rounded-2xl p-6 text-white shadow-lg" style="background: linear-gradient(135deg, #059669 0%, #0d9488 50%, #047857 100%); border-radius: 1rem; padding: 1.5rem 1.75rem; color: #ffffff; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
            <div class="absolute -right-12 -top-12 h-44 w-44 rounded-full bg-white/10 blur-2xl"></div>
            <div class="relative z-10 flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                <div style="display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap;">
                    {{-- Logo Box dengan Spacing Lega --}}
                    <div style="flex-shrink: 0; width: 92px; height: 92px; background-color: #ffffff; border-radius: 1rem; padding: 0.4rem; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.15); border: 2px solid rgba(255, 255, 255, 0.5); margin-right: 1.25rem;">
                        <img
                            src="{{ $profile->logo_url }}"
                            alt="{{ $profile->name }}"
                            style="width: 100%; height: 100%; object-fit: contain; border-radius: 0.65rem;"
                        />
                    </div>

                    {{-- Informasi & Keterangan Kampus --}}
                    <div style="flex: 1; min-width: 260px;">
                        <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem;">
                            <h2 style="font-size: 1.5rem; font-weight: 900; letter-spacing: -0.02em; line-height: 1.25; margin: 0; color: #ffffff;">{{ $profile->name }}</h2>
                            @if($profile->accreditation)
                                <span style="display: inline-flex; align-items: center; border-radius: 9999px; background-color: rgba(255, 255, 255, 0.2); padding: 0.2rem 0.75rem; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; border: 1px solid rgba(255, 255, 255, 0.35); color: #ffffff;">
                                    Akreditasi: {{ $profile->accreditation }}
                                </span>
                            @endif
                        </div>
                        
                        <p style="margin-top: 0.4rem; margin-bottom: 0; font-size: 0.875rem; color: #d1fae5; line-height: 1.4;">
                            {{ $profile->rector_name ? 'Rektor: ' . $profile->rector_name : 'Sistem Presensi Digital Kampus' }}
                            @if($profile->campus_code)
                                &bull; <span style="font-family: monospace; font-size: 0.8rem; font-weight: 600;">Kode PT: {{ $profile->campus_code }}</span>
                            @endif
                        </p>

                        <div style="margin-top: 0.75rem; display: flex; flex-wrap: wrap; align-items: center; gap: 1.25rem; font-size: 0.8rem; color: rgba(209, 250, 229, 0.95);">
                            @if($profile->address)
                                <div style="display: inline-flex; align-items: center; gap: 0.4rem;">
                                    <x-filament::icon icon="heroicon-m-map-pin" class="h-4 w-4 text-emerald-300" style="width: 1rem; height: 1rem; color: #6ee7b7; flex-shrink: 0;" />
                                    <span>{{ $profile->city ? $profile->city . ', ' . $profile->province : $profile->address }}</span>
                                </div>
                            @endif
                            @if($profile->email)
                                <div style="display: inline-flex; align-items: center; gap: 0.4rem;">
                                    <x-filament::icon icon="heroicon-m-envelope" class="h-4 w-4 text-emerald-300" style="width: 1rem; height: 1rem; color: #6ee7b7; flex-shrink: 0;" />
                                    <span>{{ $profile->email }}</span>
                                </div>
                            @endif
                            @if($profile->phone)
                                <div style="display: inline-flex; align-items: center; gap: 0.4rem;">
                                    <x-filament::icon icon="heroicon-m-phone" class="h-4 w-4 text-emerald-300" style="width: 1rem; height: 1rem; color: #6ee7b7; flex-shrink: 0;" />
                                    <span>{{ $profile->phone }}</span>
                                </div>
                            @endif
                            @if($profile->website)
                                <a href="{{ $profile->website }}" target="_blank" style="display: inline-flex; align-items: center; gap: 0.4rem; color: #ffffff; text-decoration: underline;" onmouseover="this.style.color='#a7f3d0'" onmouseout="this.style.color='#ffffff'">
                                    <x-filament::icon icon="heroicon-m-globe-alt" class="h-4 w-4 text-emerald-300" style="width: 1rem; height: 1rem; color: #6ee7b7; flex-shrink: 0;" />
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
