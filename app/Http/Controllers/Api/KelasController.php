<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function index()
    {
        // Load prodi with jurusan relationship, and mahasiswa count
        return response()->json(Kelas::with(['prodi.jurusan', 'mahasiswa'])->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'prodi_id' => 'required|exists:prodis,id',
            'nama' => 'required|string|max:255',
        ]);

        $kelas = Kelas::create([
            'prodi_id' => $request->prodi_id,
            'nama' => $request->nama,
        ]);

        // Load relationships after creation
        $kelas->load(['prodi.jurusan', 'mahasiswa']);

        return response()->json($kelas, 201);
    }

    public function show(Kelas $kelas)
    {
        return response()->json($kelas->load(['prodi.jurusan', 'mahasiswa']));
    }

    public function update(Request $request, Kelas $kelas)
    {
        $request->validate([
            'prodi_id' => 'required|exists:prodis,id',
            'nama' => 'required|string|max:255',
        ]);

        $kelas->update([
            'prodi_id' => $request->prodi_id,
            'nama' => $request->nama,
        ]);

        // Load relationships after update
        $kelas->load(['prodi.jurusan', 'mahasiswa']);

        return response()->json($kelas);
    }

    public function destroy(Kelas $kelas)
    {
        $kelas->delete();

        return response()->json(null, 204);
    }
}