#!/bin/bash
# =============================================================
# ArtisanHub — Configuration de l'application Laravel
# À exécuter APRÈS avoir uploadé le code et configuré .env
# =============================================================

set -e
GREEN='\033[0;32m'; YELLOW='\033[1;33m'; NC='\033[0m'
log()  { echo -e "${GREEN}[✓]${NC} $1"; }
warn() { echo -e "${YELLOW}[!]${NC} $1"; }

APP_DIR="/var/www/artisanhub"
cd $APP_DIR

echo "🏺 Configuration de l'application ArtisanHub..."
echo ""

# Permissions
log "Permissions des dossiers..."
chown -R www-data:www-data $APP_DIR
chmod -R 755 $APP_DIR
chmod -R 775 storage bootstrap/cache
chmod 640 .env

# Dépendances
log "Installation des dépendances Composer (production)..."
composer install --no-dev --optimize-autoloader --no-interaction

# La clé d'application doit être définie avant le déploiement et ne doit
# jamais être régénérée sur une installation existante.
if ! grep -q '^APP_KEY=base64:' .env; then
    echo "APP_KEY absent ou invalide dans .env" >&2
    exit 1
fi

# Cache config
log "Mise en cache de la configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Migrations
log "Exécution des migrations..."
php artisan migrate --force

# Seeder (optionnel)
warn "Voulez-vous exécuter le seeder ? (données de test)"
read -p "Exécuter le seeder ? (o/N) : " -n 1 -r
echo
if [[ $REPLY =~ ^[Oo]$ ]]; then
    php artisan db:seed --force
    log "Seeder exécuté"
fi

# Storage link
log "Lien symbolique storage..."
php artisan storage:link

# Table queues
log "Création de la table des queues..."
php artisan queue:table 2>/dev/null || true
php artisan migrate --force

# Redémarrer services
log "Redémarrage des services..."
supervisorctl restart artisanhub-worker:*
systemctl reload php8.3-fpm
systemctl reload nginx

echo ""
echo "================================"
log "Application déployée avec succès !"
echo ""
warn "Vérifie que .env contient bien :"
echo "  - APP_KEY (généré)"
echo "  - DB_PASSWORD"
echo "  - FEDAPAY_SECRET_KEY"
echo "  - MAILGUN_SECRET"
echo ""
