<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    protected $fillable = [
        'kelas_id', 'dosen_id', 'tanggal', 'jam_mulai', 'jam_selesai',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function dosen()
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }

    public function presensis()
    {
        return $this->hasMany(Presensi::class);
    }
}
