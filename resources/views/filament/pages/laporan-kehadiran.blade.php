<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filter Card (Screen Only) --}}
        <div class="no-print">
            <x-filament::section>
                <form wire:submit="filter" class="space-y-4">
                    {{ $this->form }}

                    <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                        <div class="flex items-center gap-3">
                            <x-filament::button type="submit" icon="heroicon-m-funnel">
                                Terapkan Filter
                            </x-filament::button>

                            <x-filament::button 
                                type="button" 
                                color="warning" 
                                icon="heroicon-m-printer" 
                                onclick="window.print()"
                                class="btn-print-action cursor-pointer"
                                style="background-color: #ea580c !important; color: #ffffff !important; border: 1px solid #c2410c !important;"
                            >
                                Cetak
                            </x-filament::button>
                        </div>

                        <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
                            <x-filament::badge color="success" size="sm">
                                Format: Landscape
                            </x-filament::badge>
                            <span>Cetak langsung dengan Kop Resmi Kampus & tabel rekapitulasi</span>
                        </div>
                    </div>
                </form>
            </x-filament::section>
        </div>

        {{-- Metrics Summary (Screen Only) --}}
        @php
            $data = $this->reportData;
            $campus = $data['campus'] ?? null;
            $campusName = $campus?->name ?? 'UNIVERSITAS HADIRKU';
            $campusFoundation = $campus?->foundation ?? null;
            $campusCode = $campus?->campus_code ?? '041001';
            $campusRector = $campus?->rector_name ?? 'Prof. Dr. H. Irwan Santoso, M.Kom.';
            $campusAccreditation = $campus?->accreditation ?? 'Unggul';
            $campusEmail = $campus?->email ?? 'info@universitas.ac.id';
            $campusPhone = $campus?->phone ?? '021-78901234';
            $campusWebsite = $campus?->website ?? 'https://hadirku.ac.id';
            $campusAddress = $campus?->address ?? 'Jl. Kampus Merdeka No. 100';
            $campusCity = $campus?->city ?? 'Jakarta Selatan';
            $campusProvince = $campus?->province ?? 'DKI Jakarta';
            $campusPostalCode = $campus?->postal_code ?? '12190';
            $campusLogo = $campus?->logo_url ?? asset('images/logo.png');
        @endphp

        <div class="report-metrics-grid no-print">
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

        {{-- Printable Formal Report Container --}}
        <div class="report-paper bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm print-area">
            
            {{-- Kop Surat Resmi Kampus --}}
            <div class="report-kop-wrapper">
                <div class="report-kop-content">
                    <div class="report-kop-logo-box">
                        <img src="{{ $campusLogo }}" alt="Logo {{ $campusName }}" class="report-kop-logo" />
                    </div>
                    <div class="report-kop-details">
                        <div class="kop-institution">{{ !empty($campusFoundation) ? strtoupper($campusFoundation) : 'KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI' }}</div>
                        <div class="kop-campus-name">{{ strtoupper($campusName) }}</div>
                        <div class="kop-accreditation">
                            @if($campusCode)
                                <span>Kode PT: {{ $campusCode }}</span>
                            @endif
                            @if($campusCode && $campusAccreditation)
                                <span>&bull;</span>
                            @endif
                            @if($campusAccreditation)
                                <span>Terakreditasi: {{ $campusAccreditation }}</span>
                            @endif
                        </div>
                        <div class="kop-address">
                            {{ $campusAddress }}{{ $campusCity ? ', ' . $campusCity : '' }}{{ $campusProvince ? ', ' . $campusProvince : '' }} {{ $campusPostalCode }}
                        </div>
                        <div class="kop-contacts">
                            @if($campusPhone)
                                <span>Telp: {{ $campusPhone }}</span>
                            @endif
                            @if($campusPhone && $campusEmail)
                                <span>|</span>
                            @endif
                            @if($campusEmail)
                                <span>Email: {{ $campusEmail }}</span>
                            @endif
                            @if(($campusPhone || $campusEmail) && $campusWebsite)
                                <span>|</span>
                            @endif
                            @if($campusWebsite)
                                <span>Laman: {{ $campusWebsite }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Garis Pembatas Kop Surat Resmi (Double Line) --}}
                <div class="report-kop-separator">
                    <div class="separator-thick"></div>
                    <div class="separator-thin"></div>
                </div>
            </div>

            {{-- Judul Laporan & Metadata Dokumen --}}
            <div class="report-meta-wrapper">
                <h1 class="report-doc-title">
                    @if($data['is_all_employees'])
                        LAPORAN REKAPITULASI KEHADIRAN PEGAWAI
                    @else
                        LAPORAN DETAIL KEHADIRAN PEGAWAI
                    @endif
                </h1>

                <div class="report-meta-box">
                    <div class="meta-col">
                        <div class="meta-entry">
                            <span class="meta-term">Periode</span>
                            <span class="meta-colon">:</span>
                            <span class="meta-desc font-bold">
                                {{ \Carbon\Carbon::parse($this->start_date)->locale('id')->translatedFormat('d F Y') }}
                                s/d
                                {{ \Carbon\Carbon::parse($this->end_date)->locale('id')->translatedFormat('d F Y') }}
                            </span>
                        </div>
                        <div class="meta-entry">
                            <span class="meta-term">Unit Kerja / Dept</span>
                            <span class="meta-colon">:</span>
                            <span class="meta-desc">
                                {{ $data['selected_department'] ? $data['selected_department']->name : 'Semua Unit Kerja / Departemen' }}
                            </span>
                        </div>
                        @if(!$data['is_all_employees'] && !empty($data['selected_user']))
                        <div class="meta-entry">
                            <span class="meta-term">Nama Pegawai</span>
                            <span class="meta-colon">:</span>
                            <span class="meta-desc font-bold">
                                {{ $data['selected_user']->name }} (NIK: {{ $data['selected_user']->nik ?? '-' }})
                            </span>
                        </div>
                        @endif
                    </div>
                    <div class="meta-col">
                        <div class="meta-entry">
                            <span class="meta-term">Tanggal Cetak</span>
                            <span class="meta-colon">:</span>
                            <span class="meta-desc">
                                {{ \Carbon\Carbon::now('Asia/Jakarta')->locale('id')->translatedFormat('d F Y - H:i:s') }} WIB
                            </span>
                        </div>
                        <div class="meta-entry">
                            <span class="meta-term">Dicetak Oleh</span>
                            <span class="meta-colon">:</span>
                            <span class="meta-desc">
                                {{ auth()->user()->name ?? 'Administrator' }}
                            </span>
                        </div>
                        <div class="meta-entry">
                            <span class="meta-term">Status Dokumen</span>
                            <span class="meta-colon">:</span>
                            <span class="meta-desc font-semibold text-emerald-600 dark:text-emerald-400 print:text-black">
                                Sah / Terverifikasi Sistem
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Ringkasan Presensi (Tabel Ringkas Versi Laporan) --}}
            <div class="report-summary-container">
                <table class="report-summary-table">
                    <thead>
                        <tr>
                            <th>Total Hari Hadir</th>
                            <th>Tepat Waktu</th>
                            <th>Terlambat</th>
                            <th>Total Akumulasi Jam Kerja</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>{{ $data['total_hadir'] }}</strong> Hari</td>
                            <td><strong>{{ $data['total_tepat_waktu'] }}</strong> Kali</td>
                            <td><strong>{{ $data['total_terlambat'] }}</strong> Kali</td>
                            <td><strong>{{ $data['total_jam_kerja'] }}</strong> Jam</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Tabel Data Kehadiran --}}
            <div class="report-table-scroll overflow-x-auto">
                @if($data['is_all_employees'])
                    <table class="report-table matrix-table">
                        <thead>
                            <tr>
                                <th rowspan="2" class="col-no">No</th>
                                <th rowspan="2" class="col-pegawai">Pegawai</th>
                                @foreach($data['dates'] as $d)
                                    <th class="col-date {{ $d['is_holiday'] || $d['is_sunday'] ? 'day-off-header' : '' }}" title="{{ $d['holiday_name'] ?? $d['date_str'] }}">
                                        <div class="day-num">{{ $d['day_num'] }} {{ $d['month_short'] }}</div>
                                        <div class="day-name">{{ $d['day_short'] }}</div>
                                    </th>
                                @endforeach
                                <th colspan="4" class="col-rekap-title">Rekap</th>
                            </tr>
                            <tr>
                                @foreach($data['dates'] as $d)
                                    <th class="col-inout {{ $d['is_holiday'] || $d['is_sunday'] ? 'day-off-header' : '' }}">
                                        In / Out
                                    </th>
                                @endforeach
                                <th class="col-rekap-sub rekap-s" title="Sakit">S</th>
                                <th class="col-rekap-sub rekap-i" title="Izin / Cuti">I</th>
                                <th class="col-rekap-sub rekap-a" title="Alpha">A</th>
                                <th class="col-rekap-sub rekap-t" title="Terlambat">T</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['matrix_rows'] as $idx => $row)
                                @php $emp = $row['user']; @endphp
                                <tr>
                                    <td class="cell-no">{{ $idx + 1 }}</td>
                                    <td class="cell-pegawai">
                                        <div class="emp-name">{{ $emp->name }}</div>
                                        <div class="emp-info">{{ $emp->nik ?? '-' }} &bull; {{ $emp->department->name ?? '-' }}</div>
                                    </td>
                                    @foreach($data['dates'] as $d)
                                        @php $cell = $row['cells'][$d['date_str']] ?? ['type' => 'off', 'label' => '-']; @endphp
                                        <td class="cell-date {{ $d['is_holiday'] || $d['is_sunday'] ? 'day-off-cell' : '' }}">
                                            @if($cell['type'] === 'present')
                                                <div class="cell-time cell-in {{ $cell['is_late'] ? 'cell-late' : 'cell-ontime' }}">
                                                    {{ $cell['in'] }}@if($cell['is_late'])<span class="late-mark">*</span>@endif
                                                </div>
                                                <div class="cell-time cell-out">
                                                    {{ $cell['out'] }}
                                                </div>
                                            @elseif($cell['type'] === 'sakit')
                                                <span class="badge-sakit">S</span>
                                            @elseif($cell['type'] === 'izin')
                                                <span class="badge-izin">I</span>
                                            @elseif($cell['type'] === 'alpha')
                                                <span class="badge-alpha">A</span>
                                            @else
                                                <span class="cell-dash">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="cell-rekap font-bold text-amber-700 dark:text-amber-400 print:text-black">{{ $row['rekap']['S'] }}</td>
                                    <td class="cell-rekap font-bold text-sky-700 dark:text-sky-400 print:text-black">{{ $row['rekap']['I'] }}</td>
                                    <td class="cell-rekap font-bold text-rose-700 dark:text-rose-400 print:text-black">{{ $row['rekap']['A'] }}</td>
                                    <td class="cell-rekap font-bold text-orange-700 dark:text-orange-400 print:text-black">{{ $row['rekap']['T'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($data['dates']) + 6 }}" class="cell-empty">
                                        Tidak ada data pegawai pada filter ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                @else
                    <table class="report-table single-table">
                        <thead>
                            <tr>
                                <th class="col-no">No</th>
                                <th>Tanggal</th>
                                <th>NIK / NIP</th>
                                <th>Nama Pegawai</th>
                                <th>Departemen</th>
                                <th class="text-center">Jam Masuk</th>
                                <th class="text-center">Jam Pulang</th>
                                <th class="text-center">Durasi</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Lokasi & Presensi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['records'] as $idx => $record)
                                <tr>
                                    <td class="cell-no">{{ $idx + 1 }}</td>
                                    <td class="whitespace-nowrap font-medium">{{ \Carbon\Carbon::parse($record->date)->locale('id')->translatedFormat('d M Y') }}</td>
                                    <td class="font-mono text-xs">{{ $record->user->nik ?? '-' }}</td>
                                    <td class="font-semibold">{{ $record->user->name }}</td>
                                    <td>{{ $record->user->department->name ?? '-' }}</td>
                                    <td class="text-center font-mono font-medium">
                                        {{ $record->check_in ? date('H:i', strtotime($record->check_in)) : '-' }}
                                    </td>
                                    <td class="text-center font-mono">
                                        {{ $record->check_out ? date('H:i', strtotime($record->check_out)) : '-' }}
                                    </td>
                                    <td class="text-center font-mono font-medium">{{ $record->formatted_working_hours }}</td>
                                    <td class="text-center">
                                        @if($record->check_in_status === 'late')
                                            <span class="status-badge status-late">Terlambat</span>
                                        @else
                                            <span class="status-badge status-ontime">Tepat Waktu</span>
                                        @endif
                                    </td>
                                    <td class="text-center text-xs">
                                        {{ $record->check_in_distance ? $record->check_in_distance . ' m' : 'Kantor' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="cell-empty">
                                        Tidak ada data kehadiran pada periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                @endif
            </div>

            {{-- Keterangan / Legenda Status --}}
            @if($data['is_all_employees'])
                <div class="report-legend-box">
                    <span class="font-bold">Keterangan:</span>
                    <span class="legend-item"><span class="legend-dot ontime"></span> <strong>Tepat Waktu</strong> (Masuk / Pulang)</span>
                    <span class="legend-item"><span class="legend-dot late"></span> <strong>Terlambat (*)</strong></span>
                    <span class="legend-item"><span class="legend-badge badge-s">S</span> <strong>Sakit</strong></span>
                    <span class="legend-item"><span class="legend-badge badge-i">I</span> <strong>Izin / Cuti</strong></span>
                    <span class="legend-item"><span class="legend-badge badge-a">A</span> <strong>Alpha (Tidak Hadir)</strong></span>
                    <span class="legend-item"><span class="legend-badge badge-t">T</span> <strong>Total Terlambat</strong></span>
                    <span class="legend-item"><span class="legend-dash">-</span> <strong>Libur / Akhir Pekan</strong></span>
                </div>
            @endif

            {{-- Lembar Pengesahan / Kolom Tanda Tangan Resmi --}}
            <div class="report-signatures-wrapper">
                <div class="sig-block sig-left">
                    <p class="sig-title">Mengetahui / Memeriksa,</p>
                    <p class="sig-position">Kepala Bagian Kepegawaian & SDM</p>
                    <div class="sig-space"></div>
                    <p class="sig-name">( ................................................................ )</p>
                    <p class="sig-nip">NIP. ................................................................</p>
                </div>
                <div class="sig-block sig-right">
                    <p class="sig-date">{{ $campusCity }}, {{ \Carbon\Carbon::now('Asia/Jakarta')->locale('id')->translatedFormat('d F Y') }}</p>
                    <p class="sig-position">Rektor / Pimpinan Perguruan Tinggi</p>
                    <div class="sig-space"></div>
                    <p class="sig-name"><strong>{{ $campusRector }}</strong></p>
                    <p class="sig-nip">NIP/NIDN. {{ $campusCode ? $campusCode : '................................................................' }}</p>
                </div>
            </div>

        </div>
    </div>

    {{-- Comprehensive Styling for Screen Preview & Landscape Official Report Printing --}}
    <style>
        /* Print Action Button (Orange color, high visibility in both light & dark themes) */
        .btn-print-action,
        button.btn-print-action,
        .btn-print-action.fi-btn {
            background-color: #ea580c !important;
            color: #ffffff !important;
            border: 1px solid #c2410c !important;
            font-weight: 600 !important;
            display: inline-flex !important;
            align-items: center !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
        }
        .btn-print-action:hover,
        button.btn-print-action:hover,
        .btn-print-action.fi-btn:hover {
            background-color: #c2410c !important;
            color: #ffffff !important;
        }
        .dark .btn-print-action,
        .dark button.btn-print-action,
        .dark .btn-print-action.fi-btn {
            background-color: #f97316 !important;
            color: #ffffff !important;
            border: 1px solid #ea580c !important;
        }
        .dark .btn-print-action:hover,
        .dark button.btn-print-action:hover,
        .dark .btn-print-action.fi-btn:hover {
            background-color: #ea580c !important;
        }

        /* Screen Metrics Grid */
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

        /* Report Paper Screen Base */
        .report-paper {
            padding: 1.5rem;
        }

        /* Kop Surat */
        .report-kop-wrapper {
            margin-bottom: 1.25rem;
        }
        .report-kop-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1.5rem;
            text-align: center;
            padding-bottom: 0.75rem;
        }
        .report-kop-logo-box {
            flex-shrink: 0;
            width: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .report-kop-logo {
            max-height: 80px;
            max-width: 80px;
            object-fit: contain;
        }
        .report-kop-details {
            flex: 1;
            min-width: 0;
            color: #111827;
        }
        .dark .report-kop-details {
            color: #f3f4f6;
        }
        .kop-institution {
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .kop-campus-name {
            font-size: 1.35rem;
            font-weight: 900;
            letter-spacing: 0.025em;
            line-height: 1.2;
            margin: 0.15rem 0;
        }
        .kop-accreditation {
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 0.2rem;
            opacity: 0.9;
        }
        .kop-address {
            font-size: 0.75rem;
            line-height: 1.35;
            opacity: 0.85;
        }
        .kop-contacts {
            font-size: 0.72rem;
            margin-top: 0.15rem;
            opacity: 0.8;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 0.4rem;
        }

        /* Kop Separator Double Line */
        .report-kop-separator {
            margin-top: 0.5rem;
        }
        .separator-thick {
            height: 2.5px;
            background-color: #111827;
            width: 100%;
        }
        .dark .separator-thick {
            background-color: #9ca3af;
        }
        .separator-thin {
            height: 1px;
            background-color: #111827;
            width: 100%;
            margin-top: 2px;
        }
        .dark .separator-thin {
            background-color: #9ca3af;
        }

        /* Document Title & Meta */
        .report-meta-wrapper {
            margin-bottom: 1.25rem;
        }
        .report-doc-title {
            text-align: center;
            font-size: 1.15rem;
            font-weight: 800;
            letter-spacing: 0.03em;
            color: #111827;
            margin-bottom: 0.85rem;
        }
        .dark .report-doc-title {
            color: #ffffff;
        }
        .report-meta-box {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            font-size: 0.8rem;
        }
        .dark .report-meta-box {
            background-color: #1e293b;
            border-color: #334155;
            color: #e2e8f0;
        }
        @media (min-width: 640px) {
            .report-meta-box {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 1.5rem;
            }
        }
        .meta-entry {
            display: flex;
            align-items: baseline;
            gap: 0.4rem;
            margin-bottom: 0.2rem;
        }
        .meta-term {
            width: 125px;
            flex-shrink: 0;
            font-weight: 600;
            color: #4b5563;
        }
        .dark .meta-term {
            color: #94a3b8;
        }
        .meta-colon {
            flex-shrink: 0;
            color: #6b7280;
        }
        .meta-desc {
            flex: 1;
            color: #111827;
        }
        .dark .meta-desc {
            color: #f1f5f9;
        }

        /* Summary Table */
        .report-summary-container {
            margin-bottom: 1.25rem;
        }
        .report-summary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8rem;
            text-align: center;
        }
        .report-summary-table th {
            background-color: #f1f5f9;
            color: #334155;
            padding: 0.5rem;
            font-weight: 700;
            border: 1px solid #cbd5e1;
        }
        .dark .report-summary-table th {
            background-color: #1e293b;
            color: #cbd5e1;
            border-color: #475569;
        }
        .report-summary-table td {
            padding: 0.6rem;
            border: 1px solid #cbd5e1;
            font-size: 0.95rem;
            color: #0f172a;
        }
        .dark .report-summary-table td {
            border-color: #475569;
            color: #f8fafc;
        }

        /* Main Report Table */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.75rem;
        }
        .report-table th, .report-table td {
            border: 1px solid #e2e8f0;
        }
        .dark .report-table th, .dark .report-table td {
            border-color: #334155;
        }
        .report-table th {
            background-color: #f8fafc;
            color: #1e293b;
            padding: 0.4rem 0.35rem;
            font-weight: 700;
            text-align: center;
        }
        .dark .report-table th {
            background-color: #0f172a;
            color: #f1f5f9;
        }
        .report-table td {
            padding: 0.35rem 0.3rem;
        }
        .matrix-table {
            font-size: 0.7rem;
        }
        .col-no {
            width: 32px;
            text-align: center;
        }
        .cell-no {
            text-align: center;
            font-weight: 600;
            color: #64748b;
        }
        .col-pegawai {
            min-width: 140px;
            max-width: 180px;
            text-align: left !important;
            padding-left: 0.5rem !important;
        }
        .cell-pegawai {
            text-align: left;
            padding-left: 0.5rem !important;
            white-space: nowrap;
        }
        .emp-name {
            font-weight: 700;
            color: #0f172a;
        }
        .dark .emp-name {
            color: #f8fafc;
        }
        .emp-info {
            font-size: 0.65rem;
            color: #64748b;
        }
        .dark .emp-info {
            color: #94a3b8;
        }
        .col-date {
            min-width: 48px;
            padding: 0.25rem 0.15rem !important;
            text-align: center;
        }
        .day-num {
            font-weight: 700;
            line-height: 1.1;
        }
        .day-name {
            font-size: 0.62rem;
            font-weight: 500;
            opacity: 0.85;
        }
        .day-off-header {
            background-color: #fee2e2 !important;
            color: #b91c1c !important;
        }
        .dark .day-off-header {
            background-color: #450a0a !important;
            color: #fca5a5 !important;
        }
        .day-off-cell {
            background-color: #fef2f2;
        }
        .dark .day-off-cell {
            background-color: #270b0b;
        }
        .col-inout {
            font-size: 0.6rem;
            font-weight: 500;
            color: #64748b;
            padding: 0.15rem 0.1rem !important;
        }
        .col-rekap-title {
            background-color: #e2e8f0 !important;
            color: #1e293b !important;
        }
        .dark .col-rekap-title {
            background-color: #1e293b !important;
            color: #f1f5f9 !important;
        }
        .col-rekap-sub {
            width: 28px;
            padding: 0.25rem 0.15rem !important;
            font-weight: 800 !important;
        }
        .rekap-s { background-color: #fef3c7 !important; color: #b45309 !important; }
        .rekap-i { background-color: #e0f2fe !important; color: #0369a1 !important; }
        .rekap-a { background-color: #ffe4e6 !important; color: #be123c !important; }
        .rekap-t { background-color: #ffedd5 !important; color: #c2410c !important; }

        .cell-date {
            text-align: center;
            padding: 0.2rem 0.15rem !important;
            white-space: nowrap;
        }
        .cell-time {
            font-family: monospace;
            font-size: 0.68rem;
            line-height: 1.15;
        }
        .cell-ontime {
            color: #059669;
            font-weight: 600;
        }
        .cell-late {
            color: #dc2626;
            font-weight: 700;
        }
        .late-mark {
            color: #dc2626;
            font-weight: 900;
            margin-left: 1px;
        }
        .cell-out {
            color: #64748b;
        }
        .dark .cell-out {
            color: #94a3b8;
        }
        .badge-sakit, .badge-izin, .badge-alpha {
            display: inline-block;
            font-weight: 800;
            font-size: 0.65rem;
            padding: 0.1rem 0.35rem;
            border-radius: 0.25rem;
        }
        .badge-sakit { background-color: #fef3c7; color: #92400e; }
        .badge-izin { background-color: #e0f2fe; color: #075985; }
        .badge-alpha { background-color: #fee2e2; color: #991b1b; }
        .cell-dash {
            color: #94a3b8;
            font-size: 0.75rem;
        }
        .cell-rekap {
            text-align: center;
            font-size: 0.75rem;
            font-weight: 700;
        }
        .cell-empty {
            text-align: center;
            padding: 2rem !important;
            color: #64748b;
        }

        /* Single Table */
        .single-table th {
            padding: 0.6rem 0.5rem;
        }
        .single-table td {
            padding: 0.5rem;
        }
        .status-badge {
            display: inline-block;
            padding: 0.2rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.7rem;
            font-weight: 700;
        }
        .status-ontime {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-late {
            background-color: #fee2e2;
            color: #991b1b;
        }

        /* Legend */
        .report-legend-box {
            margin-top: 1rem;
            padding: 0.65rem 0.85rem;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            font-size: 0.72rem;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.75rem;
            color: #334155;
        }
        .dark .report-legend-box {
            background-color: #1e293b;
            border-color: #334155;
            color: #cbd5e1;
        }
        .legend-item {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }
        .legend-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }
        .legend-dot.ontime { background-color: #10b981; }
        .legend-dot.late { background-color: #ef4444; }
        .legend-badge {
            display: inline-block;
            width: 18px;
            height: 18px;
            line-height: 18px;
            text-align: center;
            border-radius: 0.25rem;
            font-size: 0.65rem;
            font-weight: 800;
        }
        .badge-s { background-color: #fef3c7; color: #92400e; }
        .badge-i { background-color: #e0f2fe; color: #075985; }
        .badge-a { background-color: #fee2e2; color: #991b1b; }
        .badge-t { background-color: #ffedd5; color: #9a3412; }
        .legend-dash { color: #94a3b8; font-weight: bold; }

        /* Signatures */
        .report-signatures-wrapper {
            margin-top: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 2rem;
            page-break-inside: avoid;
        }
        .sig-block {
            width: 280px;
            font-size: 0.8rem;
            color: #0f172a;
        }
        .dark .sig-block {
            color: #f1f5f9;
        }
        .sig-left {
            text-align: left;
        }
        .sig-right {
            text-align: right;
        }
        .sig-title, .sig-date {
            margin-bottom: 0.15rem;
        }
        .sig-position {
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .sig-space {
            height: 65px;
        }
        .sig-name {
            font-size: 0.82rem;
            line-height: 1.2;
        }
        .sig-nip {
            font-size: 0.72rem;
            color: #64748b;
            margin-top: 0.2rem;
        }
        .dark .sig-nip {
            color: #94a3b8;
        }

        /* ==========================================================================
           PRINT STYLES (LANDSCAPE ORIENTATION & FORMAL REPORT FORMAT)
           ========================================================================== */
        @media print {
            /* Force landscape paper size with clean standard margins */
            @page {
                size: landscape;
                margin: 8mm 10mm 10mm 10mm;
            }

            /* Reset EVERYTHING to pure white background and black text (removes all gray tints & dark mode backgrounds) */
            *, *::before, *::after,
            html, body,
            html.dark, body.dark,
            .dark, .dark * {
                background: transparent !important;
                background-color: transparent !important;
                background-image: none !important;
                color: #000000 !important;
                box-shadow: none !important;
                text-shadow: none !important;
                filter: none !important;
            }

            html, body {
                background: #ffffff !important;
                background-color: #ffffff !important;
            }

            /* Hide all Filament admin navigation chrome & web filters */
            aside,
            nav,
            header,
            footer,
            .fi-topbar,
            .fi-sidebar,
            .fi-header,
            .fi-page-header,
            .fi-breadcrumbs,
            .fi-form,
            form,
            .no-print,
            .report-metrics-grid,
            .fi-notifications,
            .fi-modal,
            .fi-dropdown {
                display: none !important;
            }

            /* Reset all root containers to prevent overflow truncation & blank pages */
            .fi-body,
            .fi-layout,
            .fi-main-ctn,
            .fi-main,
            .fi-page,
            .fi-page-content,
            .space-y-6 {
                margin: 0 !important;
                padding: 0 !important;
                background: transparent !important;
                background-color: transparent !important;
                color: #000000 !important;
                width: 100% !important;
                max-width: 100% !important;
                height: auto !important;
                min-height: auto !important;
                overflow: visible !important;
                box-shadow: none !important;
                border: none !important;
                font-family: Arial, Helvetica, sans-serif !important;
            }

            /* Hide everything inside space-y-6 except print-area */
            .space-y-6 > *:not(.print-area) {
                display: none !important;
            }

            /* Style Printable Report Paper - Clean White Paper Without Shadows/Borders */
            .print-area, .report-paper {
                display: block !important;
                position: static !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                background: transparent !important;
                background-color: transparent !important;
                color: #000000 !important;
                overflow: visible !important;
            }

            /* Kop Surat in Print */
            .report-kop-wrapper {
                margin-bottom: 8px !important;
                page-break-inside: avoid !important;
            }
            .report-kop-content {
                gap: 16px !important;
                padding-bottom: 6px !important;
            }
            .report-kop-logo-box {
                width: 70px !important;
                background: transparent !important;
            }
            .report-kop-logo {
                max-height: 65px !important;
                max-width: 65px !important;
                background: transparent !important;
            }
            .report-kop-details {
                color: #000000 !important;
            }
            .kop-institution {
                font-size: 8.5pt !important;
                color: #000000 !important;
            }
            .kop-campus-name {
                font-size: 13.5pt !important;
                color: #000000 !important;
                margin: 1px 0 !important;
            }
            .kop-accreditation, .kop-address, .kop-contacts {
                font-size: 7.5pt !important;
                color: #222222 !important;
            }
            .separator-thick {
                height: 2.5px !important;
                background-color: #000000 !important;
                background: #000000 !important;
            }
            .separator-thin {
                height: 1px !important;
                background-color: #000000 !important;
                background: #000000 !important;
                margin-top: 1.5px !important;
            }

            /* Document Title & Meta in Print */
            .report-meta-wrapper {
                margin-bottom: 8px !important;
                page-break-inside: avoid !important;
            }
            .report-doc-title {
                font-size: 11pt !important;
                color: #000000 !important;
                margin-bottom: 6px !important;
            }
            .report-meta-box {
                background: transparent !important;
                background-color: transparent !important;
                border: 1px solid #000000 !important;
                border-radius: 0 !important;
                padding: 4px 8px !important;
                font-size: 7.5pt !important;
                color: #000000 !important;
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                gap: 10px !important;
            }
            .meta-term {
                width: 95px !important;
                color: #000000 !important;
            }
            .meta-desc {
                color: #000000 !important;
            }

            /* Summary Table in Print - Clean White */
            .report-summary-container {
                margin-bottom: 8px !important;
                page-break-inside: avoid !important;
            }
            .report-summary-table {
                font-size: 7.5pt !important;
                background: transparent !important;
            }
            .report-summary-table th {
                background: transparent !important;
                background-color: transparent !important;
                color: #000000 !important;
                border: 1px solid #000000 !important;
                padding: 3px 6px !important;
            }
            .report-summary-table td {
                background: transparent !important;
                background-color: transparent !important;
                color: #000000 !important;
                border: 1px solid #000000 !important;
                padding: 4px 6px !important;
                font-size: 8.5pt !important;
            }

            /* Main Tables in Print - Pure White Background with Clean Black Borders */
            .report-table-scroll, .overflow-x-auto {
                overflow: visible !important;
                width: 100% !important;
                background: transparent !important;
            }
            .report-table {
                width: 100% !important;
                border-collapse: collapse !important;
                table-layout: auto !important;
                background: transparent !important;
            }
            .matrix-table {
                font-size: 6.8pt !important;
                line-height: 1.15 !important;
            }
            .report-table th, .report-table td {
                border: 1px solid #000000 !important;
                color: #000000 !important;
                background: transparent !important;
                background-color: transparent !important;
            }
            .report-table th {
                color: #000000 !important;
                padding: 2.5px 1.5px !important;
                font-weight: 700 !important;
            }
            .report-table td {
                padding: 2px 1.5px !important;
            }

            /* Multi-page table headers */
            thead {
                display: table-header-group !important;
            }
            tr {
                page-break-inside: avoid !important;
            }

            /* Column sizing in matrix print */
            .col-no {
                width: 22px !important;
            }
            .col-pegawai {
                width: 120px !important;
                max-width: 120px !important;
                white-space: normal !important;
            }
            .emp-name {
                font-size: 7pt !important;
                color: #000000 !important;
            }
            .emp-info {
                font-size: 5.8pt !important;
                color: #333333 !important;
            }
            .col-date {
                min-width: auto !important;
                padding: 1.5px 0.5px !important;
            }
            .day-num {
                font-size: 6.5pt !important;
            }
            .day-name {
                font-size: 5.5pt !important;
            }
            .col-inout {
                font-size: 5.5pt !important;
            }
            .day-off-header, .day-off-cell {
                background: transparent !important;
                background-color: transparent !important;
                color: #000000 !important;
            }
            .cell-time {
                font-size: 6.2pt !important;
                line-height: 1.1 !important;
            }
            .cell-ontime {
                color: #000000 !important;
            }
            .cell-late {
                color: #dc2626 !important;
                font-weight: 800 !important;
            }
            .late-mark {
                color: #dc2626 !important;
                font-weight: 900 !important;
            }
            .cell-out {
                color: #444444 !important;
            }
            /* Plain symbols S / I / A without badge box or border */
            .badge-sakit, .badge-izin, .badge-alpha {
                border: none !important;
                background: transparent !important;
                background-color: transparent !important;
                color: #000000 !important;
                padding: 0 !important;
                margin: 0 !important;
                border-radius: 0 !important;
                display: inline !important;
                font-weight: 700 !important;
                font-size: 7.2pt !important;
                box-shadow: none !important;
            }
            .col-rekap-sub {
                width: 18px !important;
                padding: 1.5px 0.5px !important;
            }
            .rekap-s, .rekap-i, .rekap-a, .rekap-t {
                background: transparent !important;
                background-color: transparent !important;
                color: #000000 !important;
            }
            .cell-rekap {
                color: #000000 !important;
                font-size: 7pt !important;
            }

            /* Single employee table in print */
            .single-table {
                font-size: 7.5pt !important;
            }
            .single-table th, .single-table td {
                padding: 3px 5px !important;
            }
            .status-badge {
                border: none !important;
                background: transparent !important;
                background-color: transparent !important;
                color: #000000 !important;
                padding: 0 !important;
                border-radius: 0 !important;
                display: inline !important;
                font-weight: 600 !important;
                font-size: 7.5pt !important;
            }

            /* Sticky overrides in print */
            .sticky {
                position: static !important;
            }

            /* Legend in Print */
            .report-legend-box {
                margin-top: 8px !important;
                padding: 4px 6px !important;
                background: transparent !important;
                background-color: transparent !important;
                border: 1px solid #000000 !important;
                border-radius: 0 !important;
                font-size: 6.8pt !important;
                color: #000000 !important;
                page-break-inside: avoid !important;
            }
            .legend-dot {
                display: none !important;
            }
            .legend-badge {
                border: none !important;
                background: transparent !important;
                background-color: transparent !important;
                color: #000000 !important;
                width: auto !important;
                height: auto !important;
                line-height: normal !important;
                display: inline !important;
                font-weight: 700 !important;
                font-size: 7.2pt !important;
                padding: 0 !important;
            }

            /* Signatures in Print */
            .report-signatures-wrapper {
                margin-top: 18px !important;
                page-break-inside: avoid !important;
            }
            .sig-block {
                width: 250px !important;
                font-size: 7.5pt !important;
                color: #000000 !important;
            }
            .sig-space {
                height: 50px !important;
            }
            .sig-name {
                font-size: 7.8pt !important;
                color: #000000 !important;
            }
            .sig-nip {
                font-size: 6.8pt !important;
                color: #333333 !important;
            }
        }
    </style>
</x-filament-panels::page>