@extends('layouts.dashboard')
@section('title', 'Mes avis')
@section('page-title', 'Mes avis')

@section('sidebar-nav')
    @include('artisan.partials.sidebar')
@endsection

@section('content')

@if($withoutReply > 0)
    <div class="alert d-flex align-items-center gap-2" style="background:#FFF3CD;border:1px solid #FFE69C;color:#856404">
        <i class="bi bi-info-circle"></i>
        Vous avez <strong>{{ $withoutReply }}</strong> avis sans réponse. Répondre publiquement rassure les futurs clients.
    </div>
@endif

@if($reviews->isEmpty())
    <div class="content-card text-center py-5">
        <p style="font-size:2rem">⭐</p>
        <h5 class="fw-700">Aucun avis pour l'instant</h5>
        <p class="text-muted">Les avis de vos clients apparaîtront ici après chaque commande terminée.</p>
    </div>
@else
    @foreach($reviews as $review)
        <div class="content-card mb-3">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                <div class="d-flex align-items-center gap-2">
                    <img src="{{ $review->client->avatarUrl() }}" class="rounded-circle" width="40" height="40"
                         style="object-fit:cover" alt="{{ $review->client->name }}">
                    <div>
                        <div class="fw-700">{{ $review->client->name }}</div>
                        <div class="text-muted" style="font-size:.78rem">
                            {{ $review->created_at->format('d/m/Y') }}
                            @if($review->order)
                                · {{ $review->order->title }}
                            @endif
                        </div>
                    </div>
                </div>
                <span style="color:#D4A853">
                    @for($i=1;$i<=5;$i++)<i class="bi bi-star{{ $i<=$review->rating?'-fill':'' }}"></i>@endfor
                </span>
            </div>

            @if($review->comment)
                <p class="mb-3">{{ $review->comment }}</p>
            @else
                <p class="text-muted mb-3" style="font-size:.85rem">Aucun commentaire laissé.</p>
            @endif

            @if($review->artisan_reply)
                <div class="p-3 rounded-3" style="background:#F5EFE6">
                    <div class="fw-600 mb-1" style="font-size:.82rem;color:#C4622D">
                        <i class="bi bi-reply-fill me-1"></i>Votre réponse
                    </div>
                    <p class="mb-0" style="font-size:.9rem">{{ $review->artisan_reply }}</p>
                </div>
            @elseif($review->comment)
                <form method="POST" action="{{ route('reviews.reply', $review) }}" class="d-flex gap-2">
                    @csrf @method('PATCH')
                    <input type="text" name="reply" class="form-control form-control-sm"
                           placeholder="Répondre publiquement à cet avis..." maxlength="300" required>
                    <button type="submit" class="btn btn-clay btn-sm text-nowrap">Répondre</button>
                </form>
            @endif
        </div>
    @endforeach

    <div class="d-flex justify-content-center mt-3">
        {{ $reviews->links() }}
    </div>
@endif

@endsection
