<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Laravel\Dusk\Browser;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Facades\DB;

class SyncPhinnisiPandu extends Command
{
    protected $signature = 'phinnisi:sync-pandu {--periode=}';
    protected $description = 'Sync data pandu dari Phinnisi Pelindo';

    public function handle()
    {
        $this->info('Starting Phinnisi Pandu sync...');
        
        try {
            // Setup download path
            $downloadPath = env('PHINNISI_DOWNLOAD_PATH', 'C:\Users\Downloads');
            
            $options = (new ChromeOptions)->setExperimentalOption('prefs', [
                'download.default_directory' => $downloadPath,
                'download.prompt_for_download' => false,
            ]);

            $capabilities = DesiredCapabilities::chrome();
            $capabilities->setCapability(ChromeOptions::CAPABILITY, $options);

            // Create browser instance
            $driver = RemoteWebDriver::create(
                'http://localhost:9515',
                $capabilities
            );

            // Login
            $this->info('Logging in to Phinnisi...');
            $driver->get(env('PHINNISI_URL') . '/login');
            
            // Wait for page load
            sleep(2);
            
            // Fill login form (sesuaikan selector dengan HTML Phinnisi)
            $driver->findElement(\Facebook\WebDriver\WebDriverBy::name('username'))
                ->sendKeys(env('PHINNISI_USERNAME'));
            $driver->findElement(\Facebook\WebDriver\WebDriverBy::name('password'))
                ->sendKeys(env('PHINNISI_PASSWORD'));
            
            // Click login button (sesuaikan selector)
            $driver->findElement(\Facebook\WebDriver\WebDriverBy::cssSelector('button[type="submit"]'))
                ->click();
            
            sleep(3); // Wait for login
            
            // Navigate to Laporan COA
            $this->info('Navigating to Laporan COA...');
            $driver->get(env('PHINNISI_URL') . '/reporting/laporan-coa');
            
            sleep(2);
            
            // Set filter periode if provided
            if ($periode = $this->option('periode')) {
                // TODO: Set periode filter (sesuaikan dengan form Phinnisi)
                $this->info("Setting periode filter: {$periode}");
            }
            
            // Click Export Excel button (sesuaikan selector)
            $this->info('Clicking Export Excel...');
            $driver->findElement(\Facebook\WebDriver\WebDriverBy::cssSelector('button.export-excel'))
                ->click();
            
            // Wait for download to complete
            $this->info('Waiting for download...');
            $maxWait = 60; // 60 seconds
            $waited = 0;
            $filename = null;
            
            while ($waited < $maxWait) {
                sleep(1);
                $waited++;
                
                // Check for latest xlsx file
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
            
            // Import Excel to database
            $this->info('Importing to database...');
            $imported = $this->importExcelToDatabase($filename);
            
            $this->info("Successfully imported {$imported} records");
            
            // Clean up downloaded file (optional)
            // unlink($filename);
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
    
    private function importExcelToDatabase($filename)
    {
        // Use PhpSpreadsheet to read Excel
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filename);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();
        
        // Remove header row
        $header = array_shift($rows);
        
        $imported = 0;
        $batch = [];
        $batchSize = 500;
        
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::statement('SET AUTOCOMMIT=0');
        
        foreach ($rows as $row) {
            // Map Excel columns to database columns
            // TODO: Sesuaikan mapping dengan struktur Excel Phinnisi
            $data = [
                'BILLING' => $row[0] ?? null,
                'BILLING_DATE' => $row[1] ?? null,
                'INVOICE_NUMBER' => $row[2] ?? null,
                'INVOICE_DATE' => $row[3] ?? null,
                'VESSEL_NAME' => $row[4] ?? null,
                'PILOT' => $row[5] ?? null,
                'NAME_BRANCH' => $row[6] ?? null,
                'REVENUE' => $row[7] ?? null,
                // ... tambahkan kolom lainnya
            ];
            
            // Skip empty rows
            if (empty(array_filter($data))) {
                continue;
            }
            
            $batch[] = $data;
            
            if (count($batch) >= $batchSize) {
                try {
                    DB::table('pandu_prod')->insert($batch);
                    $imported += count($batch);
                    $batch = [];
                } catch (\Exception $e) {
                    $this->warn("Batch insert failed: " . $e->getMessage());
                }
            }
        }
        
        // Insert remaining
        if (!empty($batch)) {
            try {
                DB::table('pandu_prod')->insert($batch);
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
