<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prodi;
use Illuminate\Http\Request;

class ProdiController extends Controller
{
    public function index()
    {
        // Load both jurusan and kelas relationships
        return response()->json(Prodi::with(['jurusan', 'kelas'])->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'jurusan_id' => 'required|exists:jurusans,id',
            'nama' => 'required|string|max:255',
        ]);

        $prodi = Prodi::create([
            'jurusan_id' => $request->jurusan_id,
            'nama' => $request->nama,
        ]);

        // Load relationships after creation
        $prodi->load(['jurusan', 'kelas']);

        return response()->json($prodi, 201);
    }

    public function show(Prodi $prodi)
    {
        return response()->json($prodi->load(['jurusan', 'kelas']));
    }

    public function update(Request $request, Prodi $prodi)
    {
        $request->validate([
            'jurusan_id' => 'required|exists:jurusans,id',
            'nama' => 'required|string|max:255',
        ]);

        $prodi->update([
            'jurusan_id' => $request->jurusan_id,
            'nama' => $request->nama,
        ]);

        // Load relationships after update
        $prodi->load(['jurusan', 'kelas']);

        return response()->json($prodi);
    }

    public function destroy(Prodi $prodi)
    {
        $prodi->delete();

        return response()->json(null, 204);
    }
}