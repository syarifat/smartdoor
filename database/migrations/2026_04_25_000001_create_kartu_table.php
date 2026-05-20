<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kartu', function (Blueprint $table) {
            $table->id();
            $table->string('uid', 50)->unique()->comment('UID kartu RFID');
            $table->foreignId('penghuni_id')->nullable()->constrained('penghunis')->nullOnDelete();
            $table->enum('status', ['aktif', 'nonaktif', 'diblokir'])->default('aktif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kartu');
    }
};
