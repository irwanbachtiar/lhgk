<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes to lhgk table for better query performance
        DB::connection('dashboard_phinnisi')->statement('ALTER TABLE lhgk ADD INDEX idx_periode (PERIODE)');
        DB::connection('dashboard_phinnisi')->statement('ALTER TABLE lhgk ADD INDEX idx_nm_branch (NM_BRANCH)');
        DB::connection('dashboard_phinnisi')->statement('ALTER TABLE lhgk ADD INDEX idx_periode_branch (PERIODE, NM_BRANCH)');
        DB::connection('dashboard_phinnisi')->statement('ALTER TABLE lhgk ADD INDEX idx_nm_pers_pandu (NM_PERS_PANDU)');
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
