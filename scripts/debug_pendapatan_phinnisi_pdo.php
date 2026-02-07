<?php
// Usage: php scripts/debug_pendapatan_phinnisi_pdo.php [cabang]
$env = parse_ini_file(__DIR__ . '/../.env');
$host = $env['DB_PHINNISI_HOST'] ?? $env['DB_HOST'] ?? '127.0.0.1';
$port = $env['DB_PHINNISI_PORT'] ?? $env['DB_PORT'] ?? '3306';
$db = $env['DB_PHINNISI_DATABASE'] ?? 'dashboard_phinnisi';
$user = $env['DB_PHINNISI_USERNAME'] ?? $env['DB_USERNAME'] ?? 'root';
$pass = $env['DB_PHINNISI_PASSWORD'] ?? $env['DB_PASSWORD'] ?? '';
$cabang = $argv[1] ?? 'all';

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $out = ['cabang' => $cabang];

    // check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'pendapatan_sap'");
    $has = $stmt->fetchAll(PDO::FETCH_NUM);
    $hasPendapatan = count($has) > 0;
    $out['has_pendapatan_sap'] = $hasPendapatan;

    if ($hasPendapatan) {
        // columns
        $cols = $pdo->query("SHOW COLUMNS FROM pendapatan_sap")->fetchAll(PDO::FETCH_ASSOC);
        $fields = array_map(function($c){ return $c['Field']; }, $cols);
        $out['columns'] = $fields;

        // detect tanggal nota col
        $tanggalNotaCol = null;
        foreach ($fields as $f) {
            $normalized = strtolower(str_replace([' ', '_'], '', $f));
            if (in_array($normalized, ['tanggalnota','tglnota','tanggalket','tanggaldok','tanggal','tanggaldokumen','invoicedate','invoicedate'])) {
                $tanggalNotaCol = $f; break;
            }
        }
        $out['tanggal_nota_col'] = $tanggalNotaCol;

        // detect profit center col
        $sapProfitCenterCol = null;
        foreach ($fields as $f) {
            if (strtolower(str_replace([' ', '_'], '', $f)) === 'profitcenter' || strtolower($f) === 'profit_center') { $sapProfitCenterCol = $f; break; }
        }
        $out['sap_profit_center_col'] = $sapProfitCenterCol;

        // sample profit_center table
        try {
            $pcStmt = $pdo->query("SELECT profit_center, name_branch FROM profit_center LIMIT 10");
            $pcSample = $pcStmt->fetchAll(PDO::FETCH_ASSOC);
            $out['profit_center_sample'] = $pcSample;
        } catch (Exception $e) {
            $out['profit_center_sample_error'] = $e->getMessage();
        }

        // periods overall if tanggal col
        if ($tanggalNotaCol) {
            try {
                $sql = "SELECT DISTINCT DATE_FORMAT(STR_TO_DATE($tanggalNotaCol, '%d-%m-%Y'), '%m-%Y') as periode FROM pendapatan_sap WHERE $tanggalNotaCol IS NOT NULL AND $tanggalNotaCol != '' ORDER BY STR_TO_DATE(CONCAT('01-', DATE_FORMAT(STR_TO_DATE($tanggalNotaCol, '%d-%m-%Y'), '%m-%Y')), '%d-%m-%Y') DESC LIMIT 50";
                $pStmt = $pdo->query($sql);
                $periods = array_column($pStmt->fetchAll(PDO::FETCH_ASSOC), 'periode');
                $out['periods_overall_sample'] = $periods;
            } catch (Exception $e) {
                $out['periods_overall_error'] = $e->getMessage();
            }
        }

        // periods for cabang
        if ($cabang !== 'all' && $tanggalNotaCol) {
            try {
                if ($sapProfitCenterCol) {
                    $sql = "SELECT DISTINCT DATE_FORMAT(STR_TO_DATE(p.$tanggalNotaCol, '%d-%m-%Y'), '%m-%Y') as periode FROM pendapatan_sap p JOIN profit_center pc ON p.$sapProfitCenterCol = pc.profit_center WHERE pc.name_branch = :cabang AND p.$tanggalNotaCol IS NOT NULL AND p.$tanggalNotaCol != '' ORDER BY STR_TO_DATE(CONCAT('01-', DATE_FORMAT(STR_TO_DATE(p.$tanggalNotaCol, '%d-%m-%Y'), '%m-%Y')), '%d-%m-%Y') DESC LIMIT 50";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([':cabang' => $cabang]);
                    $periodsFor = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'periode');
                    $out['periods_for_cabang_sample'] = $periodsFor;
                } else {
                    $sql = "SELECT DISTINCT DATE_FORMAT(STR_TO_DATE($tanggalNotaCol, '%d-%m-%Y'), '%m-%Y') as periode FROM pendapatan_sap WHERE (NAME_BRANCH = :cabang OR name_branch = :cabang OR CABANG = :cabang OR PROFIT_CENTER = :cabang) AND $tanggalNotaCol IS NOT NULL AND $tanggalNotaCol != '' ORDER BY STR_TO_DATE(CONCAT('01-', DATE_FORMAT(STR_TO_DATE($tanggalNotaCol, '%d-%m-%Y'), '%m-%Y')), '%d-%m-%Y') DESC LIMIT 50";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([':cabang' => $cabang]);
                    $out['periods_for_cabang_sample'] = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'periode');
                }
            } catch (Exception $e) {
                $out['periods_for_cabang_error'] = $e->getMessage();
            }
        }

    } else {
        // list tables
        try {
            $tstmt = $pdo->query('SHOW TABLES');
            $out['tables'] = $tstmt->fetchAll(PDO::FETCH_NUM);
        } catch (Exception $e) {
            $out['tables_error'] = $e->getMessage();
        }
    }

    echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
