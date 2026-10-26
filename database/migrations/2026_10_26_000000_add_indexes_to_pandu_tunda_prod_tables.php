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
        $panduIndexes = [
            'idx_pandu_prod_name_branch' => 'NAME_BRANCH(191)',
            'idx_pandu_prod_billing' => 'BILLING(191)',
            'idx_pandu_prod_invoice' => 'INVOICE(191)',
            'idx_pandu_prod_pilot' => 'PILOT(191)',
            'idx_pandu_prod_shipping_agent' => 'SHIPPING_AGENT(191)',
        ];
        foreach ($panduIndexes as $name => $columns) {
            try {
                DB::connection('dashboard_phinnisi')->statement("ALTER TABLE pandu_prod ADD INDEX {$name} ({$columns})");
            } catch (\Exception $e) {
                // index already exists, skip
            }
        }

        $tundaIndexes = [
            'idx_tunda_prod_name_branch' => 'NAME_BRANCH(191)',
            'idx_tunda_prod_billing' => 'BILLING(191)',
            'idx_tunda_prod_nama_tunda' => 'NAMA_TUNDA(191)',
            'idx_tunda_prod_shipping_agent' => 'SHIPPING_AGENT(191)',
        ];
        foreach ($tundaIndexes as $name => $columns) {
            try {
                DB::connection('dashboard_phinnisi')->statement("ALTER TABLE tunda_prod ADD INDEX {$name} ({$columns})");
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
        foreach (['idx_pandu_prod_name_branch', 'idx_pandu_prod_billing', 'idx_pandu_prod_invoice', 'idx_pandu_prod_pilot', 'idx_pandu_prod_shipping_agent'] as $name) {
            try {
                DB::connection('dashboard_phinnisi')->statement("ALTER TABLE pandu_prod DROP INDEX {$name}");
            } catch (\Exception $e) {
                // index doesn't exist, skip
            }
        }

        foreach (['idx_tunda_prod_name_branch', 'idx_tunda_prod_billing', 'idx_tunda_prod_nama_tunda', 'idx_tunda_prod_shipping_agent'] as $name) {
            try {
                DB::connection('dashboard_phinnisi')->statement("ALTER TABLE tunda_prod DROP INDEX {$name}");
            } catch (\Exception $e) {
                // index doesn't exist, skip
            }
        }
    }
};
