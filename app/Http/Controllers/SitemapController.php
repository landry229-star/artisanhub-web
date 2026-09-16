<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Génère le sitemap.xml : pages statiques + profils artisans actifs.
     * On ne liste que les URLs publiques et indexables (pas les pages
     * auth/dashboard qui sont déjà en noindex ou derrière un middleware auth).
     */
    public function index(): Response
    {
        $urls = collect();

        // Pages statiques publiques
        $urls->push(['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'weekly']);
        $urls->push(['loc' => route('artisans.index'), 'priority' => '0.9', 'changefreq' => 'daily']);
        $urls->push(['loc' => route('how_it_works'), 'priority' => '0.5', 'changefreq' => 'monthly']);
        $urls->push(['loc' => route('register'), 'priority' => '0.6', 'changefreq' => 'monthly']);

        // Pages catégorie (SEO local par métier)
        foreach (array_keys(config('artisanhub.categories')) as $category) {
            $urls->push(['loc' => route('category.show', $category), 'priority' => '0.8', 'changefreq' => 'daily']);
        }

        // Pages ville (SEO local par commune)
        foreach (config('artisanhub.cities') as $city) {
            $urls->push(['loc' => route('city.show', \Illuminate\Support\Str::slug($city)), 'priority' => '0.7', 'changefreq' => 'daily']);
        }

        // Profils artisans actifs — cœur du contenu indexable du site
        User::where('role', 'artisan')
            ->where('is_active', true)
            ->select('id', 'name', 'city', 'updated_at')
            ->with('artisanProfile:id,user_id,specialty')
            ->chunk(500, function ($artisans) use ($urls) {
                foreach ($artisans as $artisan) {
                    $urls->push([
                        'loc'        => route('artisans.show', $artisan->routeSlug()),
                        'lastmod'    => $artisan->updated_at->toAtomString(),
                        'priority'   => '0.7',
                        'changefreq' => 'weekly',
                    ]);
                }
            });

        $xml = view('sitemap', ['urls' => $urls])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
