<?php

/**
 * Sentry Laravel SDK configuration file.
 *
 * @see https://docs.sentry.io/platforms/php/guides/laravel/configuration/options/
 */
return [
    'dsn' => env('SENTRY_LARAVEL_DSN', env('SENTRY_DSN')),

    // Environnement (local, staging, production)
    'environment' => env('SENTRY_ENVIRONMENT', env('APP_ENV', 'production')),

    // Échantillonnage des traces de performance (0.0 = aucune, 1.0 = toutes)
    // 0.2 = 20% des requêtes → bon compromis coût/visibilité
    'traces_sample_rate' => (float) env('SENTRY_TRACES_SAMPLE_RATE', 0.2),

    // Breadcrumbs : contexte autour de chaque erreur
    'breadcrumbs' => [
        'logs'            => true,   // logs Laravel inclus
        'sql_queries'     => true,   // requêtes SQL
        'queue_info'      => true,   // jobs de queue
        'http_client_requests' => true,
    ],

    // Informations sur l'utilisateur connecté (sans données sensibles)
    'send_default_pii' => false,

    // Ignorer certaines exceptions qui ne méritent pas une alerte
    'ignore_exceptions' => [
        \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,     // 404
        \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException::class,
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Validation\ValidationException::class,
    ],
];
