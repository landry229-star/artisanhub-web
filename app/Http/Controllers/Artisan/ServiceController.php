<?php
namespace App\Http\Controllers\Artisan;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    public function index()
    {
        $profile  = Auth::user()->artisanProfile ?: Auth::user()->artisanProfile()->create([
            'specialty' => 'Artisan',
            'category' => 'autre',
        ]);
        $services = $profile->services()->latest()->get();
        return view('artisan.services.index', compact('profile', 'services'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => ['required', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:1000'],
            'price'       => ['required', 'integer', 'min:500'],
            'delay_days'  => ['required', 'integer', 'min:1', 'max:90'],
            'image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ]);

        $profile = Auth::user()->artisanProfile ?: Auth::user()->artisanProfile()->create([
            'specialty' => 'Artisan',
            'category' => 'autre',
        ]);

        if ($profile->services()->count() >= 10) {
            return back()->with('error', 'Maximum 10 services autorisés.');
        }

        $path = $request->hasFile('image')
            ? $request->file('image')->store('services', 'public')
            : null;

        $profile->services()->create([
            'title'       => $request->title,
            'description' => $request->description,
            'price'       => $request->price,
            'delay_days'  => $request->delay_days,
            'image_path'  => $path,
            'is_active'   => true,
        ]);

        return back()->with('success', '✅ Service ajouté avec succès.');
    }

    public function toggle(Service $service)
    {
        abort_if($service->artisanProfile->user_id !== Auth::id(), 403);
        $service->update(['is_active' => !$service->is_active]);
        $label = $service->is_active ? 'activé' : 'désactivé';
        return back()->with('success', "Service {$label}.");
    }

    public function edit(Service $service)
    {
        abort_if($service->artisanProfile->user_id !== Auth::id(), 403);
        return view('artisan.services.edit', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        abort_if($service->artisanProfile->user_id !== Auth::id(), 403);
        $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:1000'],
            'price' => ['required', 'integer', 'min:500'],
            'delay_days' => ['required', 'integer', 'min:1', 'max:90'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ]);

        $data = $request->only('title', 'description', 'price', 'delay_days');
        if ($request->hasFile('image')) {
            if ($service->image_path) {
                Storage::disk('public')->delete($service->image_path);
            }
            $data['image_path'] = $request->file('image')->store('services', 'public');
        }
        $service->update($data);
        return redirect()->route('artisan.services.index')->with('success', '✅ Service modifié avec succès.');
    }

    public function destroy(Service $service)
    {
        abort_if($service->artisanProfile->user_id !== Auth::id(), 403);
        if ($service->image_path) {
            Storage::disk('public')->delete($service->image_path);
        }
        $service->delete();
        return back()->with('success', 'Service supprimé.');
    }
}
