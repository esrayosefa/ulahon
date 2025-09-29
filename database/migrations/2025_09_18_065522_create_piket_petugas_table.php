<?php

namespace Database\Seeders;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
    {
        Schema::create('piket', function (Blueprint $table) {
            $table->id('id_piket');
            $table->foreignId('id_petugas')->constrained('users')->onDelete('cascade');
            $table->date('tanggal');
            $table->string('shift');
            $table->integer('jumlah_pengunjung');
            $table->string('kendala')->nullable();
            $table->string('dokumentasi')->nullable();
            $table->timestamp('cek_in')->nullable();
            $table->timestamp('cek_out')->nullable();
            $table->timestamps();
        });
    }}

    public function down(): void
    {
        Schema::dropIfExists('piket');
    }
};