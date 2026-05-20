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
        Schema::table('penghunis', function (Blueprint $table) {
            $table->string('pin', 60)->nullable()->after('kamar_id'); // 60 untuk hash bcrypt
            $table->boolean('pin_aktif')->default(false)->after('pin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penghunis', function (Blueprint $table) {
            $table->dropColumn(['pin', 'pin_aktif']);
        });
    }
};
