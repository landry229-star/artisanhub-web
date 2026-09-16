<?php
namespace App\Exceptions;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Sentry\Laravel\Integration;
use Sentry\State\Scope;
use Throwable;
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
   ->withMiddleware(function (\Illuminate\Foundation\Configuration\Middleware $middleware) {
    $middleware->alias([
        'role'           => \App\Http\Middleware\CheckRole::class,
        'persist-search' => \App\Http\Middleware\PersistSearchFilters::class,
        'verify-email'   => \App\Http\Middleware\EnsureEmailIsVerified::class,
        'active'         => \App\Http\Middleware\EnsureUserIsActive::class,
    ]);

    // Rate limiting login (brute force)
    $middleware->appendToGroup('web', [
        \App\Http\Middleware\LoginRateLimiter::class,
    ]);

    // Validation MIME sur les routes avec upload
    $middleware->appendToGroup('web', [
        \App\Http\Middleware\ValidateFileUploads::class,
        \App\Http\Middleware\EnsureUserIsActive::class,
    ]);
})

    // Middleware global (s'applique à toutes les routes authentifiées)

    ->withExceptions(function (Exceptions $exceptions): void {


// Dans bootstrap/app.php, dans ->withExceptions(function (Exceptions $exceptions) {

// ── Enrichir chaque erreur avec le contexte utilisateur ──────────────────────
$exceptions->report(function (Throwable $e) {
    if (app()->bound('sentry')) {
        \Sentry\withScope(function (Scope $scope) use ($e) {
            $user = Auth::user();

            // Contexte utilisateur (sans mot de passe ni token)
            if ($user) {
                $scope->setUser([
                    'id'    => $user->id,
                    'email' => $user->email,
                    'role'  => $user->role,
                ]);
            }

            // Tags pour filtrer dans Sentry
            $scope->setTag('app', 'artisanhub');
            $scope->setTag('php_version', PHP_VERSION);

            // Contexte de la requête courante
            if (request()) {
                $scope->setContext('request_context', [
                    'url'    => request()->fullUrl(),
                    'method' => request()->method(),
                    'ip'     => request()->ip(),
                    'route'  => optional(request()->route())->getName(),
                ]);
            }
        });
    }
});

// ── Alertes critiques métier (Slack/email en plus de Sentry) ─────────────────
// Ces erreurs envoient aussi un email d'alerte immédiate à l'admin

$exceptions->report(function (\Exception $e) {
    $criticalPatterns = [
        'FedaPay',     // erreur de paiement
        'Payment',     // erreur sur les paiements
        'SQLSTATE',    // erreur de base de données
        'Connection',  // problème de connexion
    ];

    foreach ($criticalPatterns as $pattern) {
        if (str_contains($e->getMessage(), $pattern)) {
            \Illuminate\Support\Facades\Log::critical('🚨 ERREUR CRITIQUE ArtisanHub', [
                'message'   => $e->getMessage(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                'url'       => request()?->fullUrl(),
                'user_id'   => Auth::id(),
            ]);
            break;
        }
    }
});

// ── Pages d'erreur personnalisées ────────────────────────────────────────────
$exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, Request $request) {
    return response()->view('errors.404', [], 404);
});

$exceptions->render(function (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e, Request $request) {
    return response()->view('errors.403', [], 403);
});

$exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, Request $request) {
    return response()->view('errors.419', [], 419);
});
    })->create();
