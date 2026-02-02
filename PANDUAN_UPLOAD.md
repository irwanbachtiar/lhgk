# Panduan Upload CSV - Dashboard LHGK

## Persiapan

### 1. Jalankan Migration Database
Jalankan perintah berikut untuk membuat tabel database:

```bash
php artisan migrate
```

### 2. Jalankan Laravel Development Server
```bash
php artisan serve
```

Buka browser dan akses: `http://localhost:8000`

## Format File CSV

File CSV harus memiliki header dengan nama kolom yang sesuai. Berikut format yang diperlukan:

### Kolom-kolom yang Tersedia:
- `NM_PERS_PANDU` - Nama Pandu
- `PENDAPATAN_PANDU` - Pendapatan Pandu (angka)
- `PENDAPATAN_TUNDA` - Pendapatan Tunda (angka)
- `NM_KAPAL` - Nama Kapal
- `JN_KAPAL` - Jenis Kapal
- `KP_GRT` - GRT (Gross Registered Tonnage)
- `PILOT_DEPLOY` - Tanggal Deploy (format: DD-MM-YYYY)
- `REALISAS_PILOT_VIA` - Via Realisasi (MOBILE/WEB/PARTIAL)
- `PERIODE` - Periode (format: MM-YYYY)

### Contoh Format CSV:
```csv
NM_PERS_PANDU,PENDAPATAN_PANDU,PENDAPATAN_TUNDA,NM_KAPAL,JN_KAPAL,KP_GRT,PILOT_DEPLOY,REALISAS_PILOT_VIA,PERIODE
Kapten Ahmad,2500000,500000,KM Sinar Jaya,Kapal Penumpang,5000,01-12-2024,MOBILE,12-2024
Kapten Budi,3000000,750000,MV Ocean Star,Kapal Kargo,8000,05-12-2024,WEB,12-2024
```

## Cara Upload

1. **Buka Dashboard**
   - Akses `http://localhost:8000`

2. **Pilih File CSV**
   - Scroll ke bagian "Upload File CSV"
   - Klik tombol "Choose File" atau "Browse"
   - Pilih file CSV yang sudah disiapkan
   - File maksimal 10MB

3. **Upload**
   - Klik tombol "Upload"
   - Tunggu proses upload selesai

4. **Hasil Upload**
   - Jika berhasil: Akan muncul pesan hijau "Berhasil import X data"
   - Jika ada error: Akan muncul pesan merah dengan detail error
   - Jika ada warning: Akan muncul pesan kuning dengan detail baris yang bermasalah

## Fitur Upload yang Sudah Diperbaiki

✅ **Encoding Support** - Otomatis detect dan convert encoding (UTF-8, ISO-8859-1, dll)
✅ **Validation** - Validasi format file dan ukuran
✅ **Transaction Support** - Jika ada error besar, semua data akan di-rollback
✅ **Error Handling** - Menampilkan error detail per baris
✅ **Empty Row Handling** - Otomatis skip baris kosong
✅ **Trim Whitespace** - Otomatis bersihkan spasi di data
✅ **Multiple MIME Types** - Support berbagai format CSV

## File Contoh

File contoh sudah disediakan di: `contoh_upload.csv`

Anda bisa menggunakan file ini untuk testing upload pertama kali.

## Troubleshooting

### Upload Gagal - "File harus berupa CSV"
- Pastikan file ekstensi `.csv`
- Coba save ulang dari Excel dengan format "CSV (Comma delimited)"

### Upload Gagal - "File CSV kosong"
- Pastikan ada header di baris pertama
- Pastikan ada minimal 1 baris data

### Error pada Baris Tertentu
- Cek format data di baris tersebut
- Pastikan angka tidak mengandung huruf
- Pastikan tanggal dalam format DD-MM-YYYY

### Data Tidak Muncul di Dashboard
- Refresh halaman browser
- Cek filter periode di dropdown
- Pastikan data PILOT_DEPLOY terisi dengan benar

## Tips

1. **Gunakan Excel/Google Sheets**
   - Buat data di Excel atau Google Sheets
   - Save As → CSV (Comma delimited)

2. **Backup Data**
   - Selalu backup data sebelum upload besar
   - Bisa export database: `php artisan db:export`

3. **Upload Bertahap**
   - Untuk data banyak (>1000 baris), upload bertahap
   - Split file menjadi beberapa bagian

4. **Cek Data**
   - Setelah upload, cek dashboard apakah data sudah muncul
   - Gunakan filter periode untuk memfilter data

## Database

Jika perlu reset database:

```bash
# Drop semua tabel dan buat ulang
php artisan migrate:fresh

# Atau drop hanya tabel lhgk
php artisan migrate:rollback --step=1
php artisan migrate
```

## Support

Jika masih ada masalah, cek:
- Log Laravel: `storage/logs/laravel.log`
- Browser Console (F12) untuk error JavaScript
- PHP Error Log
