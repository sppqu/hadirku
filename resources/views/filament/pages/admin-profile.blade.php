<x-filament-panels::page>
    @php
        $user = auth()->user();
    @endphp

    <div class="space-y-6">
        {{-- Ringkasan Identitas Admin --}}
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <img
                        src="{{ $user->avatar_url }}"
                        alt="{{ $user->name }}"
                        class="h-16 w-16 rounded-full object-cover ring-2 ring-emerald-500/30"
                    />
                    <div>
                        <h2 class="text-lg font-bold text-gray-950 dark:text-white">{{ $user->name }}</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                        <div class="mt-1.5 flex flex-wrap items-center gap-2 text-xs">
                            <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/30">
                                {{ strtoupper($user->role ?? 'ADMIN') }}
                            </span>
                            @if($user->nik)
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 font-mono font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                    NIK: {{ $user->nik }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Form Update Profil --}}
        <form wire:submit="updateProfile" class="space-y-4">
            {{ $this->profileForm }}

            <div class="flex justify-end">
                <x-filament::button type="submit" icon="heroicon-o-check">
                    Simpan Profil
                </x-filament::button>
            </div>
        </form>

        {{-- Form Ubah Kata Sandi --}}
        <form wire:submit="updatePassword" class="space-y-4">
            {{ $this->passwordForm }}

            <div class="flex justify-end">
                <x-filament::button type="submit" color="warning" icon="heroicon-o-key">
                    Ubah Kata Sandi
                </x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
