<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;

class UploadFotoMahasiswaController extends Controller
{
    public function index()
    {
        return view('mahasiswa.wajah.index');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'foto.*' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user = Auth::user();
        $namaMahasiswa = $user->mahasiswa?->nama ?? 'unknown';

        Log::info('Mulai upload wajah oleh user:', ['id' => $user->id, 'nama' => $namaMahasiswa]);

        $client = new Client();
        $multipart = [];

        // Tambahkan semua file ke multipart
        foreach ($request->file('foto') as $file) {
            Log::info('Menyiapkan file:', [
                'original_name' => $file->getClientOriginalName(),
                'path' => $file->getPathname()
            ]);

            $multipart[] = [
                'name'     => 'files',
                'contents' => fopen($file->getPathname(), 'r'),
                'filename' => $file->getClientOriginalName(),
            ];
        }

        // Tambahkan nama mahasiswa (folder tujuan)
        $multipart[] = [
            'name'     => 'name',
            'contents' => $namaMahasiswa,
        ];

        try {
            // Upload ke FastAPI dengan timeout yang lebih pendek karena menggunakan background task
            $response = $client->post('http://127.0.0.1:8000/upload', [
                'multipart' => $multipart,
                'timeout' => 30, // Reduced timeout karena embedding dijalankan di background
                'connect_timeout' => 10, // Timeout untuk koneksi
            ]);

            $body = json_decode((string) $response->getBody(), true);

            Log::info('Response dari FastAPI:', $body);

            // Cek apakah ada error dalam upload individual files
            $hasErrors = false;
            $errorMessages = [];
            
            if (isset($body['results'])) {
                foreach ($body['results'] as $result) {
                    if (isset($result['error'])) {
                        $hasErrors = true;
                        $errorMessages[] = "Error uploading {$result['filename']}: {$result['error']}";
                    }
                }
            }

            if ($hasErrors) {
                Log::warning('Ada error dalam upload beberapa file:', $errorMessages);
                return back()->with('warning', 'Upload selesai dengan beberapa error. Embeddings sedang di-generate di background.')
                            ->withErrors($errorMessages);
            }

            return back()->with('success', 'Upload berhasil! Embeddings sedang di-generate di background untuk ' . $namaMahasiswa . '.');
            
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            Log::error('Gagal koneksi ke FastAPI:', [
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['upload' => 'Gagal terhubung ke server FastAPI. Pastikan server berjalan di http://127.0.0.1:8000']);
            
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            Log::error('Request error ke FastAPI:', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            if ($e->hasResponse()) {
                $responseBody = $e->getResponse()->getBody()->getContents();
                Log::error('Response body:', ['body' => $responseBody]);
            }

            return back()->withErrors(['upload' => 'Gagal upload ke FastAPI: ' . $e->getMessage()]);
            
        } catch (\Exception $e) {
            Log::error('Gagal upload ke FastAPI:', [
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['upload' => 'Gagal upload ke FastAPI: ' . $e->getMessage()]);
        }
    }

    // Method untuk manual generate embedding (menggunakan background task)
    public function generateEmbedding(Request $request)
    {
        $user = Auth::user();
        $namaMahasiswa = $user->mahasiswa?->nama ?? 'unknown';
        
        Log::info('Manual generate embedding diminta:', ['nama' => $namaMahasiswa]);
        
        $client = new Client();
        
        try {
            $response = $client->post("http://127.0.0.1:8000/generate-embeddings/{$namaMahasiswa}", [
                'timeout' => 10, // Lebih pendek karena background task
                'connect_timeout' => 5,
            ]);
            
            $body = json_decode((string) $response->getBody(), true);
            Log::info('Response generate embedding:', $body);
            
            return back()->with('success', 'Embeddings sedang di-generate ulang di background untuk ' . $namaMahasiswa);
            
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            Log::error('Gagal koneksi untuk generate embedding:', [
                'error' => $e->getMessage(),
            ]);
            
            return back()->withErrors(['embedding' => 'Gagal terhubung ke server FastAPI untuk generate embedding']);
            
        } catch (\Exception $e) {
            Log::error('Gagal generate embedding:', [
                'error' => $e->getMessage(),
            ]);
            
            return back()->withErrors(['embedding' => 'Gagal generate embedding: ' . $e->getMessage()]);
        }
    }

    // Method untuk cek status embedding (opsional)
    public function checkEmbeddingStatus(Request $request)
    {
        $user = Auth::user();
        $namaMahasiswa = $user->mahasiswa?->nama ?? 'unknown';
        
        // Cek apakah file embedding sudah ada
        $embeddingPath = storage_path('app/embeddings/dataset_info.json');
        
        if (!file_exists($embeddingPath)) {
            return response()->json(['status' => 'no_embeddings', 'message' => 'Belum ada embedding yang tersimpan']);
        }
        
        try {
            $data = json_decode(file_get_contents($embeddingPath), true);
            $userEmbeddings = array_filter($data, function($item) use ($namaMahasiswa) {
                return $item['name'] === $namaMahasiswa;
            });
            
            return response()->json([
                'status' => 'success',
                'count' => count($userEmbeddings),
                'message' => count($userEmbeddings) . ' embedding ditemukan untuk ' . $namaMahasiswa
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal membaca file embedding']);
        }
    }



}
