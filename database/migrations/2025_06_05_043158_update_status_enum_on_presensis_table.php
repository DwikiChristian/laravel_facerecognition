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
        Schema::table('presensis', function (Blueprint $table) {
            // Hapus dulu kolom status lama
            $table->dropColumn('status');
        });

        Schema::table('presensis', function (Blueprint $table) {
            // Tambah ulang dengan enum
            $table->enum('status', ['hadir', 'telat', 'alpha'])->default('alpha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presensis', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('presensis', function (Blueprint $table) {
            $table->string('status')->default('hadir');
        });
    }
};
