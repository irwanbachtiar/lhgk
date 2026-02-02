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
        Schema::table('lhgk', function (Blueprint $table) {
            $table->string('NM_BRANCH')->nullable()->after('NM_PERS_PANDU');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lhgk', function (Blueprint $table) {
            $table->dropColumn('NM_BRANCH');
        });
    }
};
