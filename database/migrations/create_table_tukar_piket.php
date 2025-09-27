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
        Schema::create('tukar_piket', function (Blueprint $table) {
            $table->id('id_tukar');

            // piket yang ingin ditukar
            $table->unsignedBigInteger('id_piket_awal');
            $table->unsignedBigInteger('id_piket_tukar');

            // informasi tanggal & sesi
            $table->date('tanggal_awal');
            $table->date('tanggal_tukar');
            $table->string('sesi_awal');
            $table->string('sesi_tukar');

            // petugas yang mengajukan tukar
            $table->unsignedBigInteger('petugas_tukar');

            // alasan & status
            $table->text('alasan')->nullable();
            $table->enum('status', ['pending', 'disetujui', 'ditolak'])->default('pending');

            $table->timestamps();

            // foreign keys
            $table->foreign('id_piket_awal')->references('id_piket')->on('piket_petugas')->onDelete('cascade');
            $table->foreign('id_piket_tukar')->references('id_piket')->on('piket_petugas')->onDelete('cascade');
            $table->foreign('petugas_tukar')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tukar_piket');
    }
};