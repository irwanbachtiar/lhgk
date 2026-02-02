# Solusi Gagal Upload CSV - LHGK Dashboard

## Masalah yang Ditemukan

Setelah analisis mendalam, ditemukan **3 masalah utama** yang menyebabkan gagal upload CSV:

### 1. ❌ View Cache Corrupted
**Status:** ✅ **SUDAH DIPERBAIKI**

Error di file view yang dikompilasi:
```
ParseError: syntax error, unexpected token "endforeach" at storage/framework/views/6cc6128f8b793c8b1a7b47cad23a332c.php:313
```

**Solusi yang sudah dijalankan:**
```bash
php artisan view:clear
php artisan config:clear
php artisan cache:clear
```

### 2. ❌ Kolom Database Tidak Lengkap
**Status:** ⚠️ **PERLU DIPERBAIKI**

Tabel `lhgk` di database **TIDAK memiliki kolom** yang dibutuhkan oleh CSV:
- ❌ `mulai_pelaksanaan` (missing)
- ❌ `selesai_pelaksanaan` (missing)

Kolom ini dibutuhkan untuk:
- Upload CSV (file contoh_upload.csv dan contoh_oktober_2024.csv mengandung kolom ini)
- Analisis kelelahan pandu (menghitung jam kerja)

### 3. ❌ Migration Pending
**Status:** ⚠️ **PERLU DIPERBAIKI**

Migration tidak bisa dijalankan karena tabel sudah ada:
```
2024_12_22_000000_create_lhgk_table ....................... Pending
2025_12_23_012539_add_nm_branch_to_lhgk_table ............. Pending
```

## 🔧 CARA MEMPERBAIKI

### Opsi 1: Jalankan Script Fix (RECOMMENDED)

Saya sudah membuat script `fix-table.php` di root project. Jalankan:

```bash
cd "d:\project ai\lhgk"
php fix-table.php
```

Script ini akan:
1. Cek kolom yang ada di tabel `lhgk`
2. Tambahkan kolom `mulai_pelaksanaan` jika belum ada
3. Tambahkan kolom `selesai_pelaksanaan` jika belum ada
4. Tampilkan hasil

### Opsi 2: Manual via phpMyAdmin/MySQL Client

Jalankan SQL berikut di database `staging_data`:

```sql
-- Cek kolom yang ada
SHOW COLUMNS FROM lhgk;

-- Tambah kolom mulai_pelaksanaan
ALTER TABLE lhgk 
ADD COLUMN mulai_pelaksanaan VARCHAR(255) NULL 
AFTER PILOT_DEPLOY;

-- Tambah kolom selesai_pelaksanaan
ALTER TABLE lhgk 
ADD COLUMN selesai_pelaksanaan VARCHAR(255) NULL 
AFTER mulai_pelaksanaan;

-- Verifikasi
SHOW COLUMNS FROM lhgk;
```

### Opsi 3: Via Laravel Tinker

```bash
php artisan tinker
```

Lalu jalankan:
```php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Cek kolom
$columns = Schema::getColumnListing('lhgk');
print_r($columns);

// Tambah kolom jika belum ada
if (!in_array('mulai_pelaksanaan', $columns)) {
    DB::statement('ALTER TABLE lhgk ADD COLUMN mulai_pelaksanaan VARCHAR(255) NULL AFTER PILOT_DEPLOY');
    echo "Added mulai_pelaksanaan\n";
}

if (!in_array('selesai_pelaksanaan', $columns)) {
    DB::statement('ALTER TABLE lhgk ADD COLUMN selesai_pelaksanaan VARCHAR(255) NULL AFTER mulai_pelaksanaan');
    echo "Added selesai_pelaksanaan\n";
}

// Verifikasi
$updatedColumns = Schema::getColumnListing('lhgk');
print_r($updatedColumns);
```

### Opsi 4: Via Route `/fix-table`

Saya sudah menambahkan route helper. Akses via browser atau curl:

```bash
# Jika server Laravel sudah running
curl http://localhost:8000/fix-table

# Atau buka di browser
http://localhost:8000/fix-table
```

## ✅ Verifikasi Setelah Fix

### 1. Cek Kolom Database
Pastikan tabel `lhgk` memiliki kolom berikut:
```
- id
- NM_PERS_PANDU
- NM_BRANCH
- PENDAPATAN_PANDU
- PENDAPATAN_TUNDA
- NM_KAPAL
- JN_KAPAL
- KP_GRT
- PILOT_DEPLOY
- mulai_pelaksanaan ← HARUS ADA
- selesai_pelaksanaan ← HARUS ADA
- REALISAS_PILOT_VIA
- PERIODE
```

### 2. Test Upload CSV
1. Jalankan server: `php artisan serve`
2. Buka: http://localhost:8000
3. Upload file: `contoh_upload.csv` atau `contoh_oktober_2024.csv`
4. Harusnya muncul: "Berhasil import X data"

### 3. Cek Data di Debug
Buka: http://localhost:8000/debug-periods

Harus menampilkan:
```json
{
  "total_records": X,
  "samples": [...],
  "unique_periods": ["10-2024", ...],
  "unique_branches": ["Cabang Jakarta", ...]
}
```

## 📊 Struktur CSV yang Benar

File CSV harus memiliki header dengan kolom berikut:

```csv
NM_PERS_PANDU,NM_BRANCH,PENDAPATAN_PANDU,PENDAPATAN_TUNDA,NM_KAPAL,JN_KAPAL,KP_GRT,PILOT_DEPLOY,mulai_pelaksanaan,selesai_pelaksanaan,REALISAS_PILOT_VIA,PERIODE
Kapten Ahmad,Cabang Jakarta,2500000,500000,KM Sinar Jaya,Kapal Penumpang,5000,01-10-2024,08:00:00,12:00:00,MOBILE,10-2024
```

### Format Kolom:
- **PILOT_DEPLOY**: DD-MM-YYYY atau D-M-YYYY (akan dinormalisasi)
- **mulai_pelaksanaan**: HH:MM:SS atau HH:MM
- **selesai_pelaksanaan**: HH:MM:SS atau HH:MM
- **PERIODE**: MM-YYYY (contoh: 10-2024)
- **PENDAPATAN_PANDU/TUNDA**: Angka tanpa pemisah ribuan

## 🎯 Kesimpulan

**Penyebab utama gagal upload:**
1. ✅ View cache corrupted (sudah diperbaiki)
2. ⚠️ **Kolom `mulai_pelaksanaan` dan `selesai_pelaksanaan` belum ada di database**
3. ⚠️ Migration pending (tidak kritis, tabel sudah ada)

**Untuk menyelesaikan masalah:**
Jalankan salah satu dari 4 opsi di atas untuk menambahkan kolom yang hilang.

**File yang sudah dibuat/dimodifikasi:**
- ✅ `fix-table.php` - Script untuk fix database
- ✅ `routes/web.php` - Ditambah route `/fix-table`
- ✅ `database/migrations/2026_01_26_061913_add_time_columns_to_lhgk_table.php` - Migration baru
- ✅ Cache view sudah di-clear

## 🚀 Quick Fix Command

Pilih salah satu:

```bash
# Via script PHP
php fix-table.php

# Via tinker
php artisan tinker
# Lalu copy-paste code dari Opsi 3 di atas

# Via browser (jika server running)
# Buka: http://localhost:8000/fix-table
```

Setelah fix, coba upload CSV lagi!
