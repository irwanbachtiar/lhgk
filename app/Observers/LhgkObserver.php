<?php

namespace App\Observers;

use App\Models\Lhgk;

class LhgkObserver
{
    /**
     * Handle the Lhgk "creating" event.
     */
    public function creating(Lhgk $lhgk): void
    {
        $this->fillCalculatedFields($lhgk);
    }

    /**
     * Handle the Lhgk "updating" event.
     */
    public function updating(Lhgk $lhgk): void
    {
        $this->fillCalculatedFields($lhgk);
    }

    /**
     * Fill calculated fields based on existing data
     */
    private function fillCalculatedFields(Lhgk $lhgk): void
    {
        // Fill RANGE_GT and RANGE_GT_LABEL based on KP_GRT
        if (!empty($lhgk->KP_GRT) && empty($lhgk->RANGE_GT)) {
            $grt = (float) $lhgk->KP_GRT;
            
            if ($grt >= 0 && $grt <= 3500) {
                $lhgk->RANGE_GT = '0-3500 GT';
                $lhgk->RANGE_GT_LABEL = 'A';
            } elseif ($grt >= 3501 && $grt <= 8000) {
                $lhgk->RANGE_GT = '3501-8000 GT';
                $lhgk->RANGE_GT_LABEL = 'B';
            } elseif ($grt >= 8001 && $grt <= 14000) {
                $lhgk->RANGE_GT = '8001-14000 GT';
                $lhgk->RANGE_GT_LABEL = 'C';
            } elseif ($grt >= 14001 && $grt <= 18000) {
                $lhgk->RANGE_GT = '14001-18000 GT';
                $lhgk->RANGE_GT_LABEL = 'D';
            } elseif ($grt >= 18001 && $grt <= 26000) {
                $lhgk->RANGE_GT = '18001-26000 GT';
                $lhgk->RANGE_GT_LABEL = 'E';
            } elseif ($grt >= 26001 && $grt <= 40000) {
                $lhgk->RANGE_GT = '26001-40000 GT';
                $lhgk->RANGE_GT_LABEL = 'F';
            } elseif ($grt >= 40001 && $grt <= 75000) {
                $lhgk->RANGE_GT = '40001-75000 GT';
                $lhgk->RANGE_GT_LABEL = 'G';
            } elseif ($grt > 75000) {
                $lhgk->RANGE_GT = '>75000 GT';
                $lhgk->RANGE_GT_LABEL = 'H';
            }
        }

        // Fill JENIS_KAPAL_DARI_BENDERA based on KD_BENDERA
        if (!empty($lhgk->KD_BENDERA) && empty($lhgk->JENIS_KAPAL_DARI_BENDERA)) {
            $bendera = strtoupper(trim($lhgk->KD_BENDERA));
            if (in_array($bendera, ['ID', 'IDN', 'INDONESIA'])) {
                $lhgk->JENIS_KAPAL_DARI_BENDERA = 'KAPAL NASIONAL';
            } else {
                $lhgk->JENIS_KAPAL_DARI_BENDERA = 'KAPAL ASING';
            }
        }

        // Fill TOTAL_PENDAPATAN_PANDU_CLEAN from PENDAPATAN_PANDU
        if (!empty($lhgk->PENDAPATAN_PANDU) && empty($lhgk->TOTAL_PENDAPATAN_PANDU_CLEAN)) {
            $lhgk->TOTAL_PENDAPATAN_PANDU_CLEAN = $lhgk->PENDAPATAN_PANDU;
        }

        // Fill TOTAL_PENDAPATAN_TUNDA_CLEAN from PENDAPATAN_TUNDA
        if (!empty($lhgk->PENDAPATAN_TUNDA) && empty($lhgk->TOTAL_PENDAPATAN_TUNDA_CLEAN)) {
            $lhgk->TOTAL_PENDAPATAN_TUNDA_CLEAN = $lhgk->PENDAPATAN_TUNDA;
        }
    }
}
