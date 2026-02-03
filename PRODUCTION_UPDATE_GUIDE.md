# PRODUCTION UPDATE GUIDE - DEPARTURE INVOICE DELAY FEATURE
# Server: 103.190.214.94 | User: binabola | Path: /var/www/html/lhgk

## UPDATE STEPS

### 1. Connect ke Server
```bash
ssh binabola@103.190.214.94
# Password: serverBola@2026
```

### 2. Navigate ke Aplikasi
```bash
cd /var/www/html/lhgk
```

### 3. Pull Latest Changes dari GitHub
```bash
sudo git pull origin main
```

Expected output:
```
Updating 9a17ff9b..c093b980
Fast-forward
 app/Http/Controllers/DashboardController.php | 145 +++++++++++++++++++++
 resources/views/dashboard.blade.php          | 100 ++++++++++++++
 routes/web.php                               | 1 +
 optimize-departure-query.php                 | 1 file created
```

### 4. Clear Cache
```bash
php artisan cache:clear
php artisan view:clear
php artisan config:clear
php artisan route:clear
```

### 5. Optimize Laravel
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 6. Run Database Optimization Script
```bash
php optimize-departure-query.php
```

Expected output:
```
Checking and creating indexes...
Creating index: idx_departure_delay...
✓ Index created successfully!
✓ Query executed in XXXXms
✓ Found XXXXX records with departure delay > 2 days
Optimization complete!
```

### 7. Verify Permissions
```bash
sudo chown -R apache:apache /var/www/html/lhgk
sudo chmod -R 755 /var/www/html/lhgk
sudo chmod -R 775 /var/www/html/lhgk/storage
sudo chmod -R 775 /var/www/html/lhgk/bootstrap/cache
```

### 8. Restart Web Server (Optional)
```bash
# For Apache
sudo systemctl restart httpd

# For Nginx + PHP-FPM
sudo systemctl restart php-fpm
sudo systemctl restart nginx
```

### 9. Test Application
1. Open browser: http://103.190.214.94
2. Login dan pilih Cabang + Periode
3. Cek bagian "Data Keterlambatan Invoice Departure"
4. Klik "Tampilkan Data Departure"
5. Test pagination
6. Test download Excel

---

## FITUR BARU YANG DITAMBAHKAN

### 1. Departure Invoice Delay Tracking
- **Lokasi**: Dashboard LHGK (setelah tabel Statistik Kapal)
- **Trigger**: Otomatis cek jumlah data saat pilih periode & cabang
- **Filter**: GERAKAN = 'DEPARTURE' AND selisih > 2 hari
- **Display**: On-demand (klik button untuk load data)

### 2. Features
- ✅ Show/Hide toggle button
- ✅ Preview jumlah transaksi sebelum load
- ✅ Pagination 10 data per halaman
- ✅ Download Excel/CSV (semua data)
- ✅ Sorting by selisih hari (terbesar ke terkecil)

### 3. Performance
- ✅ Database index: idx_departure_delay
- ✅ Conditional loading (tidak auto-load)
- ✅ Optimized query (~1.3s untuk 182K+ records)
- ✅ Pagination untuk reduce memory usage

### 4. Data Columns
- No. UKK
- Nama Kapal
- Nama Pandu
- Cabang
- Gerakan
- Selesai Pelaksanaan
- Invoice Date
- Selisih (Hari)
- Pendapatan Pandu
- Pendapatan Tunda

---

## TROUBLESHOOTING

### Error: Index already exists
```bash
# Check existing indexes
mysql -u developer -p -h 103.190.215.115 dashboard_phinnisi
SHOW INDEX FROM lhgk WHERE Key_name = 'idx_departure_delay';

# If exists, skip optimization script
```

### Error: Permission denied
```bash
sudo chown -R apache:apache /var/www/html/lhgk
sudo chmod -R 755 /var/www/html/lhgk
```

### Error: 500 Internal Server Error
```bash
# Check logs
tail -f /var/www/html/lhgk/storage/logs/laravel.log

# Clear all cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Error: Query too slow
```bash
# Verify index exists
php optimize-departure-query.php

# Check MySQL configuration
mysql -u developer -p -h 103.190.215.115
SHOW VARIABLES LIKE 'query_cache%';
```

---

## ROLLBACK (Jika ada masalah)

### Rollback ke versi sebelumnya
```bash
cd /var/www/html/lhgk
sudo git log --oneline -5
sudo git checkout 9a17ff9b  # Previous commit
php artisan cache:clear
php artisan config:cache
```

### Drop index jika bermasalah
```bash
mysql -u developer -p -h 103.190.215.115 dashboard_phinnisi
DROP INDEX idx_departure_delay ON lhgk;
```

---

## VERIFICATION CHECKLIST

- [ ] Git pull berhasil tanpa conflict
- [ ] Database index tercipta
- [ ] Cache Laravel ter-clear dan ter-rebuild
- [ ] Permissions sudah benar (apache:apache)
- [ ] Dashboard bisa dibuka tanpa error
- [ ] Button "Tampilkan Data Departure" muncul
- [ ] Data departure bisa di-load
- [ ] Pagination berfungsi
- [ ] Download Excel berfungsi
- [ ] Performance acceptable (<3 detik)

---

## MONITORING

### Check Application Logs
```bash
tail -f /var/www/html/lhgk/storage/logs/laravel.log
```

### Check Apache/Nginx Logs
```bash
# Apache
tail -f /var/log/httpd/lhgk_error.log

# Nginx
tail -f /var/log/nginx/error.log
```

### Check Database Performance
```bash
mysql -u developer -p -h 103.190.215.115 dashboard_phinnisi
SHOW PROCESSLIST;
```

---

## SUPPORT

Jika ada masalah, kirim informasi berikut:
1. Screenshot error
2. Laravel log: `storage/logs/laravel.log`
3. Browser console errors (F12)
4. Server details: `php -v`, `mysql --version`
