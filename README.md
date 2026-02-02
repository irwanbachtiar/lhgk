# Dashboard LHGK (Laporan Harian Gerak Kapal)

Dashboard untuk monitoring dan analisis data pandu kapal dengan fitur upload CSV dan visualisasi statistik.

## Fitur Utama

✅ **Dashboard Statistik**
- Tampilan statistik per pandu
- Total pendapatan pandu dan tunda
- Filter berdasarkan periode
- Grafik visualisasi data

✅ **Upload CSV**
- Import data dari file CSV
- Auto-detect encoding (UTF-8, ISO-8859-1, dll)
- Validasi data otomatis
- Error handling per baris
- Transaction support (rollback on major errors)

✅ **Analisis Data**
- Breakdown per jenis kapal
- Statistik GRT (Gross Registered Tonnage)
- Tracking via realisasi (Mobile/Web/Partial)
- Filter periode dinamis

## Teknologi

- **Laravel 11.x** - PHP Framework
- **MySQL/MariaDB** - Database
- **Bootstrap 5** - UI Framework
- **Chart.js** - Data Visualization

## Quick Start

### 1. Install Dependencies
```bash
composer install
```

### 2. Setup Environment
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Configure Database
Edit file `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lhgk
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Run Migration
```bash
php artisan migrate
```

### 5. Start Server
```bash
php artisan serve
```

Akses: `http://localhost:8000`

## Upload CSV

### Format File CSV

File CSV harus memiliki header dengan kolom berikut:

```csv
NM_PERS_PANDU,PENDAPATAN_PANDU,PENDAPATAN_TUNDA,NM_KAPAL,JN_KAPAL,KP_GRT,PILOT_DEPLOY,REALISAS_PILOT_VIA,PERIODE
```

**Contoh:**
```csv
NM_PERS_PANDU,PENDAPATAN_PANDU,PENDAPATAN_TUNDA,NM_KAPAL,JN_KAPAL,KP_GRT,PILOT_DEPLOY,REALISAS_PILOT_VIA,PERIODE
Kapten Ahmad,2500000,500000,KM Sinar Jaya,Kapal Penumpang,5000,01-12-2024,MOBILE,12-2024
Kapten Budi,3000000,750000,MV Ocean Star,Kapal Kargo,8000,05-12-2024,WEB,12-2024
```

File contoh tersedia di: `contoh_upload.csv`

### Cara Upload

1. Buka dashboard di browser
2. Scroll ke bagian "Upload File CSV"
3. Pilih file CSV (maksimal 10MB)
4. Klik tombol "Upload"
5. Lihat hasil import

**Lihat panduan lengkap:** [PANDUAN_UPLOAD.md](PANDUAN_UPLOAD.md)

## Struktur Project

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
