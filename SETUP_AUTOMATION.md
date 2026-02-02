# Setup Automasi Download dari Phinnisi

## 1. Install Laravel Dusk (Browser Automation)

```bash
composer require --dev laravel/dusk
php artisan dusk:install
```

## 2. Set Credentials di .env

Tambahkan di file `.env`:

```env
PHINNISI_URL=https://phinnisi.pelindo.co.id
PHINNISI_USERNAME=your_username
PHINNISI_PASSWORD=your_password
PHINNISI_DOWNLOAD_PATH=C:\Users\YourUser\Downloads
```

## 3. Jalankan Sync Manual

```bash
php artisan phinnisi:sync-pandu
php artisan phinnisi:sync-tunda
```

## 4. Setup Auto Sync (Optional)

Edit `app/Console/Kernel.php`, uncomment baris schedule:

```php
$schedule->command('phinnisi:sync-pandu')->daily();
$schedule->command('phinnisi:sync-tunda')->daily();
```

Jalankan scheduler:
```bash
php artisan schedule:work
```

## 5. Trigger via Web (Manual)

Akses di browser:
- http://localhost:8000/sync-phinnisi-pandu
- http://localhost:8000/sync-phinnisi-tunda

## Troubleshooting

**Error: ChromeDriver**
```bash
php artisan dusk:chrome-driver --detect
```

**Error: Download folder**
Pastikan path download folder benar di .env

**Error: Login gagal**
Cek username/password di .env

**Error: Selector tidak ditemukan**
Buka file `app/Console/Commands/SyncPhinnisiPandu.php` dan sesuaikan selector CSS sesuai struktur HTML aplikasi Phinnisi
