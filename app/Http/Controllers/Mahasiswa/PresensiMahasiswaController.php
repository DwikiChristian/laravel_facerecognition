<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use App\Models\Jadwal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PresensiMahasiswaController extends Controller
{
    public function index()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        
        // Get all presensi for the authenticated mahasiswa with related data
        $presensi = Presensi::with(['jadwal.matakuliah', 'jadwal.dosen', 'jadwal.kelas'])
            ->where('mahasiswa_id', $mahasiswa->id)
            ->orderBy('waktu_presensi', 'desc')
            ->get();

        // Calculate statistics
        $totalPresensi = $presensi->count();
        $hadirCount = $presensi->where('status', 'hadir')->count();
        $telatCount = $presensi->where('status', 'telat')->count();
        $alphaCount = $presensi->where('status', 'alpha')->count();

        // Calculate attendance percentage (hadir + telat dianggap sebagai kehadiran)
        $attendanceCount = $hadirCount + $telatCount;
        $attendancePercentage = $totalPresensi > 0 ? round(($attendanceCount / $totalPresensi) * 100, 1) : 0;

        // Group presensi by month for better organization
        $presensiGrouped = $presensi->groupBy(function($item) {
            return Carbon::parse($item->waktu_presensi)->format('Y-m');
        });

        // Get unique subjects for filter (optional)
        $mataKuliah = $presensi->pluck('jadwal.matakuliah.nama')->unique()->values();

        return view('mahasiswa.presensi.index', compact(
            'presensi',
            'presensiGrouped',
            'totalPresensi',
            'hadirCount',
            'telatCount',
            'alphaCount',
            'attendancePercentage',
            'mataKuliah'
        ));
    }

    public function show($id)
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();
        
        $presensi = Presensi::with(['jadwal.matakuliah', 'jadwal.dosen', 'jadwal.kelas'])
            ->where('mahasiswa_id', $mahasiswa->id)
            ->where('id', $id)
            ->firstOrFail();

        return view('mahasiswa.presensi.show', compact('presensi'));
    }
}
