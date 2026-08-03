# Deploiement Hostinger

Memo pour les prochains deploiements de Castaneas Station sur Hostinger mutualise.

## Contexte valide

- Hebergement: Hostinger mutualise
- Domaine temporaire: `https://dimgrey-moose-518972.hostingersite.com`
- Projet deploye dans: `/home/u362859991/domains/dimgrey-moose-518972.hostingersite.com/public_html/station-castaneas`
- Racine web publique: `/home/u362859991/domains/dimgrey-moose-518972.hostingersite.com/public_html`
- PHP CLI a utiliser: `/opt/alt/php83/usr/bin/php`
- Composer: `/usr/local/bin/composer`
- Node.js indisponible sur le serveur

## Important

- Toujours builder les assets en local avant push.
- Le dossier `public/build` doit etre versionne dans Git.
- Ne jamais utiliser `php` seul en SSH si la CLI par defaut pointe sur PHP 8.2.
- Toujours utiliser `/opt/alt/php83/usr/bin/php` pour `artisan` et `composer`.

## Workflow de deploiement

### 1. En local

Depuis le projet local:

```bash
npm run build
git add .
git commit -m "Votre message"
git push
```

### 2. Sur Hostinger

Dans `Avance > GIT`, redeployer le repo GitHub sur le sous-dossier:

```text
public_html/station-castaneas
```

### 3. Mise a jour serveur en SSH

Connexion:

```bash
ssh -p 65002 u362859991@178.16.128.170
```

Puis:

```bash
cd /home/u362859991/domains/dimgrey-moose-518972.hostingersite.com/public_html/station-castaneas
git pull
/opt/alt/php83/usr/bin/php /usr/local/bin/composer install --no-dev --optimize-autoloader
```

### 4. Recopier le dossier public Laravel dans la racine web

```bash
cd /home/u362859991/domains/dimgrey-moose-518972.hostingersite.com/public_html
cp -r station-castaneas/public/* .
cp station-castaneas/public/.htaccess .
```

### 5. Verifier le fichier index.php public

Le fichier `/home/u362859991/domains/dimgrey-moose-518972.hostingersite.com/public_html/index.php` doit contenir:

```php
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
```

### 6. .htaccess minimal compatible

Le fichier `/home/u362859991/domains/dimgrey-moose-518972.hostingersite.com/public_html/.htaccess` peut rester dans cette version minimale stable:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On

    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

### 7. Caches Laravel

```bash
cd /home/u362859991/domains/dimgrey-moose-518972.hostingersite.com/public_html/station-castaneas
/opt/alt/php83/usr/bin/php artisan config:cache
/opt/alt/php83/usr/bin/php artisan route:cache
/opt/alt/php83/usr/bin/php artisan view:cache
chmod -R 775 storage bootstrap/cache
```

## Premier deploiement uniquement

Si l'environnement ou la base ont ete recrees:

```bash
cd /home/u362859991/domains/dimgrey-moose-518972.hostingersite.com/public_html/station-castaneas
cp .env.example .env
```

Remplir ensuite `.env` avec les vraies valeurs de prod, puis lancer:

```bash
/opt/alt/php83/usr/bin/php artisan key:generate
/opt/alt/php83/usr/bin/php artisan migrate --seed --force
```

## Valeurs .env importantes

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://dimgrey-moose-518972.hostingersite.com`
- `DB_HOST=127.0.0.1`
- `DB_PORT=3306`
- `DB_DATABASE=u362859991_stationcasta`
- `DB_USERNAME=u362859991_station`
- `SESSION_DRIVER=file`
- `QUEUE_CONNECTION=database`
- `CACHE_STORE=database`

## Checks rapides apres deploiement

```bash
ls -la /home/u362859991/domains/dimgrey-moose-518972.hostingersite.com/public_html/build
cat /home/u362859991/domains/dimgrey-moose-518972.hostingersite.com/public_html/index.php
```

Puis tester:

```text
https://dimgrey-moose-518972.hostingersite.com
```

## Pannes deja rencontrees

### HTTP 500 + `Vite manifest not found`

Cause:

- `public/build` non versionne dans Git

Correction:

- builder localement avec `npm run build`
- committer `public/build`
- redeployer le repo

### HTTP 500 sans log Laravel

Cause probable:

- `public_html/index.php` pointait encore vers `../vendor` et `../bootstrap`

Correction:

- remplacer les chemins par `station-castaneas/vendor/...` et `station-castaneas/bootstrap/...`

### HTTP 500 a cause du .htaccess

Cause probable:

- directives Apache Laravel trop strictes pour Hostinger mutualise

Correction:

- utiliser le `.htaccess` minimal ci-dessus