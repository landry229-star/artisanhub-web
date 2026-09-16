# 🚀 Guide de déploiement ArtisanHub

## Prérequis

- VPS Ubuntu 22.04 (min. 2 Go RAM, 20 Go disque)
- Accès root SSH
- Domaine pointant sur l'IP du VPS
- Compte FedaPay (app.fedapay.com)
- Compte Mailgun (mailgun.com)

---

## Étape 1 — Préparer le serveur

```bash
ssh root@IP_DU_VPS
wget https://raw.githubusercontent.com/ton-repo/artisanhub/main/deploy/DEPLOY.sh
bash DEPLOY.sh
```

Ce script installe automatiquement :
- PHP 8.3 + extensions
- Nginx
- MySQL 8
- Composer
- Node.js 20
- Supervisor (queues)
- UFW (firewall)

---

## Étape 2 — Uploader le code

```bash
# Option A — Git (recommandé)
cd /var/www/artisanhub
git init
git remote add origin https://github.com/ton-compte/artisanhub.git
git pull origin main

# Option B — SFTP
# Uploader tous les fichiers dans /var/www/artisanhub via FileZilla
```

---

## Étape 3 — Configurer l'environnement

```bash
cd /var/www/artisanhub
cp .env.example .env
nano .env
```

### Variables obligatoires à remplir

```env
APP_URL=https://artisanhub.bj

DB_PASSWORD=  # Récupérer dans /root/artisanhub_credentials.txt

FEDAPAY_PUBLIC_KEY=pk_live_XXXX   # app.fedapay.com > API Keys
FEDAPAY_SECRET_KEY=sk_live_XXXX
FEDAPAY_ENV=live
FEDAPAY_WEBHOOK_SECRET=wh_XXXX    # app.fedapay.com > Webhooks

MAILGUN_DOMAIN=artisanhub.bj      # mailgun.com > Sending > Domains
MAILGUN_SECRET=key-XXXX
```

---

## Étape 4 — Initialiser l'application

```bash
bash /var/www/artisanhub/deploy/SETUP_APP.sh
```

Le fichier `.env` doit contenir une `APP_KEY` générée une seule fois. Le script
refuse de démarrer si elle est absente et ne la régénère jamais.

---

## Étape 5 — Activer le SSL (HTTPS)

```bash
certbot --nginx -d artisanhub.bj -d www.artisanhub.bj
```

Renouvellement automatique (Certbot le configure seul).

---

## Étape 6 — Configurer FedaPay Webhook

1. Aller sur **app.fedapay.com** → Webhooks
2. Ajouter une URL : `https://artisanhub.bj/paiement/callback`
3. Événements à cocher :
   - `transaction.approved`
   - `transaction.declined`
   - `transaction.canceled`
4. Copier le **Webhook Secret** dans `.env` (`FEDAPAY_WEBHOOK_SECRET`)

---

## Étape 7 — Configurer Mailgun

1. Aller sur **mailgun.com** → Sending → Domains
2. Ajouter le domaine `artisanhub.bj`
3. Ajouter les enregistrements DNS indiqués par Mailgun
4. Attendre la validation DNS (quelques minutes)
5. Copier la clé API dans `.env` (`MAILGUN_SECRET`)

---

## Commandes utiles après déploiement

```

### Sauvegarde MySQL

Configurer une sauvegarde quotidienne (rétention de 14 jours) :

```bash
sudo chmod 700 /var/www/artisanhub/deploy/BACKUP_DB.sh
sudo crontab -e
0 2 * * * /var/www/artisanhub/deploy/BACKUP_DB.sh >> /var/log/artisanhub-backup.log 2>&1
```

Tester régulièrement la restauration sur une base séparée ; une sauvegarde non
restaurée n'est pas une procédure de reprise validée.

### Restauration MySQL

Sur une base de restauration dédiée, vérifier d'abord l'archive puis importer :

```bash
gunzip -c /var/backups/artisanhub/artisanhub-AAAA-MM-JJ.sql.gz \
  | mysql -u"$DB_USERNAME" -p"$DB_PASSWORD" artisanhub_restore
```

Contrôler ensuite les tables et le nombre d'utilisateurs avant toute remise en
production.

```bash
# Voir les logs
tail -f /var/log/nginx/error.log
tail -f /var/www/artisanhub/storage/logs/laravel.log

# Voir le statut des queues
supervisorctl status

# Redémarrer les workers
supervisorctl restart artisanhub-worker:*

# Vider les caches
cd /var/www/artisanhub
php artisan optimize:clear

# Mode maintenance
php artisan down
php artisan up

# Mettre à jour le code
bash /var/www/artisanhub/deploy/UPDATE.sh
```

---

## Structure des fichiers importants

```
/var/www/artisanhub/          → Code de l'application
/var/log/artisanhub-worker.log → Logs des queues
/var/log/nginx/error.log       → Logs Nginx
/etc/nginx/sites-available/artisanhub → Config Nginx
/root/artisanhub_credentials.txt      → Mots de passe générés
```

---

## Résolution de problèmes courants

| Problème | Solution |
|---|---|
| Page blanche | `php artisan config:cache && php artisan view:cache` |
| Emails non envoyés | `supervisorctl restart artisanhub-worker:*` |
| Erreur 502 | `systemctl restart php8.3-fpm` |
| Permissions | `chown -R www-data:www-data /var/www/artisanhub/storage` |
| FedaPay webhook échoue | Vérifier `FEDAPAY_WEBHOOK_SECRET` dans `.env` |

---

*ArtisanHub v1.0 — Bénin 🇧🇯*
