<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Jadwal;
use App\Models\Presensi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;

class PresensiDosenController extends Controller
{
    protected $client;
    protected $faceRecognitionUrl;

    public function __construct()
    {
        $this->client = new Client();
        $this->faceRecognitionUrl = config('services.face_recognition.url');
    }

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

            // Hitung presensi
            $jadwal->total_presensi = $jadwal->presensis->count();
            $jadwal->presensi_hadir = $jadwal->presensis->where('status', 'hadir')->count();
            $jadwal->presensi_telat = $jadwal->presensis->where('status', 'telat')->count();
            $jadwal->presensi_alpha = $jadwal->presensis->where('status', 'alpha')->count();

            // Cek apakah presensi aktif
            $jadwal->is_presensi_active = $this->isPresensiActive($jadwal->id);
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

    /**
     * Get live presensi data for monitoring
     * Method ini siap untuk upgrade ke real-time dengan Pusher
     */
    public function getLivePresensi($jadwalId)
    {
        try {
            $dosen = Auth::user()->dosen;
            
            $jadwal = $dosen->jadwals()
                ->with(['matakuliah', 'kelas', 'presensis' => function($query) {
                    $query->with('mahasiswa')
                          ->orderBy('waktu_presensi', 'desc');
                }])
                ->findOrFail($jadwalId);

            $currentDay = $this->getCurrentDayInIndonesian();
            
            // Validate if this is today's class
            if ($jadwal->hari !== $currentDay) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data presensi hanya tersedia untuk kelas hari ini'
                ], 400);
            }

            // Filter presensi for today only
            $today = Carbon::now('Asia/Makassar')->format('Y-m-d');
            $todayPresensis = $jadwal->presensis->filter(function($presensi) use ($today) {
                return Carbon::parse($presensi->waktu_presensi)->format('Y-m-d') === $today;
            });

            // Statistics
            $stats = [
                'total' => $todayPresensis->count(),
                'hadir' => $todayPresensis->where('status', 'hadir')->count(),
                'terlambat' => $todayPresensis->where('status', 'terlambat')->count(),
                'tidak_hadir' => $todayPresensis->where('status', 'tidak_hadir')->count(),
            ];

            // Add percentage
            $stats['percentage'] = $stats['total'] > 0 
                ? round((($stats['hadir'] + $stats['terlambat']) / $stats['total']) * 100, 1)
                : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'jadwal' => [
                        'id' => $jadwal->id,
                        'matakuliah' => $jadwal->matakuliah,
                        'kelas' => $jadwal->kelas,
                        'jam_mulai' => $jadwal->jam_mulai,
                        'jam_selesai' => $jadwal->jam_selesai,
                    ],
                    'presensis' => $todayPresensis->values(),
                    'stats' => $stats,
                    'is_active' => $this->isPresensiActive($jadwalId),
                    'last_updated' => Carbon::now('Asia/Makassar')->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data presensi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refresh presensi data for a specific jadwal
     * Useful untuk polling atau manual refresh
     */
    public function refreshPresensi($jadwalId)
    {
        return $this->getLivePresensi($jadwalId);
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

            Log::info('Memulai presensi untuk jadwal:', [
                'jadwal_id' => $jadwalId,
                'dosen_id' => $dosen->id,
                'matakuliah' => $jadwal->matakuliah->nama,
                'kelas' => $jadwal->kelas->nama
            ]);

            // Send request to Python face recognition service using Guzzle
            $response = $this->client->post($this->faceRecognitionUrl . '/start-presensi', [
                'json' => [
                    'jadwal_id' => $jadwalId,
                    'dosen_id' => $dosen->id,
                    'matakuliah' => $jadwal->matakuliah->nama,
                    'kelas' => $jadwal->kelas->nama,
                    'jam_mulai' => $jadwal->jam_mulai,
                    'jam_selesai' => $jadwal->jam_selesai
                ],
                'timeout' => 30,
                'connect_timeout' => 10,
            ]);

            $body = json_decode((string) $response->getBody(), true);
            Log::info('Response dari FastAPI start-presensi:', $body);

            // Set presensi as active
            $this->setPresensiActive($jadwalId, true);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Presensi berhasil dimulai',
                    'data' => $body
                ]);
            }
            
            return redirect()->back()
                ->with('success', 'Presensi berhasil dimulai');

        } catch (ConnectException $e) {
            Log::error('Gagal koneksi ke FastAPI start-presensi:', [
                'error' => $e->getMessage(),
                'jadwal_id' => $jadwalId
            ]);

            $errorMessage = 'Gagal terhubung ke server Face Recognition. Pastikan server berjalan di ' . $this->faceRecognitionUrl;
            
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', $errorMessage);

        } catch (RequestException $e) {
            Log::error('Request error ke FastAPI start-presensi:', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'jadwal_id' => $jadwalId
            ]);

            if ($e->hasResponse()) {
                $responseBody = $e->getResponse()->getBody()->getContents();
                Log::error('Response body:', ['body' => $responseBody]);
            }

            $errorMessage = 'Gagal memulai presensi: ' . $e->getMessage();
            
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', $errorMessage);

        } catch (\Exception $e) {
            Log::error('Error umum start-presensi:', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'jadwal_id' => $jadwalId
            ]);

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

            Log::info('Menghentikan presensi untuk jadwal:', [
                'jadwal_id' => $jadwalId,
                'dosen_id' => $dosen->id
            ]);

            // Send request to Python service to stop presensi using Guzzle
            $response = $this->client->post($this->faceRecognitionUrl . '/stop-presensi', [
                'json' => [
                    'jadwal_id' => $jadwalId,
                    'dosen_id' => $dosen->id
                ],
                'timeout' => 30,
                'connect_timeout' => 10,
            ]);

            $body = json_decode((string) $response->getBody(), true);
            Log::info('Response dari FastAPI stop-presensi:', $body);

            // Set presensi as inactive
            $this->setPresensiActive($jadwalId, false);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Presensi berhasil dihentikan',
                    'data' => $body
                ]);
            }
            
            return redirect()->back()
                ->with('success', 'Presensi berhasil dihentikan');

        } catch (ConnectException $e) {
            Log::error('Gagal koneksi ke FastAPI stop-presensi:', [
                'error' => $e->getMessage(),
                'jadwal_id' => $jadwalId
            ]);

            $errorMessage = 'Gagal terhubung ke server Face Recognition untuk menghentikan presensi';
            
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', $errorMessage);

        } catch (RequestException $e) {
            Log::error('Request error ke FastAPI stop-presensi:', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'jadwal_id' => $jadwalId
            ]);

            if ($e->hasResponse()) {
                $responseBody = $e->getResponse()->getBody()->getContents();
                Log::error('Response body:', ['body' => $responseBody]);
            }

            $errorMessage = 'Gagal menghentikan presensi: ' . $e->getMessage();
            
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', $errorMessage);

        } catch (\Exception $e) {
            Log::error('Error umum stop-presensi:', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'jadwal_id' => $jadwalId
            ]);

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

    /**
     * Helper methods for presensi status
     * Sementara pakai session, nanti bisa upgrade ke Redis untuk real-time
     */
    private function isPresensiActive($jadwalId)
    {
        // Untuk sementara pakai session, nanti bisa pakai Redis/Cache
        return session()->has("presensi_active_{$jadwalId}");
    }

    private function setPresensiActive($jadwalId, $active = true)
    {
        if ($active) {
            session()->put("presensi_active_{$jadwalId}", true);
        } else {
            session()->forget("presensi_active_{$jadwalId}");
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

    public function getTablePresensi($jadwalId)
    {
        try {
            $dosen = Auth::user()->dosen;
            
            // Debug: Cek dosen
            Log::info('Dosen ID: ' . $dosen->id);
            
            $jadwal = $dosen->jadwals()
                ->with(['matakuliah', 'kelas'])
                ->findOrFail($jadwalId);

            // Debug: Cek jadwal data
            Log::info('Jadwal found:', [
                'id' => $jadwal->id,
                'kelas_id' => $jadwal->kelas_id,
                'dosen_id' => $jadwal->dosen_id,
                'mata_kuliah_id' => $jadwal->mata_kuliah_id,
                'hari' => $jadwal->hari
            ]);

            // Debug: Cek apakah kelas ter-load
            Log::info('Kelas data:', [
                'kelas_exists' => $jadwal->kelas !== null,
                'kelas_data' => $jadwal->kelas ? $jadwal->kelas->toArray() : 'NULL'
            ]);

            // Jika kelas null, coba load manual
            if (!$jadwal->kelas) {
                Log::error('Kelas is null, trying to load manually');
                
                // Cek apakah kelas_id ada di jadwal
                if (!$jadwal->kelas_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Jadwal tidak memiliki kelas_id'
                    ], 400);
                }
                
                // Coba load kelas manual
                $kelas = \App\Models\Kelas::find($jadwal->kelas_id);
                if (!$kelas) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Kelas dengan ID ' . $jadwal->kelas_id . ' tidak ditemukan'
                    ], 404);
                }
                
                // Set kelas manual
                $jadwal->setRelation('kelas', $kelas);
            }

            $currentDay = $this->getCurrentDayInIndonesian();
            
            // Validate if this is today's class
            if ($jadwal->hari !== $currentDay) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data presensi hanya tersedia untuk kelas hari ini'
                ], 400);
            }

            // Debug: Cek relasi mahasiswa
            Log::info('Checking mahasiswa relation');
            
            // Cek apakah kelas punya mahasiswa
            $mahasiswaCount = $jadwal->kelas->mahasiswa()->count();
            Log::info('Mahasiswa count in kelas: ' . $mahasiswaCount);
            
            if ($mahasiswaCount === 0) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'jadwal' => [
                            'id' => $jadwal->id,
                            'matakuliah' => $jadwal->matakuliah,
                            'kelas' => $jadwal->kelas,
                            'jam_mulai' => $jadwal->jam_mulai,
                            'jam_selesai' => $jadwal->jam_selesai,
                        ],
                        'mahasiswas' => [],
                        'stats' => [
                            'total_mahasiswa' => 0,
                            'hadir' => 0,
                            'terlambat' => 0,
                            'tidak_hadir' => 0,
                            'belum_presensi' => 0,
                            'percentage' => 0
                        ],
                        'is_active' => $this->isPresensiActive($jadwalId),
                        'last_updated' => Carbon::now('Asia/Makassar')->toISOString()
                    ],
                    'message' => 'Tidak ada mahasiswa di kelas ini'
                ]);
            }

            // Get all students in the class
            $mahasiswas = $jadwal->kelas->mahasiswa()
                ->orderBy('nama')
                ->get();

            Log::info('Mahasiswa loaded: ' . $mahasiswas->count());

            // Get today's presensi for this jadwal
            $today = Carbon::now('Asia/Makassar')->format('Y-m-d');
            $presensis = Presensi::where('jadwal_id', $jadwalId)
                ->whereDate('waktu_presensi', $today)
                ->with('mahasiswa')
                ->get()
                ->keyBy('mahasiswa_id');

            Log::info('Presensi data loaded: ' . $presensis->count());

            // Combine mahasiswa data with presensi status
            $tableData = $mahasiswas->map(function ($mahasiswa) use ($presensis) {
                $presensi = $presensis->get($mahasiswa->id);
                
                return [
                    'id' => $mahasiswa->id,
                    'nim' => $mahasiswa->nim,
                    'nama' => $mahasiswa->nama,
                    'status' => $presensi ? $presensi->status : 'belum_presensi',
                    'waktu_presensi' => $presensi ? $presensi->waktu_presensi : null,
                    'confidence' => $presensi ? $presensi->confidence : null,
                    'has_presensi' => $presensi !== null
                ];
            });

            // Statistics
            $stats = [
                'total_mahasiswa' => $mahasiswas->count(),
                'hadir' => $tableData->where('status', 'hadir')->count(),
                'terlambat' => $tableData->where('status', 'terlambat')->count(),
                'tidak_hadir' => $tableData->where('status', 'tidak_hadir')->count(),
                'belum_presensi' => $tableData->where('status', 'belum_presensi')->count(),
            ];

            // Add percentage
            $stats['percentage'] = $stats['total_mahasiswa'] > 0 
                ? round((($stats['hadir'] + $stats['terlambat']) / $stats['total_mahasiswa']) * 100, 1)
                : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'jadwal' => [
                        'id' => $jadwal->id,
                        'matakuliah' => $jadwal->matakuliah,
                        'kelas' => $jadwal->kelas,
                        'jam_mulai' => $jadwal->jam_mulai,
                        'jam_selesai' => $jadwal->jam_selesai,
                    ],
                    'mahasiswas' => $tableData->values(),
                    'stats' => $stats,
                    'is_active' => $this->isPresensiActive($jadwalId),
                    'last_updated' => Carbon::now('Asia/Makassar')->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error in getTablePresensi:', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data tabel presensi: ' . $e->getMessage(),
                'debug' => [
                    'line' => $e->getLine(),
                    'file' => basename($e->getFile())
                ]
            ], 500);
        }
    }
}