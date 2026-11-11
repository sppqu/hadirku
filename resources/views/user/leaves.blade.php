@extends('layouts.mobile', ['title' => 'Pengajuan Izin & Cuti - HadirKU'])

@section('content')
<div class="relative bg-slate-50 min-h-full">
    <!-- Header Section -->
    <div class="bg-app-green text-white px-5 pt-4 pb-8 rounded-b-[36px] shadow-sm relative">
        <div class="flex items-center justify-between mb-2">
            <a href="{{ route('user.dashboard') }}" class="text-white hover:text-emerald-100 p-1.5 -ml-1.5 rounded-lg active:scale-95 transition">
                <i class="fa-solid fa-arrow-left text-lg"></i>
            </a>
            <h1 class="text-base font-bold tracking-tight">Pengajuan Izin & Cuti</h1>
            <div class="w-7"></div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="px-5 -mt-4 relative z-10 space-y-4">
        
        <!-- Form Pengajuan Baru -->
        <div class="bg-white rounded-3xl p-5 shadow-[0_4px_20px_rgba(0,0,0,0.04)] border border-slate-100">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                <i class="fa-solid fa-file-pen text-emerald-600"></i>
                <span>Buat Pengajuan Baru</span>
            </h3>

            @if($errors->any())
                <div class="mb-3 p-2.5 bg-red-50 text-red-700 text-xs rounded-xl font-medium">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('user.leaves.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Jenis Pengajuan</label>
                    <select name="type" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="izin">Izin Tidak Masuk</option>
                        <option value="sakit">Sakit (Wajib Lampiran)</option>
                        <option value="cuti">Cuti Tahunan</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Mulai Tanggal</label>
                        <input type="date" name="start_date" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Sampai Tanggal</label>
                        <input type="date" name="end_date" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Alasan / Keterangan</label>
                    <textarea name="reason" rows="2" required placeholder="Tuliskan alasan pengajuan..." class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Lampiran Dokumen / Surat Dokter (Opsional)</label>
                    <input type="file" name="attachment" accept="image/*,.pdf" class="w-full text-xs text-slate-500 file:mr-2 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                </div>

                <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-md shadow-emerald-600/30 transition flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>Ajukan Sekarang</span>
                </button>
            </form>
        </div>

        <!-- Riwayat Pengajuan -->
        <div class="bg-white rounded-3xl p-5 border border-slate-100 shadow-sm space-y-3">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                <i class="fa-solid fa-clock-rotate-left text-emerald-600"></i>
                <span>Riwayat Pengajuan Anda</span>
            </h3>

            @forelse($leaves as $leave)
                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-800 uppercase">{{ $leave->type }}</span>
                        @if($leave->status === 'approved')
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">
                                ✓ Disetujui
                            </span>
                        @elseif($leave->status === 'rejected')
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-800">
                                ✗ Ditolak
                            </span>
                        @else
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800">
                                ⏳ Menunggu
                            </span>
                        @endif
                    </div>

                    <div class="text-xs text-slate-600">
                        <div class="text-[11px] text-slate-400">
                            {{ \Carbon\Carbon::parse($leave->start_date)->translatedFormat('d M Y') }} s/d {{ \Carbon\Carbon::parse($leave->end_date)->translatedFormat('d M Y') }}
                        </div>
                        <p class="mt-1 font-medium">{{ $leave->reason }}</p>
                    </div>

                    @if($leave->admin_notes)
                        <div class="p-2 bg-white rounded-lg border border-slate-200 text-[11px] text-slate-500">
                            <strong>Catatan Admin:</strong> {{ $leave->admin_notes }}
                        </div>
                    @endif
                </div>
            @empty
                <div class="py-6 text-center text-slate-400 text-xs">
                    Belum ada riwayat pengajuan izin atau cuti.
                </div>
            @endforelse
        </div>

    </div>
</div>
@endsection
