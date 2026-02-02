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
            // Check if columns don't exist before adding
            if (!Schema::hasColumn('lhgk', 'mulai_pelaksanaan')) {
                $table->string('mulai_pelaksanaan')->nullable()->after('PILOT_DEPLOY');
            }
            if (!Schema::hasColumn('lhgk', 'selesai_pelaksanaan')) {
                $table->string('selesai_pelaksanaan')->nullable()->after('mulai_pelaksanaan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lhgk', function (Blueprint $table) {
            $table->dropColumn(['mulai_pelaksanaan', 'selesai_pelaksanaan']);
        });
    }
};
