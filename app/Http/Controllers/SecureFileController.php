<?php

namespace App\Http\Controllers;

use App\Models\GuaranteeClaim;
use App\Models\Message;
use App\Models\Order;
use App\Models\OrderImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SecureFileController extends Controller
{
    public function contract(Order $order)
    {
        abort_unless($this->isAdmin() || in_array(Auth::id(), [$order->client_id, $order->artisan_id]), 403);

        return $this->serve($order->contract_path);
    }

    public function contractDownload(Order $order)
    {
        abort_unless($this->isAdmin() || in_array(Auth::id(), [$order->client_id, $order->artisan_id]), 403);
        abort_unless($order->contract_path && Storage::disk('local')->exists($order->contract_path), 404);

        return Storage::disk('local')->download($order->contract_path, "contrat-commande-{$order->id}.pdf");
    }

    public function message(Message $message, string $kind)
    {
        abort_unless(
            in_array(Auth::id(), [$message->sender_id, $message->recipient_id], true)
            && in_array(Auth::id(), $message->order->chatParticipantIds(), true),
            403
        );
        abort_unless(in_array($kind, ['attachment', 'audio'], true), 404);

        return $this->serve($message->{$kind . '_path'});
    }

    public function claim(GuaranteeClaim $claim)
    {
        abort_unless($this->isAdmin() || in_array(Auth::id(), [$claim->client_id, $claim->artisan_id]), 403);

        return $this->serve($claim->evidence_path);
    }

    public function completion(Order $order)
    {
        abort_unless($this->isAdmin() || in_array(Auth::id(), [$order->client_id, $order->artisan_id], true), 403);
        return $this->serve($order->completion_photo_path);
    }

    public function orderImage(Order $order, OrderImage $image)
    {
        abort_unless($this->isAdmin() || in_array(Auth::id(), [$order->client_id, $order->artisan_id], true), 403);
        abort_unless($image->order_id === $order->id, 404);

        return $this->serve($image->path);
    }

    private function serve(?string $path)
    {
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }

    private function isAdmin(): bool
    {
        return Auth::user()?->role === 'admin';
    }
}
