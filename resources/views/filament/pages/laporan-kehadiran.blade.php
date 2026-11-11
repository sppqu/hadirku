<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filter Card --}}
        <x-filament::section>
            <form wire:submit="filter" class="space-y-4">
                {{ $this->form }}

                <div class="flex items-center gap-3 pt-2">
                    <x-filament::button type="submit" icon="heroicon-m-funnel">
                        Terapkan Filter
                    </x-filament::button>

                    <button 
                        type="button" 
                        onclick="window.print()" 
                        class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg shadow-sm transition gap-2"
                    >
                        <x-heroicon-m-printer class="w-5 h-5" />
                        Cetak Laporan
                    </button>
                </div>
            </form>
        </x-filament::section>

        {{-- Metrics Summary --}}
        @php
            $data = $this->reportData;
        @endphp
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Kehadiran</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $data['total_hadir'] }} Hari</div>
            </div>
            <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="text-sm font-medium text-emerald-600">Tepat Waktu</div>
                <div class="text-2xl font-bold text-emerald-600 mt-1">{{ $data['total_tepat_waktu'] }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="text-sm font-medium text-red-600">Terlambat</div>
                <div class="text-2xl font-bold text-red-600 mt-1">{{ $data['total_terlambat'] }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="text-sm font-medium text-blue-600">Total Akumulasi Jam</div>
                <div class="text-2xl font-bold text-blue-600 mt-1">{{ $data['total_jam_kerja'] }} Jam</div>
            </div>
        </div>

        {{-- Report Printable Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden print-area">
            {{-- Print Header --}}
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Rekapitulasi Kehadiran Pegawai</h2>
                    <p class="text-sm text-gray-500">Periode: {{ \Carbon\Carbon::parse($this->start_date)->translatedFormat('d F Y') }} - {{ \Carbon\Carbon::parse($this->end_date)->translatedFormat('d F Y') }}</p>
                </div>
                <div class="text-right">
                    <span class="text-xs px-3 py-1 bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 rounded-full font-semibold">HadirKU E-Presensi</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-900 dark:text-gray-300">
                        <tr>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">NIK</th>
                            <th class="px-4 py-3">Nama Pegawai</th>
                            <th class="px-4 py-3">Departemen</th>
                            <th class="px-4 py-3 text-center">Jam Masuk</th>
                            <th class="px-4 py-3 text-center">Jam Pulang</th>
                            <th class="px-4 py-3 text-center">Durasi</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Lokasi & GPS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($data['records'] as $record)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50">
                                <td class="px-4 py-3 font-medium">{{ \Carbon\Carbon::parse($record->date)->translatedFormat('d M Y') }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $record->user->nik ?? '-' }}</td>
                                <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">{{ $record->user->name }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $record->user->department->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="font-mono">{{ $record->check_in ? date('H:i', strtotime($record->check_in)) : '-' }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="font-mono">{{ $record->check_out ? date('H:i', strtotime($record->check_out)) : '-' }}</span>
                                </td>
                                <td class="px-4 py-3 text-center font-mono">{{ $record->formatted_working_hours }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if($record->check_in_status === 'late')
                                        <span class="px-2 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Terlambat</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Tepat Waktu</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center text-xs text-gray-500">
                                    {{ $record->check_in_distance ? $record->check_in_distance . ' m' : 'Kantor' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                                    Tidak ada data kehadiran pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            .print-area, .print-area * {
                visibility: visible;
            }
            .print-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
        }
    </style>
</x-filament-panels::page>
