<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Menambahkan 'hilang' ke tipe ENUM menggunakan raw statement karena modify() enum seringkali bermasalah di Laravel
        DB::statement("ALTER TABLE kartu MODIFY COLUMN status ENUM('aktif', 'nonaktif', 'diblokir', 'hilang') DEFAULT 'aktif'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE kartu MODIFY COLUMN status ENUM('aktif', 'nonaktif', 'diblokir') DEFAULT 'aktif'");
    }
};
