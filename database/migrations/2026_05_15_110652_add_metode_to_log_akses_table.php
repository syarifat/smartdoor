<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('log_akses', function (Blueprint $table) {
            $table->enum('metode_akses', ['rfid', 'pin', 'web'])->default('rfid')->after('kamar_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log_akses', function (Blueprint $table) {
            $table->dropColumn('metode_akses');
        });
    }
};
