<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next)
    {
        $exempt = [
            'email.notice','email.verify','email.send-verification',
            'logout','home','login','register',
            'artisans.index','artisans.show','category.show','category.city.show',
            'city.show','mentions','remboursement','support','support.send',
            'how_it_works','cgu','privacy','sitemap','payment.callback',
        ];

        if (
            auth()->check()
            && !auth()->user()->email_verified_at
            && !auth()->user()->is_seeded
            && !auth()->user()->isAdmin()
            && !$request->routeIs(...$exempt)
        ) {
            return redirect()->route('email.notice')
                ->with('warning', 'Veuillez vérifier votre email pour accéder à votre espace.');
        }

        if (auth()->check() && auth()->user()->is_seeded && !auth()->user()->email_verified_at) {
            session()->flash('warning', 'Compte de démonstration : la vérification email est désactivée.');
        }

        return $next($request);
    }
}
