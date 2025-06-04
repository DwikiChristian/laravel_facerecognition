<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use App\Models\Jurusan;
use App\Models\Prodi;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PresensiController extends Controller
{
    /**
     * Get presensi data with advanced filtering and grouping
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'jurusan_id' => 'nullable|exists:jurusans,id',
            'prodi_id' => 'nullable|exists:prodis,id',
            'kelas_id' => 'nullable|exists:kelas,id',
            'status' => 'nullable|in:hadir,telat,tidak_hadir,unknown',
            'mata_kuliah_id' => 'nullable|exists:mata_kuliahs,id',
            'confidence_min' => 'nullable|numeric|min:0|max:100',
            'group_by' => 'nullable|in:jurusan,prodi,kelas,tanggal,mata_kuliah',
            'per_page' => 'nullable|integer|min:10|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Default filter tanggal (hari ini)
        $tanggalMulai = $request->input('tanggal_mulai', Carbon::today()->format('Y-m-d'));
        $tanggalSelesai = $request->input('tanggal_selesai', Carbon::today()->format('Y-m-d'));

        // Build query dengan relasi lengkap
        $query = Presensi::with([
            'mahasiswa:id,nim,nama,kelas_id',
            'mahasiswa.kelas:id,nama,prodi_id',
            'mahasiswa.kelas.prodi:id,nama,jurusan_id',
            'mahasiswa.kelas.prodi.jurusan:id,nama',
            'jadwal:id,mata_kuliah_id,dosen_id,hari,jam_mulai,jam_selesai',
            'jadwal.matakuliah:id,nama,kode',
            'jadwal.dosen:id,nama'
        ])
        ->whereBetween('waktu_presensi', [
            $tanggalMulai . ' 00:00:00', 
            $tanggalSelesai . ' 23:59:59'
        ]);

        // Apply filters
        $this->applyFilters($query, $request);

        // Sorting
        $query->orderBy('waktu_presensi', 'desc');

        // Group by logic
        $groupBy = $request->input('group_by');
        if ($groupBy) {
            return $this->getGroupedData($query, $groupBy);
        }

        // Pagination
        $perPage = $request->input('per_page', 50);
        $presensis = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $presensis,
            'summary' => $this->getSummary($tanggalMulai, $tanggalSelesai, $request)
        ]);
    }

    /**
     * Get presensi statistics/dashboard data
     */
    public function stats(Request $request)
    {
        $tanggalMulai = $request->input('tanggal_mulai', Carbon::today()->format('Y-m-d'));
        $tanggalSelesai = $request->input('tanggal_selesai', Carbon::today()->format('Y-m-d'));

        $baseQuery = Presensi::whereBetween('waktu_presensi', [
            $tanggalMulai . ' 00:00:00', 
            $tanggalSelesai . ' 23:59:59'
        ]);

        // Apply filters if provided
        $this->applyFilters($baseQuery, $request);

        $stats = [
            'total_presensi' => (clone $baseQuery)->count(),
            'hadir' => (clone $baseQuery)->where('status', 'hadir')->count(),
            'telat' => (clone $baseQuery)->where('status', 'telat')->count(),
            'tidak_hadir' => (clone $baseQuery)->where('status', 'tidak_hadir')->count(),
            'unknown' => (clone $baseQuery)->where('status', 'unknown')->count(),
            'confidence_avg' => (clone $baseQuery)->whereNotNull('confidence')->avg('confidence'),
            'low_confidence' => (clone $baseQuery)->where('confidence', '<', 80)->count(),
            'by_hour' => $this->getPresensiByHour($baseQuery),
            'by_mata_kuliah' => $this->getPresensiByMataKuliah($baseQuery),
            'by_jurusan' => $this->getPresensiByJurusan($baseQuery)
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'period' => [
                'tanggal_mulai' => $tanggalMulai,
                'tanggal_selesai' => $tanggalSelesai
            ]
        ]);
    }

    /**
     * Update presensi status (untuk koreksi manual admin)
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:hadir,telat,tidak_hadir,unknown',
            'catatan' => 'nullable|string|max:500',
            'admin_correction' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $presensi = Presensi::findOrFail($id);
        
        $oldStatus = $presensi->status;
        $presensi->update([
            'status' => $request->status,
            'catatan' => $request->catatan,
            'admin_correction' => $request->input('admin_correction', true),
            'updated_by' => auth()->id(), // Assuming auth is available
            'updated_at' => now()
        ]);

        // Log the change (optional)
        Log::info('Presensi status updated', [
            'presensi_id' => $id,
            'old_status' => $oldStatus,
            'new_status' => $request->status,
            'admin_id' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status presensi berhasil diperbarui',
            'data' => $presensi->fresh(['mahasiswa', 'jadwal.matakuliah'])
        ]);
    }

    /**
     * Get detailed presensi info
     */
    public function show($id)
    {
        $presensi = Presensi::with([
            'mahasiswa:id,nim,nama,kelas_id',
            'mahasiswa.kelas:id,nama,prodi_id',
            'mahasiswa.kelas.prodi:id,nama,jurusan_id',
            'mahasiswa.kelas.prodi.jurusan:id,nama',
            'jadwal:id,mata_kuliah_id,dosen_id,hari,jam_mulai,jam_selesai',
            'jadwal.matakuliah:id,nama,kode',
            'jadwal.dosen:id,nama'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $presensi
        ]);
    }

    /**
     * Export presensi data
     */
    public function export(Request $request)
    {
        // Validation sama seperti index
        $validator = Validator::make($request->all(), [
            'format' => 'required|in:excel,csv,pdf',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            // ... filter lainnya
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Build query yang sama seperti index
        $query = Presensi::with([
            'mahasiswa:id,nim,nama,kelas_id',
            'mahasiswa.kelas:id,nama,prodi_id',
            'mahasiswa.kelas.prodi:id,nama,jurusan_id',
            'mahasiswa.kelas.prodi.jurusan:id,nama',
            'jadwal:id,mata_kuliah_id,dosen_id,hari,jam_mulai,jam_selesai',
            'jadwal.matakuliah:id,nama,kode',
            'jadwal.dosen:id,nama'
        ]);

        $this->applyFilters($query, $request);
        
        $data = $query->get();

        // Return download URL or trigger export job
        return response()->json([
            'success' => true,
            'message' => 'Export sedang diproses',
            'export_id' => uniqid(), // Generate export job ID
            'estimated_time' => '2-5 menit'
        ]);
    }

    // Helper Methods
    private function applyFilters($query, $request)
    {
        if ($request->filled('jurusan_id')) {
            $query->whereHas('mahasiswa.kelas.prodi.jurusan', function ($q) use ($request) {
                $q->where('id', $request->jurusan_id);
            });
        }

        if ($request->filled('prodi_id')) {
            $query->whereHas('mahasiswa.kelas.prodi', function ($q) use ($request) {
                $q->where('id', $request->prodi_id);
            });
        }

        if ($request->filled('kelas_id')) {
            $query->whereHas('mahasiswa.kelas', function ($q) use ($request) {
                $q->where('id', $request->kelas_id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('mata_kuliah_id')) {
            $query->whereHas('jadwal', function ($q) use ($request) {
                $q->where('mata_kuliah_id', $request->mata_kuliah_id);
            });
        }

        if ($request->filled('confidence_min')) {
            $query->where('confidence', '>=', $request->confidence_min);
        }
    }

    private function getSummary($tanggalMulai, $tanggalSelesai, $request)
    {
        $query = Presensi::whereBetween('waktu_presensi', [
            $tanggalMulai . ' 00:00:00', 
            $tanggalSelesai . ' 23:59:59'
        ]);

        $this->applyFilters($query, $request);

        return [
            'total' => $query->count(),
            'hadir' => (clone $query)->where('status', 'hadir')->count(),
            'telat' => (clone $query)->where('status', 'telat')->count(),
            'tidak_hadir' => (clone $query)->where('status', 'tidak_hadir')->count(),
            'unknown' => (clone $query)->where('status', 'unknown')->count(),
            'periode' => [
                'dari' => $tanggalMulai,
                'sampai' => $tanggalSelesai
            ]
        ];
    }

    private function getGroupedData($query, $groupBy)
    {
        $data = $query->get();
        $grouped = [];

        foreach ($data as $presensi) {
            $key = $this->getGroupKey($presensi, $groupBy);
            
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'group_info' => $this->getGroupInfo($presensi, $groupBy),
                    'presensis' => [],
                    'summary' => [
                        'total' => 0,
                        'hadir' => 0,
                        'telat' => 0,
                        'tidak_hadir' => 0,
                        'unknown' => 0
                    ]
                ];
            }

            $grouped[$key]['presensis'][] = $presensi;
            $grouped[$key]['summary']['total']++;
            $grouped[$key]['summary'][$presensi->status]++;
        }

        return response()->json([
            'success' => true,
            'data' => array_values($grouped),
            'group_by' => $groupBy
        ]);
    }

    private function getGroupKey($presensi, $groupBy)
    {
        switch ($groupBy) {
            case 'jurusan':
                return $presensi->mahasiswa->kelas->prodi->jurusan->id;
            case 'prodi':
                return $presensi->mahasiswa->kelas->prodi->id;
            case 'kelas':
                return $presensi->mahasiswa->kelas->id;
            case 'tanggal':
                return Carbon::parse($presensi->waktu_presensi)->format('Y-m-d');
            case 'mata_kuliah':
                return $presensi->jadwal->matakuliah->id;
            default:
                return 'default';
        }
    }

    private function getGroupInfo($presensi, $groupBy)
    {
        switch ($groupBy) {
            case 'jurusan':
                return $presensi->mahasiswa->kelas->prodi->jurusan;
            case 'prodi':
                return $presensi->mahasiswa->kelas->prodi;
            case 'kelas':
                return $presensi->mahasiswa->kelas;
            case 'tanggal':
                return ['tanggal' => Carbon::parse($presensi->waktu_presensi)->format('Y-m-d')];
            case 'mata_kuliah':
                return $presensi->jadwal->matakuliah;
            default:
                return null;
        }
    }

    private function getPresensiByHour($baseQuery)
    {
        return (clone $baseQuery)
            ->select(DB::raw('HOUR(waktu_presensi) as hour, COUNT(*) as total'))
            ->groupBy(DB::raw('HOUR(waktu_presensi)'))
            ->orderBy('hour')
            ->get();
    }

    private function getPresensiByMataKuliah($baseQuery)
    {
        return (clone $baseQuery)
            ->join('jadwals', 'presensis.jadwal_id', '=', 'jadwals.id')
            ->join('mata_kuliahs', 'jadwals.mata_kuliah_id', '=', 'mata_kuliahs.id')
            ->select('mata_kuliahs.nama', DB::raw('COUNT(*) as total'))
            ->groupBy('mata_kuliahs.id', 'mata_kuliahs.nama')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();
    }

    private function getPresensiByJurusan($baseQuery)
    {
        return (clone $baseQuery)
            ->join('mahasiswas', 'presensis.mahasiswa_id', '=', 'mahasiswas.id')
            ->join('kelas', 'mahasiswas.kelas_id', '=', 'kelas.id')
            ->join('prodis', 'kelas.prodi_id', '=', 'prodis.id')
            ->join('jurusans', 'prodis.jurusan_id', '=', 'jurusans.id')
            ->select('jurusans.nama', DB::raw('COUNT(*) as total'))
            ->groupBy('jurusans.id', 'jurusans.nama')
            ->orderBy('total', 'desc')
            ->get();
    }
}