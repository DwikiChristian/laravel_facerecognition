<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        // Hapus foreign key constraint yang salah
        Schema::table('jadwals', function (Blueprint $table) {
            $table->dropForeign(['dosen_id']);
        });

        // Tambahkan foreign key constraint yang benar ke tabel dosens
        Schema::table('jadwals', function (Blueprint $table) {
            $table->foreign('dosen_id')->references('id')->on('dosens')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus foreign key constraint yang benar
        Schema::table('jadwals', function (Blueprint $table) {
            $table->dropForeign(['dosen_id']);
        });

        // Kembalikan ke foreign key constraint yang salah (untuk rollback)
        Schema::table('jadwals', function (Blueprint $table) {
            $table->foreign('dosen_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
