#!/bin/bash
# Sauvegarde MySQL chiffrée par les permissions du système.

set -euo pipefail

APP_DIR="/var/www/artisanhub"
BACKUP_DIR="/var/backups/artisanhub"
DATE="$(date +%Y%m%d-%H%M%S)"

mkdir -p "$BACKUP_DIR"
chmod 700 "$BACKUP_DIR"

set -a
. "$APP_DIR/.env"
set +a

mysqldump \
    --host="${DB_HOST:-127.0.0.1}" \
    --port="${DB_PORT:-3306}" \
    --user="$DB_USERNAME" \
    --password="$DB_PASSWORD" \
    --single-transaction \
    --quick \
    --routines \
    "$DB_DATABASE" | gzip > "$BACKUP_DIR/${DB_DATABASE}-${DATE}.sql.gz"

chmod 600 "$BACKUP_DIR/${DB_DATABASE}-${DATE}.sql.gz"
find "$BACKUP_DIR" -type f -name '*.sql.gz' -mtime +14 -delete
