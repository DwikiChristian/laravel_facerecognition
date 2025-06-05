<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Presensi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;


class ReceivePresensiController extends Controller
{
    /**
     * Receive presensi data from Python service
     * Method ini akan dipanggil oleh Python service ketika ada mahasiswa yang presensi
     */
    public function receivePresensiData(Request $request)
    {
        try {
            $validated = $request->validate([
                'jadwal_id' => 'required|exists:jadwals,id',
                'mahasiswa_id' => 'required|exists:mahasiswas,id',
                'waktu_presensi' => 'required|date',
                'confidence' => 'required|numeric|min:0|max:1',
                'status' => 'required|in:hadir,terlambat,tidak_hadir',
                'bukti_screenshot' => 'nullable|string'
            ]);

            Log::info('Menerima data presensi dari Python service:', $validated);

            // Check if presensi already exists for today
            $today = Carbon::parse($validated['waktu_presensi'])->format('Y-m-d');
            $existingPresensi = Presensi::where('jadwal_id', $validated['jadwal_id'])
                ->where('mahasiswa_id', $validated['mahasiswa_id'])
                ->whereDate('waktu_presensi', $today)
                ->first();

            if ($existingPresensi) {
                // Update existing presensi if new confidence is higher
                if ($validated['confidence'] > $existingPresensi->confidence) {
                    $existingPresensi->update([
                        'waktu_presensi' => $validated['waktu_presensi'],
                        'confidence' => $validated['confidence'],
                        'status' => $validated['status'],
                        'bukti_screenshot' => $validated['bukti_screenshot'] ?? $existingPresensi->bukti_screenshot
                    ]);
                    $presensi = $existingPresensi->fresh(['mahasiswa', 'jadwal']);
                    Log::info('Presensi diupdate dengan confidence lebih tinggi');
                } else {
                    $presensi = $existingPresensi->load(['mahasiswa', 'jadwal']);
                    Log::info('Presensi sudah ada dengan confidence lebih tinggi, tidak diupdate');
                }
            } else {
                // Create new presensi
                $presensi = Presensi::create($validated);
                $presensi->load(['mahasiswa', 'jadwal']);
                Log::info('Presensi baru berhasil dibuat');
            }

            return response()->json([
                'success' => true,
                'message' => 'Data presensi berhasil disimpan',
                'data' => [
                    'id' => $presensi->id,
                    'jadwal_id' => $presensi->jadwal_id,
                    'mahasiswa_id' => $presensi->mahasiswa_id,
                    'mahasiswa_nama' => $presensi->mahasiswa->nama,
                    'status' => $presensi->status,
                    'waktu_presensi' => $presensi->waktu_presensi,
                    'confidence' => $presensi->confidence
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error pada receivePresensiData:', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Error pada receivePresensiData:', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'input' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data presensi: ' . $e->getMessage()
            ], 500);
        }
    }
}
