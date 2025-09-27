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
        Schema::create('pengaduan', function (Blueprint $table) {
            $table->id('id_pengaduan');
            
            // relasi dengan tamu/pengguna
            $table->unsignedBigInteger('id_pengguna')->nullable();
            
            // field tambahan sesuai ERD
            $table->date('tanggal')->nullable();
            $table->string('identitas_pelapor')->nullable();
            $table->string('jenis_aduan')->nullable();
            $table->text('deskripsi_aduan')->nullable();
            
            // tindak lanjut (oleh petugas/users)
            $table->unsignedBigInteger('pic_tindak_lanjut')->nullable();
            $table->text('tindak_lanjut')->nullable();
            $table->string('bukti_tindak_lanjut')->nullable();
            
            // status penyelesaian
            $table->enum('status_penyelesaian', ['pending', 'proses', 'selesai'])->default('pending');
            
            $table->timestamps();

            // Foreign keys
            $table->foreign('id_pengguna')->references('id_pengguna')->on('tamu')->onDelete('set null');
            $table->foreign('pic_tindak_lanjut')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaduan');
    }
};