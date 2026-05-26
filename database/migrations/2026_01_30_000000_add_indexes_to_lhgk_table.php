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
        $indexes = ['idx_periode' => 'PERIODE', 'idx_nm_branch' => 'NM_BRANCH', 'idx_periode_branch' => 'PERIODE, NM_BRANCH', 'idx_nm_pers_pandu' => 'NM_PERS_PANDU'];
        foreach ($indexes as $name => $columns) {
            try {
                DB::connection('dashboard_phinnisi')->statement("ALTER TABLE lhgk ADD INDEX {$name} ({$columns})");
            } catch (\Exception $e) {
                // index already exists, skip
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::connection('dashboard_phinnisi')->statement('ALTER TABLE lhgk DROP INDEX idx_periode');
        DB::connection('dashboard_phinnisi')->statement('ALTER TABLE lhgk DROP INDEX idx_nm_branch');
        DB::connection('dashboard_phinnisi')->statement('ALTER TABLE lhgk DROP INDEX idx_periode_branch');
        DB::connection('dashboard_phinnisi')->statement('ALTER TABLE lhgk DROP INDEX idx_nm_pers_pandu');
    }
};
