# PANDUAN DEPLOYMENT LHGK DASHBOARD KE PRODUCTION SERVER
# Server: 103.190.214.94 | User: binabola | Target: /var/www/html/lhgk

## CARA 1: DEPLOYMENT OTOMATIS DENGAN GIT (RECOMMENDED)

### Step 1: Connect ke Server
ssh binabola@103.190.214.94
# Password: serverBola@2026

### Step 2: Install Git (jika belum ada)
sudo apt update
sudo apt install git -y

### Step 3: Clone Repository
cd /var/www/html
sudo git clone https://github.com/irwanbachtiar/lhgk.git
cd lhgk

### Step 4: Install Dependencies

# Install Composer dependencies
composer install --no-dev --optimize-autoloader

# Install NPM & Build assets
npm install
npm run build

### Step 5: Setup Environment
cp .env.example .env
nano .env

# Edit configuration (gunakan .env.production sebagai referensi):
# - APP_ENV=production
# - APP_DEBUG=false
# - APP_URL=http://103.190.214.94
# - Database settings (DB_HOST, DB_DATABASE, dll)
# - Phinnisi database settings (DB_PHINNISI_*)

# Generate application key
php artisan key:generate

### Step 6: Set Permissions
sudo chown -R www-data:www-data /var/www/html/lhgk
sudo chmod -R 755 /var/www/html/lhgk
sudo chmod -R 775 /var/www/html/lhgk/storage
sudo chmod -R 775 /var/www/html/lhgk/bootstrap/cache

### Step 7: Optimize Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

### Step 8: Configure Web Server

#### Nginx Configuration:
sudo nano /etc/nginx/sites-available/lhgk

# Paste this configuration:
server {
    listen 80;
    server_name 103.190.214.94;
    root /var/www/html/lhgk/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}

# Enable site & restart Nginx:
sudo ln -s /etc/nginx/sites-available/lhgk /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx

#### Apache Configuration:
sudo nano /etc/apache2/sites-available/lhgk.conf

# Paste this configuration:
<VirtualHost *:80>
    ServerName 103.190.214.94
    DocumentRoot /var/www/html/lhgk/public

    <Directory /var/www/html/lhgk/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/lhgk_error.log
    CustomLog ${APACHE_LOG_DIR}/lhgk_access.log combined
</VirtualHost>

# Enable site & modules:
sudo a2ensite lhgk.conf
sudo a2enmod rewrite
sudo systemctl restart apache2

### Step 9: Test Application
# Open browser: http://103.190.214.94

### Step 10: Setup Cron untuk Auto-sync (Optional)
crontab -e

# Add these lines:
# Sync Pandu every hour
0 * * * * cd /var/www/html/lhgk && php artisan phinnisi:sync-pandu >> /var/log/lhgk-sync.log 2>&1

# Sync Tunda every hour
0 * * * * cd /var/www/html/lhgk && php artisan phinnisi:sync-tunda >> /var/log/lhgk-sync.log 2>&1

---

## CARA 2: UPDATE CODE (Jika sudah deploy)

### Connect ke server:
ssh binabola@103.190.214.94

### Pull latest changes:
cd /var/www/html/lhgk
sudo git pull origin main

### Update dependencies (jika ada perubahan):
composer install --no-dev --optimize-autoloader
npm install && npm run build

### Clear & cache:
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache

---

## TROUBLESHOOTING

### Error: Permission denied
sudo chown -R www-data:www-data /var/www/html/lhgk
sudo chmod -R 775 /var/www/html/lhgk/storage
sudo chmod -R 775 /var/www/html/lhgk/bootstrap/cache

### Error: 500 Internal Server Error
# Check logs:
tail -f /var/www/html/lhgk/storage/logs/laravel.log

# Clear cache:
php artisan cache:clear
php artisan config:clear

### Error: Database connection
# Check .env file:
nano /var/www/html/lhgk/.env
# Pastikan DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD benar

### Error: CSS/JS not loading
# Rebuild assets:
npm run build
# Clear cache:
php artisan view:clear

---

## SECURITY CHECKLIST

✅ Set APP_DEBUG=false in .env
✅ Set APP_ENV=production in .env
✅ .env file NOT in Git repository
✅ storage/ and bootstrap/cache/ writable by www-data
✅ Setup firewall (ufw/iptables)
✅ Setup SSL certificate (Let's Encrypt) - RECOMMENDED
✅ Regular backups of database
✅ Monitor logs regularly
