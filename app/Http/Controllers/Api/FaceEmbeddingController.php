<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FaceEmbedding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FaceEmbeddingController extends Controller
{
    public function index()
    {
        $embeddings = FaceEmbedding::with('mahasiswa')->get();
        return response()->json($embeddings);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mahasiswa_id' => 'required|exists:mahasiswas,id',
            'embedding' => 'required|string', // Simpan json string embedding
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $embedding = FaceEmbedding::create($validator->validated());

        return response()->json([
            'message' => 'Face embedding berhasil ditambahkan',
            'data' => $embedding,
        ], 201);
    }

    public function show($id)
    {
        $embedding = FaceEmbedding::with('mahasiswa')->findOrFail($id);
        return response()->json($embedding);
    }

    public function update(Request $request, $id)
    {
        $embedding = FaceEmbedding::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'mahasiswa_id' => 'sometimes|required|exists:mahasiswas,id',
            'embedding' => 'sometimes|required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $embedding->update($validator->validated());

        return response()->json([
            'message' => 'Face embedding berhasil diperbarui',
            'data' => $embedding,
        ]);
    }

    public function destroy($id)
    {
        $embedding = FaceEmbedding::findOrFail($id);
        $embedding->delete();

        return response()->json(['message' => 'Face embedding berhasil dihapus']);
    }
}
