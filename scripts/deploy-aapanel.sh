#!/usr/bin/env bash
# Run on server as www (or root) after GitHub clone is available.
set -euo pipefail
SITE_ROOT="${SITE_ROOT:-/www/wwwroot/oydinnazorat.uz}"
REPO_URL="${REPO_URL:?Set REPO_URL=https://github.com/USER/REPO.git}"
BRANCH="${BRANCH:-main}"
PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"

TS=$(date +%Y%m%d_%H%M%S)
BACKUP="/www/backup/oydinnazorat_${TS}"
mkdir -p /www/backup
echo "==> Backup old site to ${BACKUP}"
mkdir -p "$BACKUP"
if [ -d "$SITE_ROOT" ]; then
  rsync -a --exclude 'storage/logs' "$SITE_ROOT/" "$BACKUP/" || cp -a "$SITE_ROOT" "$BACKUP/old"
fi

TMP="/tmp/oydin_deploy_${TS}"
rm -rf "$TMP"
echo "==> Clone ${REPO_URL} (${BRANCH})"
git clone --depth 1 -b "$BRANCH" "$REPO_URL" "$TMP"

echo "==> Replace site root (keep nothing from old Node/SQLite app)"
mkdir -p "$SITE_ROOT"
find "$SITE_ROOT" -mindepth 1 -maxdepth 1 -exec rm -rf {} +
rsync -a "$TMP/" "$SITE_ROOT/"
cd "$SITE_ROOT"

if [ ! -f .env ]; then
  echo "ERROR: copy .env into ${SITE_ROOT}/.env first (MySQL + Telegram + Billz)"
  exit 1
fi

echo "==> Composer install"
$COMPOSER_BIN install --no-dev --optimize-autoloader --no-interaction

echo "==> Permissions"
chown -R www:www "$SITE_ROOT" 2>/dev/null || true
chmod -R ug+rwx storage bootstrap/cache

echo "==> APP_KEY"
grep -q '^APP_KEY=base64:' .env || $PHP_BIN artisan key:generate --force

echo "==> Wipe DB and seed (SQLite era data discarded)"
$PHP_BIN artisan migrate:fresh --force --seed

echo "==> Optimize"
$PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache
$PHP_BIN artisan storage:link || true

echo "==> Telegram webhook"
$PHP_BIN artisan telegram:set-webhook 2>/dev/null || $PHP_BIN artisan list | grep -i telegram || true

echo "DONE. Document root must be ${SITE_ROOT}/public"
echo "Panel login after seed: admin / admin123 — change immediately."
