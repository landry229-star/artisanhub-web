#!/bin/bash
# =============================================================
# ArtisanHub — Script de mise à jour (après modification du code)
# =============================================================

set -e
GREEN='\033[0;32m'; NC='\033[0m'
log() { echo -e "${GREEN}[✓]${NC} $1"; }

APP_DIR="/var/www/artisanhub"
cd $APP_DIR

cleanup() {
    if [ "${UPDATE_FAILED:-0}" -eq 1 ]; then
        php artisan up || true
    fi
}
trap 'UPDATE_FAILED=1; cleanup' ERR

echo "🔄 Mise à jour ArtisanHub..."

# Mode maintenance
php artisan down --retry=60 --refresh=15
log "Mode maintenance activé"

# Pull Git (si dépôt Git)
# git pull origin main

# Dépendances
composer install --no-dev --optimize-autoloader --no-interaction
log "Dépendances mises à jour"

# Migrations
php artisan migrate --force
log "Migrations exécutées"

# Clear caches
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
log "Caches vidés et regénérés"

# Redémarrer queues
supervisorctl restart artisanhub-worker:*
log "Workers redémarrés"

# Désactiver maintenance
php artisan up
trap - ERR
log "Site en ligne !"

echo ""
log "Mise à jour terminée 🎉"
