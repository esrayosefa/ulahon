<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kunjungan', function (Blueprint $table) {
            $table->id('id_kunjungan');
            $table->unsignedBigInteger('id_pengguna'); // tamu
            $table->timestamp('timestamp_berkunjung')->useCurrent();
            $table->string('moda_kunjungan')->nullable();
            $table->string('tujuan_kunjungan')->nullable();
            $table->string('foto')->nullable();
            $table->string('nomor_antrian')->nullable();

            $table->timestamps();

            // relasi
            $table->foreign('id_pengguna')->references('id_pengguna')->on('tamu')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kunjungan');
    }
};