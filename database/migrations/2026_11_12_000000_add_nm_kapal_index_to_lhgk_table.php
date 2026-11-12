<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            DB::connection('dashboard_phinnisi')->statement('ALTER TABLE lhgk ADD INDEX idx_nm_kapal (NM_KAPAL)');
        } catch (\Exception $e) {
            // index already exists, skip
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::connection('dashboard_phinnisi')->statement('ALTER TABLE lhgk DROP INDEX idx_nm_kapal');
    }
};
