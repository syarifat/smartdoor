<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kamars', function (Blueprint $table) {
            $table->enum('status_pintu', ['terbuka', 'tertutup'])->default('tertutup')->after('status');
            $table->timestamp('terakhir_diakses')->nullable()->after('status_pintu');
        });
    }

    public function down(): void
    {
        Schema::table('kamars', function (Blueprint $table) {
            $table->dropColumn(['status_pintu', 'terakhir_diakses']);
        });
    }
};
