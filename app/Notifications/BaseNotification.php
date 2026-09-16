<?php
namespace App\Notifications;

/**
 * Trait à ajouter dans toutes les notifications ArtisanHub.
 * Ajoute le canal 'database' en plus du 'mail' existant.
 * Chaque notification doit implémenter toDatabase().
 */
trait SendsToDatabase
{
    /**
     * Canaux : mail + database (pour la cloche en temps réel)
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }
}
