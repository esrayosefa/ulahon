<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('laporan_piket', function (Blueprint $table) {
            $table->id('id_laporan');

            // jadwal piket yang dilaporkan
            $table->unsignedBigInteger('id_piket'); // -> piket.id_piket
            $table->foreign('id_piket')->references('id_piket')->on('piket')->onDelete('cascade');

            // redundansi cepat query
            $table->foreignId('petugas_id')->constrained('users')->onDelete('cascade');

            // jam mulai & selesai
            $table->timestamp('mulai_at')->nullable();
            $table->timestamp('selesai_at')->nullable();

            // status kehadiran
            $table->enum('status_kehadiran', ['tepat_waktu','terlambat_a','terlambat_b','tidak_hadir'])->nullable();

            // catatan/isi laporan
            $table->text('catatan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_piket');
    }
};
