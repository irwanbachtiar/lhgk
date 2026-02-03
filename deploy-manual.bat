@echo off
echo ========================================
echo DEPLOYMENT LHGK DASHBOARD
echo Server: 103.190.214.94
echo ========================================
echo.
echo Langkah 1: Connect ke server
echo.
echo Copy dan paste command berikut:
echo.
echo ssh binabola@103.190.214.94
echo.
echo Password: serverBola@2026
echo.
echo ========================================
echo.
echo Setelah login, jalankan command berikut SATU PER SATU:
echo.
echo --- STEP 1: Clone Repository ---
echo cd /var/www/html
echo sudo git clone https://github.com/irwanbachtiar/lhgk.git
echo cd lhgk
echo.
echo --- STEP 2: Install Composer Dependencies ---
echo composer install --no-dev --optimize-autoloader
echo.
echo --- STEP 3: Build Assets ---
echo npm install
echo npm run build
echo.
echo --- STEP 4: Setup Environment ---
echo cp .env.example .env
echo nano .env
echo.
echo   Edit file .env:
echo   - APP_ENV=production
echo   - APP_DEBUG=false
echo   - APP_URL=http://103.190.214.94
echo   - DB_HOST=103.190.215.115
echo   - DB_DATABASE=staging_data
echo   - DB_USERNAME=developer
echo   - DB_PASSWORD=developer215
echo   - DB_PHINNISI_HOST=103.190.215.115
echo   - DB_PHINNISI_DATABASE=dashboard_phinnisi
echo   - DB_PHINNISI_USERNAME=developer
echo   - DB_PHINNISI_PASSWORD=developer215
echo.
echo   Tekan Ctrl+O untuk save, Enter, lalu Ctrl+X untuk exit
echo.
echo php artisan key:generate
echo.
echo --- STEP 5: Set Permissions ---
echo sudo chown -R apache:apache /var/www/html/lhgk
echo sudo chmod -R 755 /var/www/html/lhgk
echo sudo chmod -R 775 /var/www/html/lhgk/storage
echo sudo chmod -R 775 /var/www/html/lhgk/bootstrap/cache
echo.
echo --- STEP 6: Optimize Laravel ---
echo php artisan config:cache
echo php artisan route:cache
echo php artisan view:cache
echo.
echo --- STEP 7: Configure Apache ---
echo sudo nano /etc/httpd/conf.d/lhgk.conf
echo.
echo   Paste configuration berikut:
echo.
echo ^<VirtualHost *:80^>
echo     ServerName 103.190.214.94
echo     DocumentRoot /var/www/html/lhgk/public
echo.
echo     ^<Directory /var/www/html/lhgk/public^>
echo         Options Indexes FollowSymLinks
echo         AllowOverride All
echo         Require all granted
echo     ^</Directory^>
echo.
echo     ErrorLog /var/log/httpd/lhgk_error.log
echo     CustomLog /var/log/httpd/lhgk_access.log combined
echo ^</VirtualHost^>
echo.
echo   Save dengan Ctrl+O, Enter, Ctrl+X
echo.
echo sudo systemctl restart httpd
echo.
echo --- STEP 8: Test ---
echo echo "Installation Complete!"
echo echo "Open: http://103.190.214.94"
echo.
echo ========================================
echo.
pause
