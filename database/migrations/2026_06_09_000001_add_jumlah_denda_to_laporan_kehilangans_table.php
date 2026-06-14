<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan_kehilangans', function (Blueprint $table) {
            $table->decimal('jumlah_denda', 10, 2)->nullable()->after('status');
            $table->boolean('denda_ditagihkan')->default(false)->after('jumlah_denda');
        });
    }

    public function down(): void
    {
        Schema::table('laporan_kehilangans', function (Blueprint $table) {
            $table->dropColumn(['jumlah_denda', 'denda_ditagihkan']);
        });
    }
};
