<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('presensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_id')->constrained()->onDelete('cascade');
            $table->foreignId('mahasiswa_id')->constrained()->onDelete('cascade');
            $table->timestamp('waktu_presensi');
            $table->string('confidence')->nullable(); // hasil kepercayaan dari face recognition
            $table->string('status')->default('hadir'); // hadir / telat / tidak hadir / unknown
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensis');
    }
};
