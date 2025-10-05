<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('layanan', function (Blueprint $table) {
            $table->id();

            // relasi ke kunjungan
            $table->foreignId('kunjungan_id')
                  ->constrained('kunjungan')
                  ->onDelete('cascade');

            // relasi ke users (petugas yang melayani)
            $table->foreignId('petugas_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->string('jenis_layanan');
            $table->text('deskripsi')->nullable();
            $table->text('kebutuhan_data')->nullable();
            $table->string('foto_bukti_path')->nullable()->after('deskripsi');

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('layanan');
    }
};