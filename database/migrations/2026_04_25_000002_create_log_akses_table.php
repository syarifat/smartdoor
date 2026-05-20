<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_akses', function (Blueprint $table) {
            $table->id();
            $table->string('uid', 50)->comment('UID kartu yang di-scan');
            $table->foreignId('penghuni_id')->nullable()->constrained('penghunis')->nullOnDelete();
            $table->foreignId('kamar_id')->nullable()->constrained('kamars')->nullOnDelete();
            $table->enum('status', ['berhasil', 'ditolak'])->default('berhasil');
            $table->enum('aksi', ['masuk', 'keluar'])->default('masuk');
            $table->string('keterangan')->nullable()->comment('Contoh: Kartu tidak dikenal');
            $table->timestamp('waktu')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_akses');
    }
};
