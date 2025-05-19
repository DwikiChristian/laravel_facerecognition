<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Jurusan;
use Illuminate\Http\Request;

class JurusanController extends Controller
{
    public function index()
    {
        return response()->json(Jurusan::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
        ]);

        $jurusan = Jurusan::create([
            'nama' => $request->nama,
        ]);

        return response()->json($jurusan, 201);
    }

    public function show(Jurusan $jurusan)
    {
        return response()->json($jurusan);
    }

    public function update(Request $request, Jurusan $jurusan)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
        ]);

        $jurusan->update([
            'nama' => $request->nama,
        ]);

        return response()->json($jurusan);
    }

    public function destroy(Jurusan $jurusan)
    {
        $jurusan->delete();

        return response()->json(null, 204);
    }
}
