<?php
namespace App\Http\Controllers\Artisan;

use App\Http\Controllers\Controller;
use App\Models\PortfolioItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PortfolioController extends Controller
{
    public function index()
    {
        $profile = Auth::user()->artisanProfile ?: Auth::user()->artisanProfile()->create([
            'specialty' => 'Artisan',
            'category' => 'autre',
        ]);
        $items   = $profile->portfolioItems()->latest()->get();
        return view('artisan.portfolio.index', compact('profile', 'items'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'image'       => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ]);

        $profile = Auth::user()->artisanProfile ?: Auth::user()->artisanProfile()->create([
            'specialty' => 'Artisan',
            'category' => 'autre',
        ]);

        if ($profile->portfolioItems()->count() >= 20) {
            return back()->with('error', 'Limite de 20 photos atteinte.');
        }

        $path = $request->file('image')->store('portfolio', 'public');

        $profile->portfolioItems()->create([
            'title'       => $request->title,
            'description' => $request->description,
            'image_path'  => $path,
        ]);

        return back()->with('success', 'Photo ajoutée au portfolio.');
    }

    public function destroy(PortfolioItem $item)
    {
        // Vérifier que l'item appartient bien à l'artisan connecté
        abort_if($item->artisanProfile->user_id !== Auth::id(), 403);
        Storage::disk('public')->delete($item->image_path);
        $item->delete();
        return back()->with('success', 'Photo supprimée.');
    }
}
