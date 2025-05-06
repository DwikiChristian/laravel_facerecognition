<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaceEmbedding extends Model
{
    use HasFactory;

    protected $fillable = ['mahasiswa_id', 'embedding'];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }
}

