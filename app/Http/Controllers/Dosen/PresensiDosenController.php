<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\Jadwal;
use App\Models\Presensi;
use Carbon\Carbon;

class PresensiDosenController extends Controller
{
    public function index()
    {
        $dosen = Auth::user()->dosen;
        
        if (!$dosen) {
            return view('dosen.presensi.index', [
                'jadwalHariIni' => collect(), 
                'error' => 'Data dosen tidak ditemukan',
                'currentDay' => $this->getCurrentDayInIndonesian()
            ]);
        }

        $currentDay = $this->getCurrentDayInIndonesian();
        $currentTime = Carbon::now('Asia/Makassar');

        // Get today's schedule with presensi data
        $jadwalHariIni = $dosen->jadwals()
            ->with(['matakuliah', 'kelas', 'presensis.mahasiswa'])
            ->where('hari', $currentDay)
            ->orderBy('jam_mulai')
            ->get();

        // Add status information for each jadwal
        $jadwalHariIni->each(function ($jadwal) use ($currentTime) {
            $jamMulai = Carbon::parse($jadwal->jam_mulai)->setDateFrom($currentTime);
            $jamSelesai = Carbon::parse($jadwal->jam_selesai)->setDateFrom($currentTime);
            
            if ($currentTime->lt($jamMulai)) {
                $jadwal->status = 'upcoming';
                $jadwal->status_text = 'Akan Dimulai';
            } elseif ($currentTime->between($jamMulai, $jamSelesai)) {
                $jadwal->status = 'ongoing';
                $jadwal->status_text = 'Sedang Berlangsung';
            } else {
                $jadwal->status = 'finished';
                $jadwal->status_text = 'Selesai';
            }
            
            // Count presensi
            $jadwal->total_presensi = $jadwal->presensis->count();
            $jadwal->presensi_hadir = $jadwal->presensis->where('status', 'hadir')->count();
        });

        return view('dosen.presensi.index', compact('jadwalHariIni', 'currentDay'));
    }

    public function getJadwal($jadwalId)
    {
        // Handle AJAX request for jadwal detail
        if (request()->ajax() || request()->expectsJson()) {
            $dosen = Auth::user()->dosen;
            
            $jadwal = $dosen->jadwals()
                ->with(['matakuliah', 'kelas', 'presensis.mahasiswa'])
                ->findOrFail($jadwalId);

            return response()->json([
                'success' => true,
                'data' => [
                    'jadwal' => $jadwal
                ]
            ]);
        }

        // Regular web request - show detail page
        $dosen = Auth::user()->dosen;
        
        $jadwal = $dosen->jadwals()
            ->with(['matakuliah', 'kelas', 'presensis.mahasiswa'])
            ->findOrFail($jadwalId);

        $currentDay = $this->getCurrentDayInIndonesian();
        
        // Check if this is today's class
        if ($jadwal->hari !== $currentDay) {
            return redirect()->route('dosen.presensi.index')
                ->with('error', 'Presensi hanya dapat dibuka untuk kelas hari ini');
        }

        return view('dosen.presensi.show', compact('jadwal', 'currentDay'));
    }

    public function startPresensi(Request $request, $jadwalId)
    {
        try {
            $dosen = Auth::user()->dosen;
            
            $jadwal = $dosen->jadwals()
                ->with(['matakuliah', 'kelas'])
                ->findOrFail($jadwalId);

            $currentDay = $this->getCurrentDayInIndonesian();
            
            // Validate if this is today's class
            if ($jadwal->hari !== $currentDay) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Presensi hanya dapat dibuka untuk kelas hari ini'
                    ], 400);
                }
                
                return redirect()->back()
                    ->with('error', 'Presensi hanya dapat dibuka untuk kelas hari ini');
            }

            // Send request to Python face recognition service
            $response = Http::timeout(30)->post(config('services.face_recognition.url') . '/start-presensi', [
                'jadwal_id' => $jadwalId,
                'dosen_id' => $dosen->id,
                'matakuliah' => $jadwal->matakuliah->nama,
                'kelas' => $jadwal->kelas->nama,
                'jam_mulai' => $jadwal->jam_mulai,
                'jam_selesai' => $jadwal->jam_selesai
            ]);

            if ($response->successful()) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Presensi berhasil dimulai',
                        'data' => $response->json()
                    ]);
                }
                
                return redirect()->back()
                    ->with('success', 'Presensi berhasil dimulai');
            } else {
                $errorMessage = 'Gagal memulai presensi: ' . $response->body();
                
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage
                    ], 500);
                }
                
                return redirect()->back()
                    ->with('error', $errorMessage);
            }

        } catch (\Exception $e) {
            $errorMessage = 'Terjadi kesalahan: ' . $e->getMessage();
            
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', $errorMessage);
        }
    }

    public function stopPresensi(Request $request, $jadwalId)
    {
        try {
            $dosen = Auth::user()->dosen;
            
            $jadwal = $dosen->jadwals()->findOrFail($jadwalId);

            // Send request to Python service to stop presensi
            $response = Http::timeout(30)->post(config('services.face_recognition.url') . '/stop-presensi', [
                'jadwal_id' => $jadwalId,
                'dosen_id' => $dosen->id
            ]);

            if ($response->successful()) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Presensi berhasil dihentikan',
                        'data' => $response->json()
                    ]);
                }
                
                return redirect()->back()
                    ->with('success', 'Presensi berhasil dihentikan');
            } else {
                $errorMessage = 'Gagal menghentikan presensi: ' . $response->body();
                
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage
                    ], 500);
                }
                
                return redirect()->back()
                    ->with('error', $errorMessage);
            }

        } catch (\Exception $e) {
            $errorMessage = 'Terjadi kesalahan: ' . $e->getMessage();
            
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', $errorMessage);
        }
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
        $englishDay = $now->format('l');
        return $dayMap[$englishDay] ?? 'Senin';
    }
}