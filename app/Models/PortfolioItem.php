<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortfolioItem extends Model
{
    protected $fillable = ['artisan_profile_id', 'title', 'description', 'image_path'];

    public function artisanProfile()
    {
        return $this->belongsTo(ArtisanProfile::class);
    }

    public function imageUrl(): string
    {
        return asset('storage/' . $this->image_path);
    }
}
