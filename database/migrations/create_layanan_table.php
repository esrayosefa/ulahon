<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('layanan', function (Blueprint $table) {
            $table->id('id_layanan');
            $table->unsignedBigInteger('id_kunjungan');
            $table->unsignedBigInteger('id_petugas'); // user yang melayani
            $table->unsignedBigInteger('id_piket')->nullable(); // sesi piket terkait

            $table->string('jenis_layanan');
            $table->string('foto')->nullable();
            $table->string('nomor_antrian')->nullable();
            $table->timestamp('timestamp_dilayani')->nullable();
            $table->date('tanggal_dilayani')->nullable();

            $table->timestamps();

            // relasi
            $table->foreign('id_kunjungan')->references('id_kunjungan')->on('kunjungan')->onDelete('cascade');
            $table->foreign('id_petugas')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_piket')->references('id_piket')->on('piket_petugas')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('layanan');
    }
};