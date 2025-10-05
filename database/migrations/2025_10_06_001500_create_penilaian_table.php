<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('penilaian', function (Blueprint $table) {
            $table->id();

            // siapa & layanan apa yang dinilai
            $table->foreignId('layanan_id')->constrained('layanan')->onDelete('cascade');
            $table->foreignId('tamu_id')->nullable()->constrained('tamu')->nullOnDelete();
            $table->foreignId('petugas_id')->constrained('users')->onDelete('cascade');

            // token link penilaian (dibuat saat link dikirim)
            $table->string('token', 64)->unique();

            // hasil penilaian
            $table->unsignedTinyInteger('nilai')->nullable(); // 1..5 (null = belum diisi)
            $table->text('komentar')->nullable();
            $table->timestamp('submitted_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penilaian');
    }
};
