<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MataKuliah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MatakuliahController extends Controller
{
    public function index()
    {
        return response()->json(MataKuliah::all());
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'kode' => 'required|string|max:50|unique:mata_kuliahs,kode',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $matkul = MataKuliah::create($validator->validated());

        return response()->json([
            'message' => 'Mata kuliah berhasil ditambahkan',
            'data' => $matkul
        ], 201);
    }

    public function show($id)
    {
        $matkul = MataKuliah::findOrFail($id);
        return response()->json($matkul);
    }

    public function update(Request $request, $id)
    {
        $matkul = MataKuliah::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama' => 'sometimes|required|string|max:255',
            'kode' => 'sometimes|required|string|max:50|unique:mata_kuliahs,kode,' . $matkul->id,
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $matkul->update($validator->validated());

        return response()->json([
            'message' => 'Mata kuliah berhasil diperbarui',
            'data' => $matkul
        ]);
    }

    public function destroy($id)
    {
        $matkul = MataKuliah::findOrFail($id);
        $matkul->delete();

        return response()->json(['message' => 'Mata kuliah berhasil dihapus']);
    }
}
