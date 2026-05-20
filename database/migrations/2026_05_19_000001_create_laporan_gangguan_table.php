<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_gangguan', function (Blueprint $table) {
            $table->id();
            $table->string('no_laporan')->unique(); // KMR-[kamar]-[urut]
            $table->string('nama_penghuni');
            $table->string('no_kamar');
            $table->enum('kategori', ['Listrik', 'Air', 'Furnitur', 'Pintu & Kunci', 'Internet', 'Lainnya']);
            $table->text('deskripsi');
            $table->enum('urgensi', ['normal', 'mendesak'])->default('normal');
            $table->string('foto_bukti')->nullable();
            $table->enum('status', ['baru', 'diproses', 'selesai'])->default('baru');
            $table->text('catatan_admin')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_gangguan');
    }
};
