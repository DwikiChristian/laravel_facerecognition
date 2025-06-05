<?php

use App\Http\Controllers\Dosen\PresensiDosenController;
use App\Http\Controllers\Mahasiswa\JadwalMahasiswaController;
use App\Http\Controllers\Dosen\JadwalDosenController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Mahasiswa\UploadFotoMahasiswaController;
use App\Http\Controllers\Mahasiswa\PresensiMahasiswaController;
use App\Http\Controllers\Admin\PresensiController;
use App\Models\Presensi;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::get('/redirect-dashboard', function () {
    $user = Auth::user();

    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    } elseif ($user->role === 'dosen') {
        return redirect()->route('dosen.dashboard');
    } elseif ($user->role === 'mahasiswa') {
        return redirect()->route('mahasiswa.dashboard');
    }

    abort(403); // Jika role tidak dikenali
});

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth','role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', fn () => view('admin.dashboard'))->name('dashboard');
    
    // jurusan 
    Route::prefix('/jurusan')->name('jurusan.')->group(function () {
        Route::get('/', fn () => view('admin.jurusan.index'))->name('index');
        Route::get('/create', fn () => view('admin.jurusan.create'))->name('create');
        Route::get('/{id}/edit', fn ($id) => view('admin.jurusan.edit', ['id' => $id]))->name('edit');
    });

    // Program Studi
    Route::prefix('/prodi')->name('prodi.')->group(function () {
        Route::get('/', fn () => view('admin.prodi.index'))->name('index');
        Route::get('/create', fn () => view('admin.prodi.create'))->name('create');
        Route::get('/{id}/edit', fn ($id) => view('admin.prodi.edit', ['id' => $id]))->name('edit');
    });

    // Jadwal
    Route::prefix('/jadwal')->name('jadwal.')->group(function () {
        Route::get('/', fn () => view('admin.jadwal.index'))->name('index');
        Route::get('/create', fn () => view('admin.jadwal.create'))->name('create');
        Route::get('/{id}/edit', fn ($id) => view('admin.jadwal.edit', ['id' => $id]))->name('edit');
    });

    // Matkul
    Route::prefix('/matakuliah')->name('matakuliah.')->group(function () {
        Route::get('/', fn () => view('admin.matakuliah.index'))->name('index');
        Route::get('/create', fn () => view('admin.matakuliah.create'))->name('create');
        Route::get('/{id}/edit', fn ($id) => view('admin.matakuliah.edit', ['id' => $id]))->name('edit');
    });

    // Kelas
    Route::prefix('/kelas')->name('kelas.')->group(function () {
        Route::get('/', fn () => view('admin.kelas.index'))->name('index');
        Route::get('/create', fn () => view('admin.kelas.create'))->name('create');
        Route::get('/{id}/edit', fn ($id) => view('admin.kelas.edit', ['id' => $id]))->name('edit');
    });

    // Presensi monitoring routes
    Route::prefix('presensi')->name('presensi.')->group(function () {
        
        // Main monitoring page (supports AJAX requests)
        Route::get('/', [PresensiController::class, 'index'])->name('index');
        
        // Statistics page/data (supports AJAX requests)
        Route::get('/stats', [PresensiController::class, 'stats'])->name('stats');
        
        // Show specific presensi detail
        Route::get('/{id}', [PresensiController::class, 'show'])->name('show');
        
        // Update status (manual correction by admin)
        Route::put('/{id}/status', [PresensiController::class, 'updateStatus'])->name('updateStatus');
        Route::patch('/{id}/status', [PresensiController::class, 'updateStatus'])->name('updateStatusPatch');
        
        // Get master data for filters (AJAX)
        Route::get('/master/data', [PresensiController::class, 'getMasterData'])->name('masterData');
    });

    // Dosen
    Route::prefix('/dosen')->name('dosen.')->group(function () {
        Route::get('/', fn () => view('admin.dosen.index'))->name('index');
        Route::get('/create', fn () => view('admin.dosen.create'))->name('create');
        Route::get('/{id}/edit', fn ($id) => view('admin.dosen.edit', ['id' => $id]))->name('edit');
    });

    // Mahasiswa
    Route::prefix('/mahasiswa')->name('mahasiswa.')->group(function () {
        Route::get('/', fn () => view('admin.mahasiswa.index'))->name('index');
        Route::get('/create', fn () => view('admin.mahasiswa.create'))->name('create');
        Route::get('/{id}/edit', fn ($id) => view('admin.mahasiswa.edit', ['id' => $id]))->name('edit');
    });


    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:dosen'])->prefix('dosen')->name('dosen.')->group(function () {
    Route::get('/dashboard', fn () => view('dosen.dashboard'))->name('dashboard');
    // Jadwal routes
    Route::prefix('jadwal')->name('jadwal.')->group(function () {
        Route::get('/', [JadwalDosenController::class, 'index'])->name('index');
        Route::get('/today', [JadwalDosenController::class, 'today'])->name('today');
    });
    // Presensi routes
    Route::prefix('presensi')->name('presensi.')->group(function () {
        Route::get('/', [PresensiDosenController::class, 'index'])->name('index');
        Route::get('/{jadwal}', [PresensiDosenController::class, 'getJadwal'])->name('show');
        Route::post('/{jadwal}/start', [PresensiDosenController::class, 'startPresensi'])->name('start');
        Route::post('/{jadwal}/stop', [PresensiDosenController::class, 'stopPresensi'])->name('stop');
        Route::get('/table/{jadwal}', [PresensiDosenController::class, 'getTablePresensi'])->name('table');
    });
});

Route::middleware(['auth', 'role:mahasiswa'])->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
    Route::get('/dashboard', fn () => view('mahasiswa.dashboard'))->name('dashboard');
    Route::get('/wajah', [UploadFotoMahasiswaController::class, 'index'])->name('mahasiswa.wajah');
    Route::post('/upload', [UploadFotoMahasiswaController::class, 'upload'])->name('mahasiswa.upload');
    Route::get('/jadwal', [JadwalMahasiswaController::class, 'index'])->name('mahasiswa.jadwal');
    Route::get('/presensi', [PresensiMahasiswaController::class, 'index'])->name('mahasiswa.presensi');
});



require __DIR__.'/auth.php';
