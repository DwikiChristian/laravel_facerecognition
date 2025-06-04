<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Jadwal;
use Carbon\Carbon;

class JadwalDosenController extends Controller
{
    public function index()
    {
        $dosen = Auth::user()->dosen;
        
        // Debug: Check if dosen exists
        if (!$dosen) {
            return view('dosen.jadwal.index', [
                'jadwal' => collect(), 
                'error' => 'Data dosen tidak ditemukan',
                'currentDay' => $this->getCurrentDayInIndonesian()
            ]);
        }

        // Get jadwal with proper eager loading
        $jadwal = $dosen->jadwals()
            ->with(['matakuliah', 'kelas'])
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        $currentDay = $this->getCurrentDayInIndonesian();
        

        return view('dosen.jadwal.index', compact('jadwal', 'currentDay'));
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
        $englishDay = $now->format('l'); // Gets full day name in English
        return $dayMap[$englishDay] ?? 'Senin';
    }

    public function today()
    {
        $dosen = Auth::user()->dosen;
        $currentDay = $this->getCurrentDayInIndonesian();
        
        if (!$dosen) {
            return view('dosen.jadwal.today', [
                'jadwal' => collect(), 
                'error' => 'Data dosen tidak ditemukan',
                'currentDay' => $currentDay
            ]);
        }

        // Get today's schedule only
        $jadwal = $dosen->jadwals()
            ->with(['matakuliah', 'kelas'])
            ->where('hari', $currentDay)
            ->orderBy('jam_mulai')
            ->get();

        return view('dosen.jadwal.today', compact('jadwal', 'currentDay'));
    }
}