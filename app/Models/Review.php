<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'order_id', 'client_id', 'artisan_id',
        'rating', 'comment', 'artisan_reply',
    ];

    public function order()   { return $this->belongsTo(Order::class); }
    public function client()  { return $this->belongsTo(User::class, 'client_id'); }
    public function artisan() { return $this->belongsTo(User::class, 'artisan_id'); }

    public function stars(): string
    {
        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }
}
