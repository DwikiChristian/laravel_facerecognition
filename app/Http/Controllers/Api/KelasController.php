<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use Illuminate\Http\Request;


class KelasController extends Controller
{
    public function index()
    {
        return response()->json(Kelas::with('prodi')->get());
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

        return response()->json($kelas, 201);
    }

    public function show(Kelas $kela)
    {
        return response()->json($kela->load('prodi'));
    }

    public function update(Request $request, Kelas $kela)
    {
        $request->validate([
            'prodi_id' => 'required|exists:prodis,id',
            'nama' => 'required|string|max:255',
        ]);

        $kela->update([
            'prodi_id' => $request->prodi_id,
            'nama' => $request->nama,
        ]);

        return response()->json($kela);
    }

    public function destroy(Kelas $kela)
    {
        $kela->delete();

        return response()->json(null, 204);
    }
}

