#!/bin/bash
# =============================================================
# ArtisanHub — Script de déploiement VPS Ubuntu 22.04
# Usage : bash DEPLOY.sh
# =============================================================

set -e  # Arrêter si une commande échoue
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

log()  { echo -e "${GREEN}[✓]${NC} $1"; }
warn() { echo -e "${YELLOW}[!]${NC} $1"; }
err()  { echo -e "${RED}[✗]${NC} $1"; exit 1; }

echo ""
echo "🏺 ArtisanHub — Déploiement VPS"
echo "================================"
echo ""

# ── 1. MISE À JOUR SYSTÈME ────────────────────────────────────────────────────
log "Mise à jour des paquets système..."
apt-get update -qq && apt-get upgrade -y -qq

# ── 2. PHP 8.3 ───────────────────────────────────────────────────────────────
log "Installation de PHP 8.3..."
apt-get install -y -qq software-properties-common
add-apt-repository -y ppa:ondrej/php
apt-get update -qq
apt-get install -y -qq \
    php8.3 php8.3-fpm php8.3-mysql php8.3-xml php8.3-curl \
    php8.3-mbstring php8.3-zip php8.3-gd php8.3-intl \
    php8.3-bcmath php8.3-redis

php -v
log "PHP 8.3 installé"

# ── 3. NGINX ──────────────────────────────────────────────────────────────────
log "Installation de Nginx..."
apt-get install -y -qq nginx
systemctl enable nginx
log "Nginx installé"

# ── 4. MYSQL 8 ────────────────────────────────────────────────────────────────
log "Installation de MySQL 8..."
apt-get install -y -qq mysql-server
systemctl enable mysql

# Créer la base de données
DB_PASS=$(openssl rand -base64 24)
mysql -e "CREATE DATABASE IF NOT EXISTS artisanhub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS 'artisanhub_user'@'localhost' IDENTIFIED BY '$DB_PASS';"
mysql -e "GRANT ALL PRIVILEGES ON artisanhub.* TO 'artisanhub_user'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

echo "DB_PASSWORD=$DB_PASS" >> /root/artisanhub_credentials.txt
warn "Mot de passe MySQL sauvegardé dans /root/artisanhub_credentials.txt"
log "MySQL configuré"

# ── 5. COMPOSER ───────────────────────────────────────────────────────────────
log "Installation de Composer..."
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
composer --version
log "Composer installé"

# ── 6. NODE.JS (pour assets) ──────────────────────────────────────────────────
log "Installation de Node.js 20..."
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt-get install -y -qq nodejs
node -v
log "Node.js installé"

# ── 7. PROJET LARAVEL ────────────────────────────────────────────────────────
APP_DIR="/var/www/artisanhub"
log "Configuration du projet Laravel dans $APP_DIR..."

# Cloner ou créer le dossier
if [ ! -d "$APP_DIR" ]; then
    mkdir -p $APP_DIR
fi

# Permissions (le fichier .env sera verrouillé après sa création)
chown -R www-data:www-data $APP_DIR
find $APP_DIR -type d -exec chmod 755 {} \;
find $APP_DIR -type f -exec chmod 644 {} \;

log "Dossier projet prêt"

# ── 8. NGINX CONFIG ───────────────────────────────────────────────────────────
log "Configuration Nginx..."
cat > /etc/nginx/sites-available/artisanhub << 'NGINX'
server {
    listen 80;
    listen [::]:80;
    server_name artisanhub.bj www.artisanhub.bj;

    root /var/www/artisanhub/public;
    index index.php index.html;

    charset utf-8;
    client_max_body_size 10M;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Gzip
    gzip on;
    gzip_types text/css application/javascript application/json image/svg+xml;
    gzip_min_length 1000;
}
NGINX

ln -sf /etc/nginx/sites-available/artisanhub /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx
log "Nginx configuré"

# ── 9. SSL (Let's Encrypt) ────────────────────────────────────────────────────
log "Installation de Certbot pour SSL..."
apt-get install -y -qq certbot python3-certbot-nginx
warn "Lance manuellement : certbot --nginx -d artisanhub.bj -d www.artisanhub.bj"

# ── 10. SUPERVISOR (queues Laravel) ──────────────────────────────────────────
log "Installation de Supervisor pour les queues..."
apt-get install -y -qq supervisor

cat > /etc/supervisor/conf.d/artisanhub-worker.conf << 'SUPERVISOR'
[program:artisanhub-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/artisanhub/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/artisanhub-worker.log
stopwaitsecs=3600
SUPERVISOR

supervisorctl reread
supervisorctl update
log "Supervisor configuré (queues emails)"

# ── 11. CRON LARAVEL ──────────────────────────────────────────────────────────
log "Configuration du cron Laravel..."
(crontab -l 2>/dev/null; echo "* * * * * cd /var/www/artisanhub && php artisan schedule:run >> /dev/null 2>&1") | crontab -
log "Cron configuré"

# ── 12. FIREWALL ──────────────────────────────────────────────────────────────
log "Configuration du pare-feu UFW..."
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp
ufw --force enable
log "Firewall activé (22, 80, 443)"

echo ""
echo "================================"
log "Serveur prêt ! Étapes suivantes :"
echo ""
echo "  1. Uploader le projet dans /var/www/artisanhub"
echo "  2. Copier .env.example → .env et remplir les valeurs"
echo "  3. Exécuter : bash /var/www/artisanhub/deploy/SETUP_APP.sh"
echo "  4. Activer SSL : certbot --nginx -d artisanhub.bj"
echo ""
