<?php 
namespace App\Models;

use App\Models\MataKuliah;
use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    protected $fillable = [
        'kelas_id',
        'dosen_id',
        'mata_kuliah_id',
        'hari',
        'jam_mulai',
        'jam_selesai',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function dosen()
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }

    public function matkul()
    {
        return $this->belongsTo(MataKuliah::class);
    }

    public function presensis()
    {
        return $this->hasMany(Presensi::class);
    }
}
