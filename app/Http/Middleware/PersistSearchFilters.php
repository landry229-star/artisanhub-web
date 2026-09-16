<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PersistSearchFilters
{
    public function handle(Request $request, Closure $next)
    {
        $keys = ['q','city','category','available','price_min','price_max','min_rating','sort'];
        if ($request->routeIs('artisans.index')) {
            $hasFilters = collect($keys)->some(fn($k) => $request->filled($k));
            if ($hasFilters) {
                session(['search_filters' => $request->only($keys)]);
            } elseif (session()->has('search_filters') && !$request->has('reset')) {
                return redirect()->route('artisans.index', session('search_filters'));
            }
        }
        return $next($request);
    }
}
