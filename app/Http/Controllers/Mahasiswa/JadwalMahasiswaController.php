<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Kelas;
use App\Models\MataKuliah;
use App\Models\Jadwal;
use Carbon\Carbon;

class JadwalMahasiswaController extends Controller
{
    public function index()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        
        // Debug: Check if mahasiswa exists
        if (!$mahasiswa) {
            return view('mahasiswa.jadwal.index', ['jadwal' => collect(), 'error' => 'Data mahasiswa tidak ditemukan']);
        }

        // Debug: Check if kelas exists
        if (!$mahasiswa->kelas) {
            return view('mahasiswa.jadwal.index', ['jadwal' => collect(), 'error' => 'Mahasiswa belum terdaftar di kelas manapun']);
        }

        // Get jadwal with proper eager loading
        $jadwal = $mahasiswa->kelas->jadwals()
            ->with(['matakuliah', 'dosen'])
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        $curentDay = $this->getCurrentDayInIndonesian();
        // Alternative method if the above doesn't work:
        // $jadwal = Jadwal::where('kelas_id', $mahasiswa->kelas_id)
        //     ->with(['matakuliah', 'dosen'])
        //     ->orderBy('hari')
        //     ->orderBy('jam_mulai')
        //     ->get();

        

        return view('mahasiswa.jadwal.index', compact('jadwal','curentDay'));
    }
    private function getCurrentDayInIndonesian()
    {
        $dayMap = [
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa', 
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu'
        ];
        
        $now = Carbon::now('Asia/Makassar');
        $englishDay = Carbon::now()->format('l'); // Gets full day name in English
        return $dayMap[$englishDay] ?? 'Senin';
    }
}
