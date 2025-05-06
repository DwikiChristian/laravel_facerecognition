<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use HasFactory;

    protected $fillable = ['prodi_id', 'nama'];

    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }

    public function mahasiswa()
    {
        return $this->hasMany(Mahasiswa::class);
    }
}
