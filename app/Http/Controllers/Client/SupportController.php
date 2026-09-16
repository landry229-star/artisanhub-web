<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\GuaranteeClaim;
use App\Models\SupportTicket;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    public function index()
    {
        $tickets = SupportTicket::where('user_id', Auth::id())->latest()->paginate(10);
        $claims = GuaranteeClaim::where('client_id', Auth::id())
            ->with('order')
            ->latest()
            ->paginate(10, ['*'], 'claims');

        return view('client.support.index', compact('tickets', 'claims'));
    }
}
