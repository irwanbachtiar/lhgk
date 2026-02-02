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
        Schema::create('pandu_prod', function (Blueprint $table) {
            $table->id();
            $table->string('NM_PERS_PANDU')->nullable();
            $table->text('NM_BRANCH')->nullable();
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
            $table->string('TGL_PMT')->nullable();
            $table->string('JAM_PMT')->nullable();
            $table->string('PNK')->nullable();
            $table->string('NO_NOTA')->nullable();
            $table->date('TGL_NOTA')->nullable();
            $table->string('STATUS_NOTA')->nullable();
            $table->text('KETERANGAN')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pandu_prod');
    }
};
