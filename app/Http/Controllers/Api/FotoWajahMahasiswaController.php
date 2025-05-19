<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FotoWajahMahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FotoWajahMahasiswaController extends Controller
{
    public function index()
    {
        $fotos = FotoWajahMahasiswa::with('mahasiswa')->get();
        return response()->json($fotos);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mahasiswa_id' => 'required|exists:mahasiswas,id',
            'url_foto' => 'required|url',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $foto = FotoWajahMahasiswa::create($validator->validated());

        return response()->json([
            'message' => 'Foto wajah mahasiswa berhasil ditambahkan',
            'data' => $foto,
        ], 201);
    }

    public function show($id)
    {
        $foto = FotoWajahMahasiswa::with('mahasiswa')->findOrFail($id);
        return response()->json($foto);
    }

    public function update(Request $request, $id)
    {
        $foto = FotoWajahMahasiswa::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'mahasiswa_id' => 'sometimes|required|exists:mahasiswas,id',
            'url_foto' => 'sometimes|required|url',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $foto->update($validator->validated());

        return response()->json([
            'message' => 'Foto wajah mahasiswa berhasil diperbarui',
            'data' => $foto,
        ]);
    }

    public function destroy($id)
    {
        $foto = FotoWajahMahasiswa::findOrFail($id);
        $foto->delete();

        return response()->json(['message' => 'Foto wajah mahasiswa berhasil dihapus']);
    }
}
