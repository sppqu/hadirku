<?php

namespace App\Filament\Pages;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Leave;
use App\Models\User;
use Carbon\Carbon;
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

        return [
            'records' => $records,
            'total_hadir' => $totalHadir,
            'total_tepat_waktu' => $totalTepatWaktu,
            'total_terlambat' => $totalTerlambat,
            'total_jam_kerja' => $totalJamKerja,
        ];
    }
}
