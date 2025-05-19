<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PresensiController extends Controller
{
    public function index()
    {
        // Load relasi jadwal dan mahasiswa untuk detail presensi
        $presensis = Presensi::with(['jadwal.kelas.prodi.jurusan', 'mahasiswa.kelas'])->get();
        return response()->json($presensis);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jadwal_id' => 'required|exists:jadwals,id',
            'mahasiswa_id' => 'required|exists:mahasiswas,id',
            'waktu_presensi' => 'required|date_format:Y-m-d H:i:s',
            'confidence' => 'nullable|numeric',
            'status' => 'required|string|in:hadir,telat,tidak hadir,unknown',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $presensi = Presensi::create($validator->validated());

        return response()->json([
            'message' => 'Presensi berhasil ditambahkan',
            'data' => $presensi,
        ], 201);
    }

    public function show($id)
    {
        $presensi = Presensi::with(['jadwal.kelas.prodi.jurusan', 'mahasiswa.kelas'])->findOrFail($id);
        return response()->json($presensi);
    }

    public function update(Request $request, $id)
    {
        $presensi = Presensi::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'jadwal_id' => 'sometimes|required|exists:jadwals,id',
            'mahasiswa_id' => 'sometimes|required|exists:mahasiswas,id',
            'waktu_presensi' => 'sometimes|required|date_format:Y-m-d H:i:s',
            'confidence' => 'nullable|numeric',
            'status' => 'sometimes|required|string|in:hadir,telat,tidak hadir,unknown',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $presensi->update($validator->validated());

        return response()->json([
            'message' => 'Presensi berhasil diperbarui',
            'data' => $presensi,
        ]);
    }

    public function destroy($id)
    {
        $presensi = Presensi::findOrFail($id);
        $presensi->delete();

        return response()->json(['message' => 'Presensi berhasil dihapus']);
    }
}
