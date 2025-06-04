<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dosen extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nidn',
        'nama',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function jadwals()
    {
        return $this->hasMany(Jadwal::class);
    }

    public function matakuliahs()
    {
        return $this->belongsToMany(MataKuliah::class, 'jadwals')
                    ->distinct();
    }

    public function kelas()
    {
        return $this->hasManyThrough(Kelas::class, Jadwal::class, 'dosen_id', 'id', 'id', 'kelas_id')
                    ->distinct();
    }
}
