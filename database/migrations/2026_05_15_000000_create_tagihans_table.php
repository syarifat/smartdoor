<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tagihans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penghuni_id')->constrained('penghunis')->cascadeOnDelete();
            $table->foreignId('kamar_id')->constrained('kamars')->cascadeOnDelete();
            $table->string('bulan');
            $table->decimal('jumlah_tagihan', 10, 2);
            $table->enum('status', ['belum_bayar', 'menunggu_verifikasi', 'lunas'])->default('belum_bayar');
            $table->string('midtrans_order_id')->nullable()->unique();
            $table->string('midtrans_token')->nullable();
            $table->string('midtrans_url')->nullable();
            $table->text('midtrans_response')->nullable();
            $table->date('tanggal_tagihan');
            $table->timestamp('tanggal_bayar')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tagihans');
    }
};
