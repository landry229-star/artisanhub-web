# ArtisanHub — Runbook du pilote fermé

## Objectif

Le pilote fermé valide le produit avec un groupe limité de clients, artisans,
livreurs et administrateurs avant toute ouverture publique. Chaque incident
doit être traçable, assigné et clôturé avec une preuve.

## Portée de test automatisée

La suite `PilotClosedTest` couvre :

- accès admin et isolation des rôles ;
- progression d'une mission livreur et accès interdit aux missions d'autrui ;
- formulaire support et emails interne/client ;
- dépôt KYC privé et déclenchement OTP ;
- avis client et réponse artisan ;
- endpoint `/up` utilisé par le monitoring ;
- paiement, commissions et erreurs FedaPay via `PaymentTest`.

Commande de validation :

```bash
php artisan test --filter='PilotClosedTest|PaymentTest|OrderTest'
```

## Test manuel desktop, mobile, clavier et accessibilité

Pour chaque rôle, exécuter le parcours de connexion, création de commande,
acceptation, livraison, paiement, support et déconnexion :

1. Chrome/Firefox desktop à 1440 px et 1024 px.
2. Mobile à 390 px et 412 px, portrait puis rotation.
3. Navigation clavier uniquement : `Tab`, `Shift+Tab`, `Enter`, `Escape`.
4. Vérifier focus visible, ordre logique, labels de formulaire, contraste,
   messages d'erreur lisibles et absence de défilement horizontal.
5. Tester avec le zoom navigateur à 200 %.
6. Vérifier que les actions sensibles demandent confirmation et que les erreurs
   restent compréhensibles sans couleur seule.

La validation finale doit être signée dans la fiche de recette du pilote avec
le navigateur, l'appareil, le rôle et la date.

## Monitoring et alertes

- `GET /up` : sonde de disponibilité HTTP, à vérifier toutes les 5 minutes.
- Sentry : renseigner `SENTRY_LARAVEL_DSN`, `SENTRY_ENVIRONMENT` et un taux de
  traces adapté dans l'environnement pilote.
- Alerter sur les erreurs critiques FedaPay, SQL, connexion et files d'attente.
- Centraliser les logs Laravel et conserver au minimum 14 jours.
- Définir un responsable et un délai : paiement bloqué sous 30 minutes,
  indisponibilité sous 1 heure, incident non critique sous 1 jour ouvré.

Les clés Sentry et les canaux d'alerte sont des secrets d'exploitation et ne
doivent jamais être ajoutés au dépôt.

## Sauvegarde et restauration

Avant le premier utilisateur réel :

1. sauvegarde MySQL complète horodatée ;
2. sauvegarde chiffrée du stockage privé (`storage/app`) ;
3. test de restauration sur une base et un répertoire isolés ;
4. vérification de quelques commandes, paiements, documents KYC et images ;
5. répétition quotidienne pendant le pilote ;
6. conservation d'au moins 7 sauvegardes quotidiennes et 4 hebdomadaires.

La restauration doit être chronométrée et documentée. Le pilote ne peut pas
être déclaré prêt tant qu'une restauration complète n'a pas été prouvée.

## Support utilisateur réel

- un canal principal : `support@artisanhub.bj` ;
- un canal urgent configuré et réellement surveillé ;
- un tableau de suivi avec identifiant, rôle, commande, priorité, responsable,
  statut, dernière réponse et résolution ;
- accusé de réception automatique puis réponse humaine ;
- revue quotidienne des tickets ouverts et compte-rendu hebdomadaire ;
- procédure d'escalade pour paiement, sécurité, litige et indisponibilité.

## Go / No-Go

Le pilote reste fermé si un paiement ne peut pas être confirmé, si une
restauration échoue, si les emails support ne sont pas délivrés, si un rôle
accède aux données d'un autre, ou si un incident critique n'a pas de
responsable. L'ouverture publique exige la clôture de ces points avec une
preuve datée.

## Après le pilote

- La remise est confirmée par un code à six chiffres ou par une signature
  numérique dessinée par le client dans son espace. Les deux méthodes sont
  conservées avec leur date et leur mode de vérification.
- La suppression d'un compte utilisateur anonymise les données personnelles,
  supprime les fichiers privés et désactive le compte, tout en conservant les
  informations nécessaires aux commandes et paiements déjà réalisés.
- Une migration ajoute les champs de preuve et les index `ville/quartier` ;
  elle doit être exécutée avec `php artisan migrate --force` avant le pilote.
- Une base PWA est disponible avec le manifeste et le service worker. Les
  notifications push natives restent à connecter à un fournisseur avant
  l'application mobile complète.
- WhatsApp/SMS dispose déjà de pilotes `log`, `meta` et `sms`. Le pilote réel
  nécessite les identifiants du fournisseur et un test de délivrabilité.
- La recherche par quartier est disponible via le filtre `quartier` et est
  indexée pour les recherches fréquentes.
