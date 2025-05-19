<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prodi;
use Illuminate\Http\Request;

class ProdiController extends Controller
{
    public function index()
    {
        return response()->json(Prodi::with('jurusan')->get());
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

        return response()->json($prodi, 201);
    }

    public function show(Prodi $prodi)
    {
        return response()->json($prodi->load('jurusan'));
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

        return response()->json($prodi);
    }

    public function destroy(Prodi $prodi)
    {
        $prodi->delete();

        return response()->json(null, 204);
    }
}
