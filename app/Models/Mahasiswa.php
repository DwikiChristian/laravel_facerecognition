<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $fillable = ['kelas_id', 'nama', 'nim', 'face_url', 'user_id'];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }
    
    public function jadwals()
    {
        return $this->hasManyThrough(Jadwal::class, Kelas::class, 'id', 'kelas_id', 'kelas_id', 'id');
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
