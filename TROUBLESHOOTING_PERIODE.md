# Troubleshooting: Periode Tidak Muncul di Filter

## Masalah
Setelah upload CSV untuk periode Oktober, filter periode tidak menampilkan periode Oktober.

## Penyebab
1. **Format tanggal tidak konsisten** - Tanggal ditulis dengan format berbeda (1-10-2024 vs 01-10-2024)
2. **Spasi ekstra** - Ada spasi sebelum/sesudah tanggal
3. **Format tanggal salah** - Menggunakan format selain DD-MM-YYYY

## Solusi yang Sudah Diterapkan

### 1. Normalisasi Format Tanggal
Controller sekarang otomatis menormalisasi format tanggal ke `DD-MM-YYYY`:

```php
// Mendukung berbagai format input:
- 1-10-2024 → 01-10-2024
- 01/10/2024 → 01-10-2024
- 2024-10-01 → 01-10-2024
- 01-Oct-2024 → 01-10-2024
```

### 2. Filter Query Lebih Ketat
Query untuk mengambil periode sekarang lebih ketat:
- Memastikan panjang PILOT_DEPLOY minimal 10 karakter
- Memvalidasi format DD-MM-YYYY dengan cek karakter '-' di posisi yang benar
- Sorting berdasarkan tanggal, bukan string

### 3. Debug Tools
Tambahan tools untuk troubleshooting:
- `/debug-periods` - Cek semua data periode yang tersimpan
- Button "Clear Data" - Hapus semua data untuk testing ulang

## Cara Testing

### 1. Clear Data Existing (Opsional)
Jika ingin mulai dari awal:
```
Klik tombol "Clear Data" di dashboard
```

### 2. Upload File CSV Oktober
Gunakan file: `contoh_oktober_2024.csv`

Format CSV:
```csv
NM_PERS_PANDU,PENDAPATAN_PANDU,PENDAPATAN_TUNDA,NM_KAPAL,JN_KAPAL,KP_GRT,PILOT_DEPLOY,REALISAS_PILOT_VIA,PERIODE
Kapten Ahmad,2500000,500000,KM Sinar Jaya,Kapal Penumpang,5000,01-10-2024,MOBILE,10-2024
```

**Penting:** 
- Kolom `PILOT_DEPLOY` harus format `DD-MM-YYYY` atau `D-M-YYYY`
- Sistem akan auto-normalisasi ke `DD-MM-YYYY`

### 3. Cek di Debug
Buka: `http://localhost:8000/debug-periods`

Output akan menampilkan:
```json
{
  "total_records": 5,
  "samples": [...],
  "unique_periods": ["10-2024", "11-2024", "12-2024"]
}
```

### 4. Refresh Dashboard
Refresh halaman dashboard dan cek dropdown "Filter Periode"

## Format Tanggal yang Didukung

### Input CSV (akan dinormalisasi):
✅ `01-10-2024` (DD-MM-YYYY) - Recommended
✅ `1-10-2024` (D-M-YYYY)
✅ `01/10/2024` (DD/MM/YYYY)
✅ `1/10/2024` (D/M/YYYY)
✅ `2024-10-01` (YYYY-MM-DD)
✅ `01-Oct-2024` (DD-Mon-YYYY)

### Output Database (setelah normalisasi):
✅ `01-10-2024` (DD-MM-YYYY dengan leading zero)

## Contoh CSV yang Benar

### contoh_oktober_2024.csv
```csv
NM_PERS_PANDU,PENDAPATAN_PANDU,PENDAPATAN_TUNDA,NM_KAPAL,JN_KAPAL,KP_GRT,PILOT_DEPLOY,REALISAS_PILOT_VIA,PERIODE
Kapten Ahmad,2500000,500000,KM Sinar Jaya,Kapal Penumpang,5000,1-10-2024,MOBILE,10-2024
Kapten Budi,3000000,750000,MV Ocean Star,Kapal Kargo,8000,5-10-2024,WEB,10-2024
Kapten Chandra,2750000,600000,KM Nusantara,Kapal Tanker,6500,10-10-2024,MOBILE,10-2024
```

**Note:** Tanggal bisa pakai format `1-10-2024` atau `01-10-2024`, sistem akan normalisasi.

## Verifikasi Hasil

### 1. Cek Filter Periode
Di dashboard, dropdown "Filter Periode" harus menampilkan:
- Semua Periode
- 10-2024 (Oktober 2024)
- ... periode lainnya

### 2. Pilih Periode Oktober
Pilih "10-2024" dari dropdown, data harus tampil.

### 3. Cek Total
Pastikan total transaksi dan pendapatan sesuai dengan data yang diupload.

## Jika Masih Tidak Muncul

### 1. Cek Database Langsung
```sql
SELECT 
    PILOT_DEPLOY,
    SUBSTRING(PILOT_DEPLOY, 4, 7) as periode,
    COUNT(*) as jumlah
FROM lhgk 
WHERE PILOT_DEPLOY IS NOT NULL
GROUP BY periode;
```

### 2. Cek Format Data
```sql
SELECT 
    PILOT_DEPLOY,
    LENGTH(PILOT_DEPLOY) as panjang,
    SUBSTRING(PILOT_DEPLOY, 3, 1) as char3,
    SUBSTRING(PILOT_DEPLOY, 6, 1) as char6
FROM lhgk 
LIMIT 10;
```

Hasil yang benar:
- `panjang` = 10
- `char3` = '-'
- `char6` = '-'

### 3. Clear dan Upload Ulang
```bash
# Via browser
http://localhost:8000/clear-data

# Atau via Artisan
php artisan tinker
>>> \App\Models\Lhgk::truncate();
```

Kemudian upload ulang CSV.

## Testing Step by Step

```bash
# 1. Jalankan server
php artisan serve

# 2. Buka browser
http://localhost:8000

# 3. Clear data existing
Klik "Clear Data"

# 4. Upload file Oktober
Upload: contoh_oktober_2024.csv

# 5. Cek debug
http://localhost:8000/debug-periods

# 6. Refresh dashboard
F5 atau refresh browser

# 7. Cek dropdown periode
Harus muncul "10-2024"
```

## File Testing yang Tersedia

1. **contoh_upload.csv** - Data Oktober dengan format standar
2. **contoh_oktober_2024.csv** - Data Oktober dengan format tanggal pendek (1-10-2024)

Gunakan salah satu file untuk testing.

## Support

Jika masih ada masalah:
1. Cek log: `storage/logs/laravel.log`
2. Cek output debug: `/debug-periods`
3. Cek database langsung dengan SQL di atas
4. Pastikan tidak ada spasi atau karakter hidden di file CSV
