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
        Schema::create('lhgk', function (Blueprint $table) {
            $table->id();
            $table->string('NM_PERS_PANDU')->nullable();
            $table->string('NM_BRANCH')->nullable();
            $table->decimal('PENDAPATAN_PANDU', 15, 2)->nullable();
            $table->decimal('PENDAPATAN_TUNDA', 15, 2)->nullable();
            $table->string('NM_KAPAL')->nullable();
            $table->string('JN_KAPAL')->nullable();
            $table->decimal('KP_GRT', 15, 2)->nullable();
            $table->string('PILOT_DEPLOY')->nullable();
            $table->string('mulai_pelaksanaan')->nullable();
            $table->string('selesai_pelaksanaan')->nullable();
            $table->string('REALISAS_PILOT_VIA')->nullable();
            $table->string('PERIODE')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lhgk');
    }
};
