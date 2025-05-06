<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FotoWajahMahasiswa extends Model
{
    use HasFactory;

    protected $fillable = ['mahasiswa_id', 'url_foto'];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }
}

