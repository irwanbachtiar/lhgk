# Deploy LHGK Dashboard to Production Server
# Server: 103.190.214.94
# Target: /var/www/html/lhgk

$SERVER = "103.190.214.94"
$USER = "binabola"
$TARGET_DIR = "/var/www/html/lhgk"
$REPO_URL = "https://github.com/irwanbachtiar/lhgk.git"

Write-Host "=== LHGK Dashboard - Production Deployment ===" -ForegroundColor Cyan
Write-Host ""

# Check if plink (PuTTY) is available
$plinkAvailable = Get-Command plink -ErrorAction SilentlyContinue
$sshAvailable = Get-Command ssh -ErrorAction SilentlyContinue

if (-not $plinkAvailable -and -not $sshAvailable) {
    Write-Host "ERROR: SSH client not found. Please install:" -ForegroundColor Red
    Write-Host "  - OpenSSH (Windows Feature), or" -ForegroundColor Yellow
    Write-Host "  - PuTTY (https://www.putty.org/)" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Or run manual commands below:" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "# Connect to server" -ForegroundColor Green
    Write-Host "ssh $USER@$SERVER" -ForegroundColor White
    Write-Host ""
    Write-Host "# Clone repository" -ForegroundColor Green
    Write-Host "cd /var/www/html" -ForegroundColor White
    Write-Host "sudo git clone $REPO_URL lhgk" -ForegroundColor White
    Write-Host "cd lhgk" -ForegroundColor White
    Write-Host ""
    Write-Host "# Install dependencies" -ForegroundColor Green
    Write-Host "composer install --no-dev --optimize-autoloader" -ForegroundColor White
    Write-Host "npm install && npm run build" -ForegroundColor White
    Write-Host ""
    Write-Host "# Setup environment" -ForegroundColor Green
    Write-Host "cp .env.example .env" -ForegroundColor White
    Write-Host "nano .env  # Edit configuration" -ForegroundColor White
    Write-Host "php artisan key:generate" -ForegroundColor White
    Write-Host ""
    Write-Host "# Set permissions" -ForegroundColor Green
    Write-Host "sudo chown -R www-data:www-data /var/www/html/lhgk" -ForegroundColor White
    Write-Host "sudo chmod -R 755 /var/www/html/lhgk" -ForegroundColor White
    Write-Host "sudo chmod -R 775 /var/www/html/lhgk/storage" -ForegroundColor White
    Write-Host "sudo chmod -R 775 /var/www/html/lhgk/bootstrap/cache" -ForegroundColor White
    Write-Host ""
    Write-Host "# Optimize Laravel" -ForegroundColor Green
    Write-Host "php artisan config:cache" -ForegroundColor White
    Write-Host "php artisan route:cache" -ForegroundColor White
    Write-Host "php artisan view:cache" -ForegroundColor White
    exit
}

Write-Host "Step 1: Creating deployment script on server..." -ForegroundColor Yellow

$deployScript = @"
#!/bin/bash
set -e

echo "=== Starting LHGK Dashboard Deployment ==="

# Navigate to web root
cd /var/www/html

# Check if directory exists
if [ -d "lhgk" ]; then
    echo "Directory exists. Pulling latest changes..."
    cd lhgk
    sudo git pull origin main
else
    echo "Cloning repository..."
    sudo git clone $REPO_URL lhgk
    cd lhgk
fi

# Install Composer dependencies
echo "Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Install NPM dependencies and build assets
echo "Building assets..."
npm install
npm run build

# Setup environment file if not exists
if [ ! -f .env ]; then
    echo "Creating .env file..."
    cp .env.example .env
    echo "⚠️  IMPORTANT: Edit .env file with production settings!"
fi

# Generate application key if not set
if ! grep -q "APP_KEY=base64:" .env; then
    echo "Generating application key..."
    php artisan key:generate
fi

# Set correct permissions
echo "Setting permissions..."
sudo chown -R www-data:www-data /var/www/html/lhgk
sudo chmod -R 755 /var/www/html/lhgk
sudo chmod -R 775 /var/www/html/lhgk/storage
sudo chmod -R 775 /var/www/html/lhgk/bootstrap/cache

# Clear and cache configurations
echo "Optimizing Laravel..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ""
echo "=== Deployment Complete! ==="
echo ""
echo "⚠️  NEXT STEPS:"
echo "1. Edit .env file: nano /var/www/html/lhgk/.env"
echo "2. Configure database settings (DB_PHINNISI_* variables)"
echo "3. Configure web server (Nginx/Apache)"
echo "4. Test application: http://103.190.214.94"
echo ""
"@

# Save script to temp file
$tempScript = [System.IO.Path]::GetTempFileName()
$deployScript | Out-File -FilePath $tempScript -Encoding UTF8

Write-Host "Step 2: Uploading deployment script..." -ForegroundColor Yellow

# Try SSH
if ($sshAvailable) {
    Write-Host "Using OpenSSH..." -ForegroundColor Gray
    scp $tempScript ${USER}@${SERVER}:/tmp/deploy-lhgk.sh
    Write-Host ""
    Write-Host "Step 3: Executing deployment on server..." -ForegroundColor Yellow
    Write-Host "Password: serverBola@2026" -ForegroundColor DarkGray
    Write-Host ""
    ssh ${USER}@${SERVER} "chmod +x /tmp/deploy-lhgk.sh && sudo /tmp/deploy-lhgk.sh"
} else {
    Write-Host "Using PuTTY..." -ForegroundColor Gray
    pscp -pw "serverBola@2026" $tempScript ${USER}@${SERVER}:/tmp/deploy-lhgk.sh
    Write-Host ""
    Write-Host "Step 3: Executing deployment on server..." -ForegroundColor Yellow
    plink -pw "serverBola@2026" ${USER}@${SERVER} "chmod +x /tmp/deploy-lhgk.sh && sudo /tmp/deploy-lhgk.sh"
}

# Cleanup
Remove-Item $tempScript -ErrorAction SilentlyContinue

Write-Host ""
Write-Host "=== Deployment script executed! ===" -ForegroundColor Green
Write-Host ""
Write-Host "Next: Configure .env file on server" -ForegroundColor Cyan
Write-Host "Run: ssh $USER@$SERVER" -ForegroundColor White
Write-Host "Then: nano /var/www/html/lhgk/.env" -ForegroundColor White
