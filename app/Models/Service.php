<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'artisan_profile_id', 'title', 'description',
        'price', 'delay_days', 'image_path', 'is_active',
    ];

    protected $casts = [
        'price'     => 'integer',
        'is_active' => 'boolean',
    ];

    public function artisanProfile()
    {
        return $this->belongsTo(ArtisanProfile::class);
    }

    public function imageUrl(): string
    {
        return $this->image_path
            ? asset('storage/' . $this->image_path)
            : asset('images/service-placeholder.png');
    }

    public function formattedPrice(): string
    {
        return number_format($this->price, 0, ',', ' ') . ' XOF';
    }

    public function delayLabel(): string
    {
        return $this->delay_days === 1
            ? '1 jour'
            : "{$this->delay_days} jours";
    }
}
