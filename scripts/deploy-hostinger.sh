#!/bin/sh

set -eu

DOMAIN_ROOT="/home/u362859991/domains/dimgrey-moose-518972.hostingersite.com"
PUBLIC_ROOT="$DOMAIN_ROOT/public_html"
PROJECT_ROOT="$PUBLIC_ROOT/station-castaneas"
PHP_BIN="/opt/alt/php83/usr/bin/php"
COMPOSER_BIN="/usr/local/bin/composer"

echo "==> Project root: $PROJECT_ROOT"

cd "$PROJECT_ROOT"

echo "==> Updating git checkout"
git pull

echo "==> Clearing stale Laravel bootstrap caches"
rm -f bootstrap/cache/packages.php bootstrap/cache/services.php

echo "==> Installing PHP dependencies"
"$PHP_BIN" "$COMPOSER_BIN" install --no-dev --optimize-autoloader --no-scripts

echo "==> Rebuilding Laravel package discovery"
rm -f bootstrap/cache/packages.php bootstrap/cache/services.php
"$PHP_BIN" artisan package:discover --ansi

echo "==> Publishing Laravel public files"
cd "$PUBLIC_ROOT"
cp -r station-castaneas/public/* .
cp station-castaneas/public/.htaccess .

echo "==> Restoring Hostinger public index.php"
cat > "$PUBLIC_ROOT/index.php" <<'PHP'
<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/station-castaneas/storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/station-castaneas/vendor/autoload.php';

/** @var Application $app */
$app = require_once __DIR__.'/station-castaneas/bootstrap/app.php';

$app->handleRequest(Request::capture());
PHP

echo "==> Rebuilding Laravel caches"
cd "$PROJECT_ROOT"
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan route:cache
"$PHP_BIN" artisan view:cache

echo "==> Fixing writable directories"
chmod -R 775 storage bootstrap/cache

echo "==> Deployment completed"
echo "Test URL: https://dimgrey-moose-518972.hostingersite.com"