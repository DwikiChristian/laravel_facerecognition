<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JadwalController extends Controller
{
    public function index()
    {
        $jadwals = Jadwal::with(['kelas.prodi.jurusan', 'dosen', 'matkul'])->get();
        return response()->json($jadwals);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kelas_id' => 'required|exists:kelas,id',
            'dosen_id' => 'required|exists:users,id',
            'mata_kuliah_id' => 'required|exists:mata_kuliahs,id',
            'hari' => 'required|string|in:Senin,Selasa,Rabu,Kamis,Jumat',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $jadwal = Jadwal::create($validator->validated());

        return response()->json([
            'message' => 'Jadwal berhasil ditambahkan',
            'data' => $jadwal,
        ], 201);
    }

    public function show($id)
    {
        $jadwal = Jadwal::with(['kelas.prodi.jurusan', 'dosen', 'matkul'])->findOrFail($id);
        return response()->json($jadwal);
    }

    public function update(Request $request, $id)
    {
        $jadwal = Jadwal::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'kelas_id' => 'sometimes|required|exists:kelas,id',
            'dosen_id' => 'sometimes|required|exists:users,id',
            'mata_kuliah_id' => 'sometimes|required|exists:mata_kuliahs,id',
            'hari' => 'sometimes|required|string|in:Senin,Selasa,Rabu,Kamis,Jumat',
            'jam_mulai' => 'sometimes|required|date_format:H:i',
            'jam_selesai' => 'sometimes|required|date_format:H:i|after:jam_mulai',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $jadwal->update($validator->validated());

        return response()->json([
            'message' => 'Jadwal berhasil diperbarui',
            'data' => $jadwal,
        ]);
    }

    public function destroy($id)
    {
        $jadwal = Jadwal::findOrFail($id);
        $jadwal->delete();

        return response()->json(['message' => 'Jadwal berhasil dihapus']);
    }
}
