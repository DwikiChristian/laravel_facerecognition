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

        $currentDay = $this->getCurrentDayInIndonesian();
        $currentTime = Carbon::now('Asia/Makassar');
        // Alternative method if the above doesn't work:
        // $jadwal = Jadwal::where('kelas_id', $mahasiswa->kelas_id)
        //     ->with(['matakuliah', 'dosen'])
        //     ->orderBy('hari')
        //     ->orderBy('jam_mulai')
        //     ->get();
        $jadwal = $jadwal->map(function ($item) use ($currentDay, $currentTime) {
            $item->status = $this->getJadwalStatus($item, $currentDay, $currentTime);
            return $item;
        });  
        

        return view('mahasiswa.jadwal.index', compact('jadwal','currentDay'));
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
    private function getJadwalStatus($jadwal, $currentDay, $currentTime)
    {
        // Get current time in H:i format
        $currentTimeString = $currentTime->format('H:i');
        
        // If it's the same day
        if ($jadwal->hari === $currentDay) {
            // If current time is before class starts
            if ($currentTimeString < $jadwal->jam_mulai) {
                return 'AKAN DIMULAI';
            }
            // If current time is between start and end time
            elseif ($currentTimeString >= $jadwal->jam_mulai && $currentTimeString <= $jadwal->jam_selesai) {
                return 'SEDANG BERLANGSUNG';
            }
            // If current time is after class ends
            else {
                return 'SELESAI';
            }
        }
        // If it's a different day
        else {
            // Get day order for comparison
            $dayOrder = [
                'Senin' => 1,
                'Selasa' => 2,
                'Rabu' => 3,
                'Kamis' => 4,
                'Jumat' => 5,
                'Sabtu' => 6,
                'Minggu' => 7
            ];
            
            $currentDayOrder = $dayOrder[$currentDay] ?? 1;
            $jadwalDayOrder = $dayOrder[$jadwal->hari] ?? 1;
            
            // If jadwal day has passed this week
            if ($jadwalDayOrder < $currentDayOrder) {
                return 'LEWAT';
            }
            // If jadwal day hasn't come yet this week
            else {
                return 'AKAN DATANG';
            }
        }
    }
}
