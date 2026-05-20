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
        Schema::dropIfExists('monitoring_listrik');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('monitoring_listrik', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kamar_id')->constrained()->cascadeOnDelete();
            $table->decimal('kwh', 8, 3);
            $table->decimal('tegangan', 5, 2);
            $table->decimal('arus', 5, 2);
            $table->decimal('daya', 8, 2);
            $table->timestamp('dicatat_pada');
        });
    }
};
