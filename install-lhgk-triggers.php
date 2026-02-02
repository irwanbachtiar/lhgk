<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Installing Database Triggers for lhgk table ===\n\n";

// Drop existing triggers if any
echo "Dropping existing triggers (if any)...\n";
try {
    DB::connection('dashboard_phinnisi')->statement('DROP TRIGGER IF EXISTS lhgk_before_insert');
    DB::connection('dashboard_phinnisi')->statement('DROP TRIGGER IF EXISTS lhgk_before_update');
    echo "  Existing triggers dropped\n\n";
} catch (\Exception $e) {
    echo "  No existing triggers found\n\n";
}

// Create BEFORE INSERT trigger
echo "Creating BEFORE INSERT trigger...\n";
$triggerInsert = "
CREATE TRIGGER lhgk_before_insert 
BEFORE INSERT ON lhgk
FOR EACH ROW
BEGIN
    -- Fill RANGE_GT and RANGE_GT_LABEL
    IF NEW.KP_GRT IS NOT NULL AND (NEW.RANGE_GT IS NULL OR NEW.RANGE_GT = '') THEN
        CASE
            WHEN NEW.KP_GRT BETWEEN 0 AND 3500 THEN
                SET NEW.RANGE_GT = '0-3500 GT', NEW.RANGE_GT_LABEL = 'A';
            WHEN NEW.KP_GRT BETWEEN 3501 AND 8000 THEN
                SET NEW.RANGE_GT = '3501-8000 GT', NEW.RANGE_GT_LABEL = 'B';
            WHEN NEW.KP_GRT BETWEEN 8001 AND 14000 THEN
                SET NEW.RANGE_GT = '8001-14000 GT', NEW.RANGE_GT_LABEL = 'C';
            WHEN NEW.KP_GRT BETWEEN 14001 AND 18000 THEN
                SET NEW.RANGE_GT = '14001-18000 GT', NEW.RANGE_GT_LABEL = 'D';
            WHEN NEW.KP_GRT BETWEEN 18001 AND 26000 THEN
                SET NEW.RANGE_GT = '18001-26000 GT', NEW.RANGE_GT_LABEL = 'E';
            WHEN NEW.KP_GRT BETWEEN 26001 AND 40000 THEN
                SET NEW.RANGE_GT = '26001-40000 GT', NEW.RANGE_GT_LABEL = 'F';
            WHEN NEW.KP_GRT BETWEEN 40001 AND 75000 THEN
                SET NEW.RANGE_GT = '40001-75000 GT', NEW.RANGE_GT_LABEL = 'G';
            WHEN NEW.KP_GRT > 75000 THEN
                SET NEW.RANGE_GT = '>75000 GT', NEW.RANGE_GT_LABEL = 'H';
        END CASE;
    END IF;
    
    -- Fill JENIS_KAPAL_DARI_BENDERA
    IF NEW.KD_BENDERA IS NOT NULL AND (NEW.JENIS_KAPAL_DARI_BENDERA IS NULL OR NEW.JENIS_KAPAL_DARI_BENDERA = '') THEN
        IF UPPER(NEW.KD_BENDERA) IN ('ID', 'IDN', 'INDONESIA') THEN
            SET NEW.JENIS_KAPAL_DARI_BENDERA = 'KAPAL NASIONAL';
        ELSE
            SET NEW.JENIS_KAPAL_DARI_BENDERA = 'KAPAL ASING';
        END IF;
    END IF;
    
    -- Fill TOTAL_PENDAPATAN_PANDU_CLEAN
    IF NEW.PENDAPATAN_PANDU IS NOT NULL AND (NEW.TOTAL_PENDAPATAN_PANDU_CLEAN IS NULL OR NEW.TOTAL_PENDAPATAN_PANDU_CLEAN = 0) THEN
        SET NEW.TOTAL_PENDAPATAN_PANDU_CLEAN = NEW.PENDAPATAN_PANDU;
    END IF;
    
    -- Fill TOTAL_PENDAPATAN_TUNDA_CLEAN
    IF NEW.PENDAPATAN_TUNDA IS NOT NULL AND (NEW.TOTAL_PENDAPATAN_TUNDA_CLEAN IS NULL OR NEW.TOTAL_PENDAPATAN_TUNDA_CLEAN = 0) THEN
        SET NEW.TOTAL_PENDAPATAN_TUNDA_CLEAN = NEW.PENDAPATAN_TUNDA;
    END IF;
END
";

