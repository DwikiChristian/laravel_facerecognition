<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class MahasiswaController extends Controller
{
    public function index()
    {
        return response()->json(Mahasiswa::with('kelas.prodi.jurusan')->get());
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kelas_id' => 'required|exists:kelas,id',
            'mahasiswa' => 'required|array|min:1',
            'mahasiswa.*.nama' => 'required|string|max:255',
            'mahasiswa.*.nim' => 'required|string|unique:mahasiswas,nim',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $createdMahasiswas = [];

        foreach ($request->mahasiswa as $mhs) {
            $email = strtolower(Str::slug($mhs['nama'], '')) . '@gmail.com';

            $user = User::create([
                'name' => $mhs['nama'],
                'email' => $email,
                'password' => Hash::make($mhs['nim']),
                'role' => 'mahasiswa',
            ]);

            $mahasiswa = Mahasiswa::create([
                'nama' => $mhs['nama'],
                'nim' => $mhs['nim'],
                'kelas_id' => $request->kelas_id,
                'user_id' => $user->id,
            ]);

            $createdMahasiswas[] = $mahasiswa;
        }

            return response()->json([
                'message' => 'Mahasiswa berhasil ditambahkan',
                'data' => $createdMahasiswas
            ], 201);
        }


    public function show($id)
    {
        $mahasiswa = Mahasiswa::with('kelas.prodi.jurusan')->findOrFail($id);
        return response()->json($mahasiswa);
    }

    public function update(Request $request, $id)
    {
        $mahasiswa = Mahasiswa::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'kelas_id' => 'sometimes|required|exists:kelas,id',
            'nama' => 'sometimes|required|string|max:255',
            'nim' => 'sometimes|required|string|unique:mahasiswas,nim,' . $mahasiswa->id,
            'face_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $mahasiswa->update($validator->validated());

        return response()->json([
            'message' => 'Mahasiswa berhasil diperbarui',
            'data' => $mahasiswa
        ]);
    }

    public function destroy($id)
    {
        $mahasiswa = Mahasiswa::findOrFail($id);
        $mahasiswa->delete();

        return response()->json(['message' => 'Mahasiswa berhasil dihapus']);
    }
}
