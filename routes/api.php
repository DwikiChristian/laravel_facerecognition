<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MahasiswaController;
use App\Http\Controllers\Api\JurusanController;
use App\Http\Controllers\Api\ProdiController;
use App\Http\Controllers\Api\KelasController;
use App\Http\Controllers\Api\JadwalController;
use App\Http\Controllers\Api\PresensiController;
use App\Http\Controllers\Api\FotoWajahMahasiswaController;
use App\Http\Controllers\Api\FaceEmbeddingController;
use App\Http\Controllers\Api\DosenController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::apiResource('mahasiswa', MahasiswaController::class);
Route::apiResource('jurusans', JurusanController::class);
Route::apiResource('prodis', ProdiController::class);
Route::apiResource('kelas', KelasController::class);
Route::apiResource('jadwal', JadwalController::class);
Route::apiResource('presensi', PresensiController::class);
Route::apiResource('foto-wajah-mahasiswa', FotoWajahMahasiswaController::class);
Route::apiResource('face-embedding', FaceEmbeddingController::class);
Route::apiResource('dosens', DosenController::class);

