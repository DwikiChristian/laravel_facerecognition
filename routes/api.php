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
use App\Http\Controllers\Api\MatakuliahController;
use App\Http\Controllers\Api\PresensiDosenController;

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
Route::apiResource('kelas', KelasController::class)->parameter('kelas', 'kelas');
Route::apiResource('jadwal', JadwalController::class);
Route::apiResource('foto-wajah-mahasiswa', FotoWajahMahasiswaController::class);
Route::apiResource('face-embedding', FaceEmbeddingController::class);
Route::apiResource('dosens', DosenController::class);
Route::apiResource('matakuliah', MatakuliahController::class);

// Custom routes for PresensiController (sesuai dengan method yang ada)
Route::prefix('presensi')->name('presensi.')->group(function () {
    // GET /api/presensi - index dengan filtering dan grouping
    Route::get('/', [PresensiController::class, 'index'])->name('index');
    
    // GET /api/presensi/stats - dashboard/statistics
    Route::get('/stats', [PresensiController::class, 'stats'])->name('stats');
    
    // GET /api/presensi/export - export data
    Route::get('/export', [PresensiController::class, 'export'])->name('export');
    
    // GET /api/presensi/{id} - show specific presensi
    Route::get('/{id}', [PresensiController::class, 'show'])->name('show');
    
    // PUT/PATCH /api/presensi/{id}/status - update status (koreksi manual admin)
    Route::put('/{id}/status', [PresensiController::class, 'updateStatus'])->name('updateStatus');
    Route::patch('/{id}/status', [PresensiController::class, 'updateStatus'])->name('updateStatusPatch');
});

