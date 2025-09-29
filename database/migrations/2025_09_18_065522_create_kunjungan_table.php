<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('kunjungan', function (Blueprint $table) {
            $table->id();
            
            // relasi ke tamu (foreign key)
            $table->foreignId('tamu_id')
                  ->constrained('tamu')
                  ->onDelete('cascade');
            
            $table->dateTime('tanggal_kunjungan')->nullable();
            $table->string('keperluan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('kunjungan');
    }
};