try {
    DB::connection('dashboard_phinnisi')->statement($triggerInsert);
    echo "  ✓ BEFORE INSERT trigger created successfully\n\n";
} catch (\Exception $e) {
    echo "  ✗ Error creating BEFORE INSERT trigger: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Create BEFORE UPDATE trigger
echo "Creating BEFORE UPDATE trigger...\n";
$triggerUpdate = "
CREATE TRIGGER lhgk_before_update 
BEFORE UPDATE ON lhgk
FOR EACH ROW
BEGIN
    -- Fill RANGE_GT and RANGE_GT_LABEL
    IF NEW.KP_GRT IS NOT NULL AND (NEW.RANGE_GT IS NULL OR NEW.RANGE_GT = '') THEN
        CASE
            WHEN NEW.KP_GRT BETWEEN 0 AND 3500 THEN
                SET NEW.RANGE_GT = '0-3500 GT', NEW.RANGE_GT_LABEL = 'A';
            WHEN NEW.KP_GRT BETWEEN 3501 AND 8000 THEN
                SET NEW.RANGE_GT = '3501-8000 GT', NEW.RANGE_GT_LABEL = 'B';
            WHEN NEW.KP_GRT BETWEEN 8001 AND 14000 THEN
                SET NEW.RANGE_GT = '8001-14000 GT', NEW.RANGE_GT_LABEL = 'C';
            WHEN NEW.KP_GRT BETWEEN 14001 AND 18000 THEN
                SET NEW.RANGE_GT = '14001-18000 GT', NEW.RANGE_GT_LABEL = 'D';
            WHEN NEW.KP_GRT BETWEEN 18001 AND 26000 THEN
                SET NEW.RANGE_GT = '18001-26000 GT', NEW.RANGE_GT_LABEL = 'E';
            WHEN NEW.KP_GRT BETWEEN 26001 AND 40000 THEN
                SET NEW.RANGE_GT = '26001-40000 GT', NEW.RANGE_GT_LABEL = 'F';
            WHEN NEW.KP_GRT BETWEEN 40001 AND 75000 THEN
                SET NEW.RANGE_GT = '40001-75000 GT', NEW.RANGE_GT_LABEL = 'G';
            WHEN NEW.KP_GRT > 75000 THEN
                SET NEW.RANGE_GT = '>75000 GT', NEW.RANGE_GT_LABEL = 'H';
        END CASE;
    END IF;
    
    -- Fill JENIS_KAPAL_DARI_BENDERA
    IF NEW.KD_BENDERA IS NOT NULL AND (NEW.JENIS_KAPAL_DARI_BENDERA IS NULL OR NEW.JENIS_KAPAL_DARI_BENDERA = '') THEN
        IF UPPER(NEW.KD_BENDERA) IN ('ID', 'IDN', 'INDONESIA') THEN
            SET NEW.JENIS_KAPAL_DARI_BENDERA = 'KAPAL NASIONAL';
        ELSE
            SET NEW.JENIS_KAPAL_DARI_BENDERA = 'KAPAL ASING';
        END IF;
    END IF;
    
    -- Fill TOTAL_PENDAPATAN_PANDU_CLEAN
    IF NEW.PENDAPATAN_PANDU IS NOT NULL AND (NEW.TOTAL_PENDAPATAN_PANDU_CLEAN IS NULL OR NEW.TOTAL_PENDAPATAN_PANDU_CLEAN = 0) THEN
        SET NEW.TOTAL_PENDAPATAN_PANDU_CLEAN = NEW.PENDAPATAN_PANDU;
    END IF;
    
    -- Fill TOTAL_PENDAPATAN_TUNDA_CLEAN
    IF NEW.PENDAPATAN_TUNDA IS NOT NULL AND (NEW.TOTAL_PENDAPATAN_TUNDA_CLEAN IS NULL OR NEW.TOTAL_PENDAPATAN_TUNDA_CLEAN = 0) THEN
        SET NEW.TOTAL_PENDAPATAN_TUNDA_CLEAN = NEW.PENDAPATAN_TUNDA;
    END IF;
END
";

try {
    DB::connection('dashboard_phinnisi')->statement($triggerUpdate);
    echo "  ✓ BEFORE UPDATE trigger created successfully\n\n";
} catch (\Exception $e) {
    echo "  ✗ Error creating BEFORE UPDATE trigger: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Verify triggers
echo "=== Verifying triggers ===\n";
$triggers = DB::connection('dashboard_phinnisi')->select("SHOW TRIGGERS WHERE `Table` = 'lhgk'");
foreach ($triggers as $trigger) {
    echo "  ✓ {$trigger->Trigger} - {$trigger->Event} {$trigger->Timing}\n";
}

echo "\n=== Installation Complete! ===\n";
echo "Triggers are now active. Any INSERT or UPDATE to lhgk table will automatically fill:\n";
echo "  - RANGE_GT & RANGE_GT_LABEL (based on KP_GRT)\n";
echo "  - JENIS_KAPAL_DARI_BENDERA (based on KD_BENDERA)\n";
echo "  - TOTAL_PENDAPATAN_PANDU_CLEAN (from PENDAPATAN_PANDU)\n";
echo "  - TOTAL_PENDAPATAN_TUNDA_CLEAN (from PENDAPATAN_TUNDA)\n";
