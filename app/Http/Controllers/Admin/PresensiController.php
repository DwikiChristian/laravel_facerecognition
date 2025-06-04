<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use App\Models\Jurusan;
use App\Models\Prodi;
use App\Models\Kelas;
use App\Models\MataKuliah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PresensiController extends Controller
{
    /**
     * Display the monitoring page
     */
    public function index(Request $request)
    {
        // Jika AJAX request, return data JSON
        if ($request->ajax()) {
            return $this->getData($request);
        }
        
        // Jika bukan AJAX, return view
        return view('admin.presensi.index');
    }

    /**
     * Get presensi data (AJAX)
     */
    private function getData(Request $request)
    {
        try {
            $query = Presensi::with([
                'mahasiswa.kelas.prodi.jurusan',
                'jadwal.matakuliah',
                'jadwal.dosen'
            ]);

            // Apply filters
            $this->applyFilters($query, $request);

            // Handle grouping
            if ($request->filled('group_by')) {
                return $this->getGroupedData($query, $request);
            }

            // Regular pagination
            $perPage = $request->get('per_page', 50);
            $presensiData = $query->orderBy('waktu_presensi', 'desc')
                                 ->paginate($perPage);

            // Get summary statistics
            $summaryQuery = Presensi::query();
            $this->applyFilters($summaryQuery, $request);
            $summary = $this->getSummaryStats($summaryQuery);

            return response()->json([
                'success' => true,
                'data' => $presensiData,
                'summary' => $summary
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Apply filters to query
     */
    private function applyFilters($query, Request $request)
    {
        // Date range filter
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('waktu_presensi', '>=', $request->tanggal_mulai);
        }
        
        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('waktu_presensi', '<=', $request->tanggal_selesai);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Jurusan filter
        if ($request->filled('jurusan_id')) {
            $query->whereHas('mahasiswa.kelas.prodi', function($q) use ($request) {
                $q->where('jurusan_id', $request->jurusan_id);
            });
        }

        // Prodi filter
        if ($request->filled('prodi_id')) {
            $query->whereHas('mahasiswa.kelas', function($q) use ($request) {
                $q->where('prodi_id', $request->prodi_id);
            });
        }

        // Kelas filter
        if ($request->filled('kelas_id')) {
            $query->whereHas('mahasiswa', function($q) use ($request) {
                $q->where('kelas_id', $request->kelas_id);
            });
        }

        // Mata kuliah filter
        if ($request->filled('mata_kuliah_id')) {
            $query->whereHas('jadwal', function($q) use ($request) {
                $q->where('matakuliah_id', $request->mata_kuliah_id);
            });
        }

        // Search filter (nama mahasiswa atau NIM)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('mahasiswa', function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nim', 'like', "%{$search}%");
            });
        }
    }

    /**
     * Get grouped data
     */
    private function getGroupedData($query, Request $request)
    {
        $groupBy = $request->group_by;
        $presensiData = $query->get();
        
        $grouped = [];

        switch ($groupBy) {
            case 'jurusan':
                $grouped = $this->groupByJurusan($presensiData);
                break;
            case 'prodi':
                $grouped = $this->groupByProdi($presensiData);
                break;
            case 'kelas':
                $grouped = $this->groupByKelas($presensiData);
                break;
            case 'mata_kuliah':
                $grouped = $this->groupByMataKuliah($presensiData);
                break;
            case 'tanggal':
                $grouped = $this->groupByTanggal($presensiData);
                break;
        }

        return response()->json([
            'success' => true,
            'data' => $grouped,
            'summary' => $this->getSummaryStats($query)
        ]);
    }

    /**
     * Group by functions
     */
    private function groupByJurusan($presensiData)
    {
        return $presensiData->groupBy(function($item) {
            return $item->mahasiswa->kelas->prodi->jurusan->id;
        })->map(function($items, $jurusanId) {
            $jurusan = $items->first()->mahasiswa->kelas->prodi->jurusan;
            return [
                'group_info' => $jurusan,
                'presensis' => $items->values(),
                'summary' => $this->calculateGroupSummary($items)
            ];
        })->values();
    }

    private function groupByProdi($presensiData)
    {
        return $presensiData->groupBy(function($item) {
            return $item->mahasiswa->kelas->prodi->id;
        })->map(function($items, $prodiId) {
            $prodi = $items->first()->mahasiswa->kelas->prodi;
            return [
                'group_info' => $prodi,
                'presensis' => $items->values(),
                'summary' => $this->calculateGroupSummary($items)
            ];
        })->values();
    }

    private function groupByKelas($presensiData)
    {
        return $presensiData->groupBy(function($item) {
            return $item->mahasiswa->kelas->id;
        })->map(function($items, $kelasId) {
            $kelas = $items->first()->mahasiswa->kelas;
            return [
                'group_info' => $kelas,
                'presensis' => $items->values(),
                'summary' => $this->calculateGroupSummary($items)
            ];
        })->values();
    }

    private function groupByMataKuliah($presensiData)
    {
        return $presensiData->groupBy(function($item) {
            return $item->jadwal->matakuliah->id;
        })->map(function($items, $mataKuliahId) {
            $mataKuliah = $items->first()->jadwal->matakuliah;
            return [
                'group_info' => $mataKuliah,
                'presensis' => $items->values(),
                'summary' => $this->calculateGroupSummary($items)
            ];
        })->values();
    }

    private function groupByTanggal($presensiData)
    {
        return $presensiData->groupBy(function($item) {
            return Carbon::parse($item->waktu_presensi)->format('Y-m-d');
        })->map(function($items, $tanggal) {
            return [
                'group_info' => ['tanggal' => $tanggal],
                'presensis' => $items->values(),
                'summary' => $this->calculateGroupSummary($items)
            ];
        })->values();
    }

    /**
     * Calculate group summary
     */
    private function calculateGroupSummary($items)
    {
        return [
            'total' => $items->count(),
            'hadir' => $items->where('status', 'hadir')->count(),
            'telat' => $items->where('status', 'telat')->count(),
            'tidak_hadir' => $items->where('status', 'tidak_hadir')->count(),
            'unknown' => $items->where('status', 'unknown')->count(),
        ];
    }

    /**
     * Get summary statistics
     */
    private function getSummaryStats($query)
    {
        $stats = $query->selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN status = "hadir" THEN 1 ELSE 0 END) as hadir,
            SUM(CASE WHEN status = "telat" THEN 1 ELSE 0 END) as telat,
            SUM(CASE WHEN status = "tidak_hadir" THEN 1 ELSE 0 END) as tidak_hadir,
            SUM(CASE WHEN status = "unknown" THEN 1 ELSE 0 END) as unknown
        ')->first();

        return [
            'total' => $stats->total,
            'hadir' => $stats->hadir,
            'telat' => $stats->telat,
            'tidak_hadir' => $stats->tidak_hadir,
            'unknown' => $stats->unknown,
        ];
    }

    /**
     * Get detailed statistics
     */
    public function stats(Request $request)
    {
        try {
            $query = Presensi::with([
                'mahasiswa.kelas.prodi.jurusan',
                'jadwal.matakuliah',
                'jadwal.dosen'
            ]);

            // Apply same filters as main data
            $this->applyFilters($query, $request);

            // Main statistics
            $mainStats = $this->getSummaryStats($query);

            // Confidence statistics
            $confidenceStats = $query->selectRaw('
                AVG(confidence) as confidence_avg,
                COUNT(CASE WHEN confidence < 80 THEN 1 END) as low_confidence
            ')->first();

            // By hour statistics
            $byHour = $query->selectRaw('
                HOUR(waktu_presensi) as hour,
                COUNT(*) as total
            ')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

            // By mata kuliah
            $byMataKuliah = $query->join('jadwals', 'presensis.jadwal_id', '=', 'jadwals.id')
                ->join('matakuliahs', 'jadwals.matakuliah_id', '=', 'matakuliahs.id')
                ->selectRaw('matakuliahs.nama, COUNT(*) as total')
                ->groupBy('matakuliahs.id', 'matakuliahs.nama')
                ->orderBy('total', 'desc')
                ->limit(10)
                ->get();

            // By jurusan
            $byJurusan = $query->join('mahasiswas', 'presensis.mahasiswa_id', '=', 'mahasiswas.id')
                ->join('kelas', 'mahasiswas.kelas_id', '=', 'kelas.id')
                ->join('prodis', 'kelas.prodi_id', '=', 'prodis.id')
                ->join('jurusans', 'prodis.jurusan_id', '=', 'jurusans.id')
                ->selectRaw('jurusans.nama, COUNT(*) as total')
                ->groupBy('jurusans.id', 'jurusans.nama')
                ->orderBy('total', 'desc')
                ->get();

            $stats = array_merge($mainStats, [
                'confidence_avg' => $confidenceStats->confidence_avg ?? 0,
                'low_confidence' => $confidenceStats->low_confidence ?? 0,
                'by_hour' => $byHour,
                'by_mata_kuliah' => $byMataKuliah,
                'by_jurusan' => $byJurusan,
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $stats
                ]);
            }

            return view('admin.presensi.stats', compact('stats'));

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memuat statistik: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Gagal memuat statistik: ' . $e->getMessage());
        }
    }

    /**
     * Show specific presensi detail
     */
    public function show($id)
    {
        try {
            $presensi = Presensi::with([
                'mahasiswa.kelas.prodi.jurusan',
                'jadwal.matakuliah',
                'jadwal.dosen',
                'jadwal.ruangan'
            ])->findOrFail($id);

            return view('admin.presensi.detail', compact('presensi'));

        } catch (\Exception $e) {
            return back()->with('error', 'Data presensi tidak ditemukan');
        }
    }

    /**
     * Update presensi status (manual correction by admin)
     */
    

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:hadir,telat,tidak_hadir,unknown',
            'catatan' => 'nullable|string|max:500',
        ]);

        try {
            $presensi = Presensi::findOrFail($id);
            
            $oldStatus = $presensi->status;
            
            $presensi->update([
                'status' => $request->status,
                'catatan' => $request->catatan,
                'admin_correction' => true,
                'corrected_at' => now(),
                'corrected_by' => auth()->id()
            ]);

            // Ganti activity() dengan Log
            Log::info('Status presensi dikoreksi oleh admin', [
                'presensi_id' => $presensi->id,
                'old_status' => $oldStatus,
                'new_status' => $request->status,
                'catatan' => $request->catatan,
                'corrected_by' => auth()->id(),
                'corrected_at' => now()->toDateTimeString(),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status presensi berhasil diperbarui'
                ]);
            }

            return back()->with('success', 'Status presensi berhasil diperbarui');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui status: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Gagal memperbarui status: ' . $e->getMessage());
        }
    }


   

    /**
     * Get master data for filters (AJAX)
     */
    public function getMasterData(Request $request)
    {
        try {
            $data = [];

            if ($request->has('jurusans') || $request->get('all')) {
                $data['jurusans'] = Jurusan::select('id', 'nama')->get();
            }

            if ($request->has('prodis') || $request->get('all')) {
                $query = Prodi::select('id', 'nama', 'jurusan_id');
                if ($request->filled('jurusan_id')) {
                    $query->where('jurusan_id', $request->jurusan_id);
                }
                $data['prodis'] = $query->get();
            }

            if ($request->has('kelas') || $request->get('all')) {
                $query = Kelas::select('id', 'nama', 'prodi_id');
                if ($request->filled('prodi_id')) {
                    $query->where('prodi_id', $request->prodi_id);
                }
                $data['kelas'] = $query->get();
            }

            if ($request->has('matakuliahs') || $request->get('all')) {
                $data['matakuliahs'] = MataKuliah::select('id', 'nama')->get();
            }

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data master: ' . $e->getMessage()
            ], 500);
        }
    }
}