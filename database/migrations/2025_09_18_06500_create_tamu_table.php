<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tamu', function (Blueprint $table) {
            $table->id(); // primary key default Laravel = "id"
            $table->string('nama');
            $table->string('no_hp')->nullable();        // nomor HP/WA
            $table->string('email')->nullable();        // email opsional
            $table->string('asal_instansi')->nullable(); // asal instansi / institusi
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->timestamp('waktu_kunjungan')->nullable(); // lebih aman daripada "timestamp"
            $table->text('alamat')->nullable();         // tambahan kalau memang ada di excel
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('tamu');
    }
};