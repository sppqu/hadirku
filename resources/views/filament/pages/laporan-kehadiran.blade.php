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
        <div class="report-metrics-grid">
            {{-- Total Kehadiran --}}
            <div class="bg-white dark:bg-gray-900" style="border: 1.5px solid #6366f1; border-radius: 1rem; padding: 1.15rem 1.25rem; display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;">
                <div>
                    <div style="font-size: 0.8rem; font-weight: 600; color: #818cf8; letter-spacing: 0.01em;">Total Kehadiran</div>
                    <div style="font-size: 1.65rem; font-weight: 800; color: #818cf8; line-height: 1.2; margin-top: 0.35rem; white-space: nowrap;">{{ $data['total_hadir'] }} <span style="font-size: 0.95rem; font-weight: 600;">Hari</span></div>
                </div>
                <div style="color: #818cf8; flex-shrink: 0;">
                    <x-heroicon-o-calendar-days class="w-8 h-8" />
                </div>
            </div>

            {{-- Tepat Waktu --}}
            <div class="bg-white dark:bg-gray-900" style="border: 1.5px solid #10b981; border-radius: 1rem; padding: 1.15rem 1.25rem; display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;">
                <div>
                    <div style="font-size: 0.8rem; font-weight: 600; color: #34d399; letter-spacing: 0.01em;">Tepat Waktu</div>
                    <div style="font-size: 1.65rem; font-weight: 800; color: #34d399; line-height: 1.2; margin-top: 0.35rem; white-space: nowrap;">{{ $data['total_tepat_waktu'] }} <span style="font-size: 0.95rem; font-weight: 600;">Kali</span></div>
                </div>
                <div style="color: #34d399; flex-shrink: 0;">
                    <x-heroicon-o-check-circle class="w-8 h-8" />
                </div>
            </div>

            {{-- Terlambat --}}
            <div class="bg-white dark:bg-gray-900" style="border: 1.5px solid #f43f5e; border-radius: 1rem; padding: 1.15rem 1.25rem; display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;">
                <div>
                    <div style="font-size: 0.8rem; font-weight: 600; color: #fb7185; letter-spacing: 0.01em;">Terlambat</div>
                    <div style="font-size: 1.65rem; font-weight: 800; color: #fb7185; line-height: 1.2; margin-top: 0.35rem; white-space: nowrap;">{{ $data['total_terlambat'] }} <span style="font-size: 0.95rem; font-weight: 600;">Kali</span></div>
                </div>
                <div style="color: #fb7185; flex-shrink: 0;">
                    <x-heroicon-o-clock class="w-8 h-8" />
                </div>
            </div>

            {{-- Total Akumulasi Jam --}}
            <div class="bg-white dark:bg-gray-900" style="border: 1.5px solid #0ea5e9; border-radius: 1rem; padding: 1.15rem 1.25rem; display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;">
                <div>
                    <div style="font-size: 0.8rem; font-weight: 600; color: #38bdf8; letter-spacing: 0.01em; white-space: nowrap;">Total Akumulasi Jam</div>
                    <div style="font-size: 1.55rem; font-weight: 800; color: #38bdf8; line-height: 1.2; margin-top: 0.35rem; white-space: nowrap;">{{ $data['total_jam_kerja'] }} <span style="font-size: 0.9rem; font-weight: 600;">Jam</span></div>
                </div>
                <div style="color: #38bdf8; flex-shrink: 0;">
                    <x-heroicon-o-bolt class="w-8 h-8" />
                </div>
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
                @if($data['is_all_employees'])
                    <table class="w-full text-xs text-left border-collapse">
                        <thead class="text-[11px] text-gray-700 uppercase bg-gray-50 dark:bg-gray-900 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th rowspan="2" class="px-3 py-2.5 border-r border-gray-200 dark:border-gray-700 text-center" style="min-width: 40px;">No</th>
                                <th rowspan="2" class="px-3 py-2.5 border-r border-gray-200 dark:border-gray-700 sticky left-0 bg-gray-50 dark:bg-gray-900 z-10" style="min-width: 170px;">Pegawai</th>
                                @foreach($data['dates'] as $d)
                                    <th class="px-2 py-2 border-r border-gray-200 dark:border-gray-700 text-center whitespace-nowrap {{ $d['is_holiday'] || $d['is_sunday'] ? 'bg-red-50/70 dark:bg-red-950/30 text-red-500' : '' }}" style="min-width: 68px;" title="{{ $d['holiday_name'] ?? $d['date_str'] }}">
                                        <div class="font-bold">{{ $d['day_num'] }} {{ $d['month_short'] }}</div>
                                        <div class="text-[10px] font-normal opacity-80">{{ $d['day_short'] }}</div>
                                    </th>
                                @endforeach
                                <th colspan="4" class="px-2 py-1.5 text-center border-b border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 font-bold">Rekap</th>
                            </tr>
                            <tr>
                                @foreach($data['dates'] as $d)
                                    <th class="px-1 py-1 border-r border-gray-200 dark:border-gray-700 text-center text-[10px] font-medium text-gray-400 {{ $d['is_holiday'] || $d['is_sunday'] ? 'bg-red-50/70 dark:bg-red-950/30' : '' }}">
                                        In / Out
                                    </th>
                                @endforeach
                                <th class="px-2.5 py-1.5 text-center border-r border-gray-200 dark:border-gray-700 bg-amber-50/50 dark:bg-amber-950/20 text-amber-600 dark:text-amber-400 font-bold" style="min-width: 36px;" title="Sakit">S</th>
                                <th class="px-2.5 py-1.5 text-center border-r border-gray-200 dark:border-gray-700 bg-sky-50/50 dark:bg-sky-950/20 text-sky-600 dark:text-sky-400 font-bold" style="min-width: 36px;" title="Izin / Cuti">I</th>
                                <th class="px-2.5 py-1.5 text-center border-r border-gray-200 dark:border-gray-700 bg-rose-50/50 dark:bg-rose-950/20 text-rose-600 dark:text-rose-400 font-bold" style="min-width: 36px;" title="Alpha">A</th>
                                <th class="px-2.5 py-1.5 text-center bg-orange-50/50 dark:bg-orange-950/20 text-orange-600 dark:text-orange-400 font-bold" style="min-width: 36px;" title="Terlambat">T</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($data['matrix_rows'] as $idx => $row)
                                @php $emp = $row['user']; @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/40">
                                    <td class="px-3 py-2.5 border-r border-gray-200 dark:border-gray-700 text-center text-gray-500">{{ $idx + 1 }}</td>
                                    <td class="px-3 py-2.5 border-r border-gray-200 dark:border-gray-700 sticky left-0 bg-white dark:bg-gray-800 z-10">
                                        <div class="font-semibold text-gray-900 dark:text-white whitespace-nowrap">{{ $emp->name }}</div>
                                        <div class="text-[10px] text-gray-400 whitespace-nowrap">{{ $emp->nik ?? '-' }} &bull; {{ $emp->department->name ?? '-' }}</div>
                                    </td>
                                    @foreach($data['dates'] as $d)
                                        @php $cell = $row['cells'][$d['date_str']] ?? ['type' => 'off', 'label' => '-']; @endphp
                                        <td class="px-1.5 py-2 border-r border-gray-200 dark:border-gray-700 text-center whitespace-nowrap {{ $d['is_holiday'] || $d['is_sunday'] ? 'bg-red-50/30 dark:bg-red-950/15' : '' }}">
                                            @if($cell['type'] === 'present')
                                                <div class="font-mono text-[11px] font-semibold {{ $cell['is_late'] ? 'text-rose-500' : 'text-emerald-500' }}">
                                                    {{ $cell['in'] }}
                                                </div>
                                                <div class="font-mono text-[11px] text-gray-400">
                                                    {{ $cell['out'] }}
                                                </div>
                                            @elseif($cell['type'] === 'sakit')
                                                <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">S</span>
                                            @elseif($cell['type'] === 'izin')
                                                <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300">I</span>
                                            @elseif($cell['type'] === 'alpha')
                                                <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300">A</span>
                                            @else
                                                <span class="text-gray-400 dark:text-gray-600">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="px-2 py-2 border-r border-gray-200 dark:border-gray-700 text-center font-bold text-amber-600 dark:text-amber-400">{{ $row['rekap']['S'] }}</td>
                                    <td class="px-2 py-2 border-r border-gray-200 dark:border-gray-700 text-center font-bold text-sky-600 dark:text-sky-400">{{ $row['rekap']['I'] }}</td>
                                    <td class="px-2 py-2 border-r border-gray-200 dark:border-gray-700 text-center font-bold text-rose-600 dark:text-rose-400">{{ $row['rekap']['A'] }}</td>
                                    <td class="px-2 py-2 text-center font-bold text-orange-600 dark:text-orange-400">{{ $row['rekap']['T'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($data['dates']) + 6 }}" class="px-4 py-8 text-center text-gray-500">
                                        Tidak ada data pegawai pada filter ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                @else
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
                @endif
            </div>

            @if($data['is_all_employees'])
                <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/40 flex flex-wrap items-center gap-4 text-[11px] text-gray-500 dark:text-gray-400">
                    <span><strong class="text-emerald-500">Hijau</strong> = Tepat Waktu (In / Out)</span>
                    <span><strong class="text-rose-500">Merah</strong> = Terlambat</span>
                    <span><strong class="text-amber-500">S</strong> = Sakit</span>
                    <span><strong class="text-sky-500">I</strong> = Izin / Cuti</span>
                    <span><strong class="text-rose-500">A</strong> = Alpha (Tidak Hadir)</span>
                    <span><strong class="text-orange-500">T</strong> = Total Terlambat</span>
                </div>
            @endif
        </div>
    </div>

    <style>
        .report-metrics-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1rem;
        }
        @media (min-width: 640px) {
            .report-metrics-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (min-width: 1024px) {
            .report-metrics-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }
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
