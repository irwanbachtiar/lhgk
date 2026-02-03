#!/usr/bin/env bash
set -euo pipefail

# deploy_production.sh
# Interactive deployment helper for production server.
# Usage: sudo bash scripts/deploy_production.sh

# CONFIG
APP_DIR="/var/www/html/lhgk"
BACKUP_DIR="/root/lhgk-backups"
GIT_REMOTE="origin"
GIT_BRANCH="main"
COMPOSER_BIN="/usr/local/bin/composer"
NGINX_SERVICE="nginx"
PHP_FPM_SERVICE="php-fpm"
NGINX_CONF="/etc/nginx/conf.d/lhgk.conf"
FIREWALL_CMD="firewall-cmd"
PORT=8990

# Safety prompt
echo "*** PRODUCTION DEPLOY SCRIPT ***"
read -p "This will deploy code to ${APP_DIR}. Continue? (yes/no) " CONFIRM
if [[ "$CONFIRM" != "yes" ]]; then
  echo "Aborted by user."; exit 1
fi

# 1) ensure app dir exists
if [[ ! -d "$APP_DIR" ]]; then
  echo "Error: ${APP_DIR} not found."; exit 1
fi

TIMESTAMP=$(date +%F-%H%M)
mkdir -p "$BACKUP_DIR"

# 2) backup current app directory
echo "Backing up ${APP_DIR} to ${BACKUP_DIR}/lhgk-${TIMESTAMP}.tar.gz ..."
sudo tar czf "${BACKUP_DIR}/lhgk-${TIMESTAMP}.tar.gz" -C "$(dirname "$APP_DIR")" "$(basename "$APP_DIR")"

# 3) database dump (skip if user wants to skip)
read -p "Do you want to dump the database? (recommended) (yes/no) " DO_DB_DUMP
if [[ "$DO_DB_DUMP" == "yes" ]]; then
  read -p "DB user [root]: " DB_USER
  DB_USER=${DB_USER:-root}
  read -s -p "DB password (leave empty to skip): " DB_PASS
  echo
  read -p "DB name to dump (e.g. dashboard_phinnisi): " DB_NAME
  if [[ -n "$DB_NAME" ]]; then
    echo "Dumping DB ${DB_NAME} to ${BACKUP_DIR}/${DB_NAME}-${TIMESTAMP}.sql ..."
    if [[ -n "$DB_PASS" ]]; then
      mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > "${BACKUP_DIR}/${DB_NAME}-${TIMESTAMP}.sql"
    else
      mysqldump -u "$DB_USER" "$DB_NAME" > "${BACKUP_DIR}/${DB_NAME}-${TIMESTAMP}.sql"
    fi
  fi
fi

# 4) git pull as nginx user if possible
echo "Pulling latest code from Git (${GIT_REMOTE}/${GIT_BRANCH})..."
cd "$APP_DIR"
# fetch and reset to remote branch
sudo -u nginx -H git fetch "$GIT_REMOTE" "$GIT_BRANCH"
sudo -u nginx -H git reset --hard "$GIT_REMOTE/$GIT_BRANCH"

# 5) vendor install
if [[ ! -x "$COMPOSER_BIN" ]]; then
  echo "Composer not found at ${COMPOSER_BIN}. Attempting /usr/bin/composer..."
  if [[ -x "/usr/bin/composer" ]]; then
    COMPOSER_BIN="/usr/bin/composer"
  else
    echo "Composer binary not found. Please install composer and retry."; exit 1
  fi
fi

echo "Installing PHP dependencies (composer)..."
# ensure vendor dir writable
sudo rm -rf vendor
sudo -u nginx -H mkdir -p vendor
COMPOSER_MEMORY_LIMIT=-1 sudo -u nginx -H "$COMPOSER_BIN" install --no-dev --optimize-autoloader --no-interaction

# 6) npm build (optional)
read -p "Build frontend assets? (npm) (yes/no) " DO_NPM
if [[ "$DO_NPM" == "yes" ]]; then
  if [[ -f package.json ]]; then
    sudo -u nginx -H npm ci
    sudo -u nginx -H npm run build
  else
    echo "No package.json found, skipping npm build."
  fi
fi

# 7) permissions
echo "Fixing permissions..."
sudo chown -R nginx:nginx "$APP_DIR"
sudo find "$APP_DIR" -type d -exec chmod 755 {} \;
sudo find "$APP_DIR" -type f -exec chmod 644 {} \;
sudo chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" || true
sudo mkdir -p "$APP_DIR/storage/logs" "$APP_DIR/storage/framework/views"
sudo chown -R nginx:nginx "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"

# 8) env & APP_KEY
# If APP_KEY missing, generate
if ! sudo -u nginx -H bash -c "php artisan key:generate --force"; then
  echo "Warning: key:generate failed. Ensure .env is writable and PHP CLI available.";
fi

# 9) migrations (optional)
read -p "Run database migrations? (dangerous) (yes/no) " DO_MIGRATE
if [[ "$DO_MIGRATE" == "yes" ]]; then
  php artisan migrate --force
fi

# 10) run optimize index script (if exists)
if [[ -f "$APP_DIR/optimize-departure-query.php" ]]; then
  echo "Running optimize-departure-query.php (creating DB index)..."
  php "$APP_DIR/optimize-departure-query.php" || echo "Index script failed or requires DB access."
fi

# 11) clear & cache
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan cache:clear || true
php artisan config:cache || true
php artisan route:cache || true

# 12) nginx config: ensure root points to public
if [[ -f "$NGINX_CONF" ]]; then
  if ! grep -q "root .*\/public;" "$NGINX_CONF"; then
    echo "Updating nginx config root to public in ${NGINX_CONF}"
    sudo sed -i "s|root .*;|root ${APP_DIR}/public;|g" "$NGINX_CONF" || true
  fi
fi

# 13) firewall
if command -v "$FIREWALL_CMD" >/dev/null 2>&1; then
  echo "Opening port ${PORT} via firewall-cmd"
  sudo $FIREWALL_CMD --permanent --add-port=${PORT}/tcp || true
  sudo $FIREWALL_CMD --reload || true
fi

# 14) restart services
echo "Restarting php-fpm and nginx"
sudo systemctl restart "$PHP_FPM_SERVICE" || true
sudo systemctl restart "$NGINX_SERVICE" || true

# 15) final checks
echo "Deployment finished. Quick checks:"
sudo systemctl status "$NGINX_SERVICE" --no-pager || true
sudo systemctl status "$PHP_FPM_SERVICE" --no-pager || true
sudo ss -tlnp | grep -E ":${PORT} |nginx" || true

echo "Open: http://<server-ip>:${PORT} or your domain to verify." 

echo "Backups are in ${BACKUP_DIR}." 

exit 0
