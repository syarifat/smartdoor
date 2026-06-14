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
            $table->string('uid_kartu_cadangan', 50)->nullable()->unique()->after('kamar_id')->comment('UID Kartu e-KTP / Cadangan');
        });
    }

    public function down(): void
    {
        Schema::table('penghunis', function (Blueprint $table) {
            $table->dropColumn('uid_kartu_cadangan');
        });
    }
};
