<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monitoring_listrik', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kamar_id')->constrained('kamars')->cascadeOnDelete();
            $table->decimal('kwh', 8, 3)->default(0)->comment('Pemakaian dalam kWh');
            $table->decimal('tegangan', 6, 2)->default(220)->comment('Tegangan (Volt)');
            $table->decimal('arus', 5, 2)->default(0)->comment('Arus (Ampere)');
            $table->decimal('daya', 7, 2)->default(0)->comment('Daya (Watt)');
            $table->timestamp('dicatat_pada')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monitoring_listrik');
    }
};
