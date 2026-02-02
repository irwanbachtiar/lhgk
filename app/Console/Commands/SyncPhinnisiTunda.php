<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Facades\DB;

class SyncPhinnisiTunda extends Command
{
    protected $signature = 'phinnisi:sync-tunda {--periode=}';
    protected $description = 'Sync data tunda dari Phinnisi Pelindo';

    public function handle()
    {
        $this->info('Starting Phinnisi Tunda sync...');
        
        try {
            $downloadPath = env('PHINNISI_DOWNLOAD_PATH', 'C:\Users\Downloads');
            
            $options = (new ChromeOptions)->setExperimentalOption('prefs', [
                'download.default_directory' => $downloadPath,
                'download.prompt_for_download' => false,
            ]);

            $capabilities = DesiredCapabilities::chrome();
            $capabilities->setCapability(ChromeOptions::CAPABILITY, $options);

            $driver = RemoteWebDriver::create(
                'http://localhost:9515',
                $capabilities
            );

            // Login
            $this->info('Logging in to Phinnisi...');
            $driver->get(env('PHINNISI_URL') . '/login');
            sleep(2);
            
            $driver->findElement(\Facebook\WebDriver\WebDriverBy::name('username'))
                ->sendKeys(env('PHINNISI_USERNAME'));
            $driver->findElement(\Facebook\WebDriver\WebDriverBy::name('password'))
                ->sendKeys(env('PHINNISI_PASSWORD'));
            $driver->findElement(\Facebook\WebDriver\WebDriverBy::cssSelector('button[type="submit"]'))
                ->click();
            
            sleep(3);
            
            // Navigate to tunda report page (sesuaikan URL)
            $this->info('Navigating to Tunda report...');
            $driver->get(env('PHINNISI_URL') . '/reporting/laporan-tunda'); // TODO: sesuaikan URL
            
            sleep(2);
            
            if ($periode = $this->option('periode')) {
                $this->info("Setting periode filter: {$periode}");
            }
            
            $this->info('Clicking Export Excel...');
            $driver->findElement(\Facebook\WebDriver\WebDriverBy::cssSelector('button.export-excel'))
                ->click();
            
            $this->info('Waiting for download...');
            $maxWait = 60;
            $waited = 0;
            $filename = null;
            
            while ($waited < $maxWait) {
                sleep(1);
                $waited++;
                
                $files = glob($downloadPath . '/*.xlsx');
                if (!empty($files)) {
                    usort($files, function($a, $b) {
                        return filemtime($b) - filemtime($a);
                    });
                    
                    $latestFile = $files[0];
                    if (filemtime($latestFile) > time() - 60) {
                        $filename = $latestFile;
                        break;
                    }
                }
            }
            
            $driver->quit();
            
            if (!$filename) {
                $this->error('Download timeout or file not found');
                return 1;
            }
            
            $this->info("File downloaded: {$filename}");
            
            $imported = $this->importExcelToDatabase($filename);
            $this->info("Successfully imported {$imported} records");
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
    
    private function importExcelToDatabase($filename)
    {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filename);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();
        
        $header = array_shift($rows);
        
        $imported = 0;
        $batch = [];
        $batchSize = 500;
        
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::statement('SET AUTOCOMMIT=0');
        
        foreach ($rows as $row) {
            // TODO: Sesuaikan mapping dengan struktur Excel tunda
            $data = [
                'BILLING' => $row[0] ?? null,
                'REVENUE' => $row[1] ?? null,
                'INVOICE_DATE' => $row[2] ?? null,
                // ... tambahkan kolom lainnya sesuai tunda_prod
            ];
            
            if (empty(array_filter($data))) {
                continue;
            }
            
            $batch[] = $data;
            
            if (count($batch) >= $batchSize) {
                try {
                    DB::table('tunda_prod')->insert($batch);
                    $imported += count($batch);
                    $batch = [];
                } catch (\Exception $e) {
                    $this->warn("Batch insert failed: " . $e->getMessage());
                }
            }
        }
        
        if (!empty($batch)) {
            try {
                DB::table('tunda_prod')->insert($batch);
                $imported += count($batch);
            } catch (\Exception $e) {
                $this->warn("Final batch insert failed: " . $e->getMessage());
            }
        }
        
        DB::statement('COMMIT');
        DB::statement('SET AUTOCOMMIT=1');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        return $imported;
    }
}
