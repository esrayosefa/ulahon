<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pengaduan', function (Blueprint $table) {
            $table->id();

            // relasi ke layanan
            $table->foreignId('layanan_id')
                  ->constrained('layanan')
                  ->onDelete('cascade');

            $table->date('tanggal')->nullable();
            $table->string('jenis_aduan')->nullable();
            $table->string('identitas_pelapor')->nullable();
            $table->text('isi_pengaduan')->nullable();
            $table->string('pic_tindak_lanjut')->nullable();
            $table->enum('status', ['open','in_progress','resolved'])->default('open');

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('pengaduan');
    }
};