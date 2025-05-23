<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DosenController extends Controller
{
    public function index()
    {
        return response()->json(Dosen::all());
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dosens' => 'required|array|min:1',
            'dosens.*.nama' => 'required|string|max:255',
            'dosens.*.nidn' => 'required|string|unique:dosens,nidn',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $createdDosens = [];

        foreach ($request->dosens as $dsn) {
            $email = strtolower(Str::slug($dsn['nama'], '')) . '@gmail.com';

            $user = User::create([
                'name' => $dsn['nama'],
                'email' => $email,
                'password' => Hash::make($dsn['nidn']),
                'role' => 'dosen',
            ]);

            $dosen = Dosen::create([
                'nama' => $dsn['nama'],
                'nidn' => $dsn['nidn'],
                'user_id' => $user->id,
            ]);

            $createdDosens[] = $dosen;
        }

        return response()->json([
            'message' => 'Dosen berhasil ditambahkan',
            'data' => $createdDosens
        ], 201);
    }

    public function show($id)
    {
        $dosen = Dosen::findOrFail($id);
        return response()->json($dosen);
    }

    public function update(Request $request, $id)
    {
        $dosen = Dosen::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama' => 'sometimes|required|string|max:255',
            'nidn' => 'sometimes|required|string|unique:dosens,nidn,' . $dosen->id,
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $dosen->update($validator->validated());

        return response()->json([
            'message' => 'Dosen berhasil diperbarui',
            'data' => $dosen
        ]);
    }

    public function destroy($id)
    {
        $dosen = Dosen::findOrFail($id);
        $dosen->delete();

        return response()->json(['message' => 'Dosen berhasil dihapus']);
    }
}
