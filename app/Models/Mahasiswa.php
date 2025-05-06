<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $fillable = ['kelas_id', 'nama', 'nim', 'face_url'];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function fotoWajah()
    {
        return $this->hasMany(FotoWajahMahasiswa::class);
    }

    public function embeddings()
    {
        return $this->hasMany(FaceEmbedding::class);
    }

    public function presensis()
    {
        return $this->hasMany(Presensi::class);
    }
}
