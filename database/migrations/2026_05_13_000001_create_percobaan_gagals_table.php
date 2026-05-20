<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('percobaan_gagals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kamar_id')->constrained('kamars')->onDelete('cascade');
            $table->string('rfid_uid', 50);
            $table->integer('jumlah_percobaan')->default(1);
            $table->string('foto_path')->nullable();
            $table->boolean('sudah_dilihat')->default(false);
            $table->timestamp('waktu')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('percobaan_gagals');
    }
};
