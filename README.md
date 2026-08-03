# Castaneas Station

Application web métier pour la gestion d'une station fruitière Castaneas, pensée pour un déploiement simple sur hébergement mutualisé.

## Stack retenue

- Laravel 13
- PHP 8.3
- MySQL ou MariaDB
- Blade + Tailwind CSS
- Breeze pour l'authentification interne
- Spatie Permission pour les rôles
- Spatie Activitylog pour l'audit
- DomPDF pour les étiquettes PDF

## Décisions produit déjà intégrées

- authentification interne simple
- aucune inscription publique
- rôles `superadmin` et `operateur`
- stock géré à `0` exact
- non conformes visibles dans le module Stock, sous-onglet séparé
- commandes modifiables uniquement sur leur numéro de commande
- filtres à prévoir dans tous les modules de listes

## Ce que contient déjà l'ossature

- application Laravel initialisée et build frontend prêt
- écran d'accueil, login interne et dashboard Castaneas
- navigation métier par modules
- middleware dédié pour protéger le backoffice superadmin
- migrations des packages rôles/audit
- schéma métier initial pour fruits, variétés, fournisseurs, tares, calibres, réceptions, calibrages, palox et commandes
- seeders pour créer les rôles et un compte superadmin initial
- configuration `.env.example` orientée MySQL et hébergement mutualisé

## Installation locale

1. Copier `.env.example` vers `.env`
2. Renseigner l'accès MySQL
3. Installer les dépendances PHP et JS si nécessaire
4. Lancer les migrations et seeders
5. Démarrer l'application

Commandes utiles :

```bash
php composer.phar install
npm install
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan serve
```

## Compte superadmin initial

Le compte initial est créé via les variables d'environnement suivantes :

- `CASTANEAS_SUPERADMIN_NAME`
- `CASTANEAS_SUPERADMIN_EMAIL`
- `CASTANEAS_SUPERADMIN_PASSWORD`

Pense à modifier ces valeurs avant un déploiement réel.

## Déploiement mutualisé

Pré-requis côté hébergeur :

- PHP 8.3 minimum
- MySQL ou MariaDB
- possibilité d'exécuter Composer, ou build local puis upload complet du projet
- possibilité de faire pointer le domaine ou sous-domaine vers le dossier `public`

Déploiement recommandé :

1. Construire les assets avec `npm run build` avant l'envoi si le serveur n'a pas Node.
2. Envoyer le projet sur le serveur.
3. Renseigner le fichier `.env` avec les accès base de données et URL.
4. Exécuter `php artisan key:generate` puis `php artisan migrate --seed`.
5. Vérifier que `storage` et `bootstrap/cache` sont accessibles en écriture.

Si le domaine ne peut pas pointer vers `public`, il faudra prévoir une adaptation avec redirection serveur ou réorganisation du point d'entrée selon les contraintes de l'hébergeur.

## Déploiement Hostinger avec GitHub

Configuration validée pour l'hébergement cible actuel :

- Hostinger mutualisé
- PHP web 8.3.x
- SSH actif
- Composer disponible
- Node.js absent côté serveur

Conséquence importante : les assets Vite doivent être construits en local avant le push GitHub.

### Préparation locale avant push

1. Construire les assets :

```bash
npm run build
```

2. Vérifier que le dossier `public/build` est bien inclus dans le commit.

3. Pousser le code sur GitHub.

### Déploiement côté serveur

Se connecter en SSH, puis travailler avec le binaire PHP 8.3 explicite :

```bash
/opt/alt/php83/usr/bin/php -v
```

Installation du projet :

```bash
git clone https://github.com/hugocadenedev/station-castaneas.git
cd station-castaneas
/opt/alt/php83/usr/bin/php /usr/bin/composer install --no-dev --optimize-autoloader
cp .env.example .env
```

Variables minimales recommandées dans `.env` :

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://dimgrey-moose-518972.hostingersite.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=u362859991_stationcasta
DB_USERNAME=u362859991_station
DB_PASSWORD=...

SESSION_DRIVER=file
CACHE_STORE=database
QUEUE_CONNECTION=database
```

Finalisation Laravel :

```bash
/opt/alt/php83/usr/bin/php artisan key:generate
/opt/alt/php83/usr/bin/php artisan migrate --seed --force
/opt/alt/php83/usr/bin/php artisan config:cache
/opt/alt/php83/usr/bin/php artisan route:cache
/opt/alt/php83/usr/bin/php artisan view:cache
chmod -R 775 storage bootstrap/cache
```

### Notes Hostinger

- Ne pas utiliser `php` seul en SSH si la CLI pointe sur PHP 8.2.
- Utiliser systématiquement `/opt/alt/php83/usr/bin/php` pour Composer et Artisan.
- Comme Node n'est pas disponible sur le serveur, ne pas tenter `npm run build` en production.
- Si le site ne peut pas pointer directement vers le dossier `public`, il faudra adapter l'entrée web à `public_html`.

## Structure métier visée

- Réception
- Calibrage
- Stock
- Commandes
- Backoffice Superadmin

Le cahier des charges source reste disponible dans `cahier_des_charges_castaneas_v7.md`.
