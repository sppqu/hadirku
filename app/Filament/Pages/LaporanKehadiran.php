<?php

namespace App\Filament\Pages;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;

class LaporanKehadiran extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?string $title = 'Laporan Kehadiran';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.laporan-kehadiran';

    public ?string $start_date = null;
    public ?string $end_date = null;
    public ?string $department_id = null;
    public ?string $user_id = null;

    public function mount(): void
    {
        $this->start_date = Carbon::now()->startOfMonth()->toDateString();
        $this->end_date = Carbon::now()->endOfMonth()->toDateString();
        $this->form->fill([
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(4)
                    ->schema([
                        DatePicker::make('start_date')
                            ->label('Mulai Tanggal')
                            ->required(),
                        DatePicker::make('end_date')
                            ->label('Sampai Tanggal')
                            ->required(),
                        Select::make('department_id')
                            ->label('Departemen')
                            ->options(Department::pluck('name', 'id'))
                            ->placeholder('Semua Departemen'),
                        Select::make('user_id')
                            ->label('Pegawai')
                            ->options(User::where('role', 'pegawai')->pluck('name', 'id'))
                            ->placeholder('Semua Pegawai'),
                    ]),
            ]);
    }

    public function filter(): void
    {
        $data = $this->form->getState();
        $this->start_date = $data['start_date'];
        $this->end_date = $data['end_date'];
        $this->department_id = $data['department_id'];
        $this->user_id = $data['user_id'];
    }

    public function getReportDataProperty(): array
    {
        $query = Attendance::with(['user.department', 'office'])
            ->whereBetween('date', [$this->start_date, $this->end_date]);

        if ($this->user_id) {
            $query->where('user_id', $this->user_id);
        }

        if ($this->department_id) {
            $query->whereHas('user', function ($q) {
                $q->where('department_id', $this->department_id);
            });
        }

        $records = $query->orderBy('date', 'desc')->get();

        $totalHadir = $records->whereNotNull('check_in')->count();
        $totalTepatWaktu = $records->where('check_in_status', 'on_time')->count();
        $totalTerlambat = $records->where('check_in_status', 'late')->count();
        $totalMenitKerja = $records->sum('working_minutes');
        $totalJamKerja = sprintf('%02d:%02d', floor($totalMenitKerja / 60), $totalMenitKerja % 60);

        $isAllEmployees = empty($this->user_id);
        $dates = [];
        $matrixRows = [];

        if ($isAllEmployees) {
            $start = Carbon::parse($this->start_date, 'Asia/Jakarta')->startOfDay();
            $end = Carbon::parse($this->end_date, 'Asia/Jakarta')->startOfDay();
            $today = Carbon::today('Asia/Jakarta');

            $holidays = Holiday::where('is_active', true)
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->get()
                ->keyBy(fn ($h) => Carbon::parse($h->date)->toDateString());

            $shortDays = User::dayShortLabels();
            foreach (CarbonPeriod::create($start, $end) as $dt) {
                $dateStr = $dt->toDateString();
                $hol = $holidays->get($dateStr);
                $dates[] = [
                    'date_str' => $dateStr,
                    'day_num' => $dt->format('d'),
                    'month_short' => $dt->format('M'),
                    'day_short' => $shortDays[$dt->dayOfWeekIso] ?? $dt->format('D'),
                    'is_sunday' => $dt->isSunday(),
                    'is_holiday' => (bool) $hol,
                    'holiday_name' => $hol?->name,
                    'is_future' => $dt->gt($today),
                    'carbon' => $dt->copy(),
                ];
            }

            $employeesQuery = User::with('department')
                ->where('role', 'pegawai')
                ->where('is_active', true)
                ->orderBy('name');

            if ($this->department_id) {
                $employeesQuery->where('department_id', $this->department_id);
            }

            $employees = $employeesQuery->get();

            // Index attendances: [user_id][Y-m-d] => Attendance
            $attMap = [];
            foreach ($records as $rec) {
                $dStr = Carbon::parse($rec->date)->toDateString();
                $attMap[$rec->user_id][$dStr] = $rec;
            }

            // Approved leaves overlapping range
            $leaves = Leave::whereIn('user_id', $employees->pluck('id'))
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $end->toDateString())
                ->whereDate('end_date', '>=', $start->toDateString())
                ->get()
                ->groupBy('user_id');

            foreach ($employees as $emp) {
                $empLeaves = $leaves->get($emp->id, collect());
                $cells = [];
                $rekapS = 0;
                $rekapI = 0;
                $rekapA = 0;
                $rekapT = 0;

                foreach ($dates as $d) {
                    $dStr = $d['date_str'];
                    $att = $attMap[$emp->id][$dStr] ?? null;

                    if ($att && $att->check_in) {
                        $isLate = $att->check_in_status === 'late';
                        if ($isLate) {
                            $rekapT++;
                        }
                        $cells[$dStr] = [
                            'type' => 'present',
                            'in' => date('H:i', strtotime($att->check_in)),
                            'out' => $att->check_out ? date('H:i', strtotime($att->check_out)) : '--:--',
                            'is_late' => $isLate,
                        ];
                        continue;
                    }

                    // Cek izin / sakit yang disetujui pada tanggal ini
                    $matchedLeave = $empLeaves->first(function ($lv) use ($dStr) {
                        $s = Carbon::parse($lv->start_date)->toDateString();
                        $e = Carbon::parse($lv->end_date)->toDateString();
                        return $dStr >= $s && $dStr <= $e;
                    });

                    if ($matchedLeave) {
                        if ($matchedLeave->type === 'sakit') {
                            $rekapS++;
                            $cells[$dStr] = ['type' => 'sakit', 'label' => 'S'];
                        } else {
                            $rekapI++;
                            $cells[$dStr] = ['type' => 'izin', 'label' => 'I'];
                        }
                        continue;
                    }

                    // Cek hari libur atau bukan hari kerja pegawai
                    if ($d['is_holiday'] || ! $emp->isWorkingDay($d['carbon'])) {
                        $cells[$dStr] = ['type' => 'off', 'label' => '-'];
                        continue;
                    }

                    // Hari kerja belum lewat / masa depan
                    if ($d['is_future']) {
                        $cells[$dStr] = ['type' => 'future', 'label' => '-'];
                        continue;
                    }

                    // Hari kerja terlewat tanpa hadir & tanpa izin = Alpha
                    $rekapA++;
                    $cells[$dStr] = ['type' => 'alpha', 'label' => 'A'];
                }

                $matrixRows[] = [
                    'user' => $emp,
                    'cells' => $cells,
                    'rekap' => [
                        'S' => $rekapS,
                        'I' => $rekapI,
                        'A' => $rekapA,
                        'T' => $rekapT,
                    ],
                ];
            }
        }

        return [
            'records' => $records,
            'total_hadir' => $totalHadir,
            'total_tepat_waktu' => $totalTepatWaktu,
            'total_terlambat' => $totalTerlambat,
            'total_jam_kerja' => $totalJamKerja,
            'is_all_employees' => $isAllEmployees,
            'dates' => $dates,
            'matrix_rows' => $matrixRows,
        ];
    }
}
