@extends('layouts.app')
@section('title', 'Vérifiez votre email')
@section('robots', 'noindex, follow')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="text-center mb-4">
                <div style="font-size:4rem">📧</div>
                <h2 class="mt-3" style="font-family:'Playfair Display',serif">Vérifiez votre email</h2>
                <p class="text-muted">
                    Un lien de vérification a été envoyé à<br>
                    <strong>{{ auth()->user()->email }}</strong>
                </p>
            </div>

            @foreach(['success'=>'success','error'=>'danger','warning'=>'warning','info'=>'info'] as $type => $class)
                @if(session($type))
                    <div class="alert alert-{{ $class }} alert-dismissible fade show mb-4">
                        {{ session($type) }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
            @endforeach

            {{-- Étapes --}}
            <div class="content-card mb-4">
                <p class="section-label mb-3">QUE FAIRE MAINTENANT ?</p>
                @foreach([
                    ['num'=>'1','title'=>'Ouvrez votre boîte email','desc'=>'Cherchez un email de ArtisanHub : « Vérifiez votre adresse email »','bg'=>'#F5EFE6','color'=>'#C4622D'],
                    ['num'=>'2','title'=>'Cliquez sur le lien','desc'=>'Le lien est valable 24 heures. Un seul clic suffit.','bg'=>'#F5EFE6','color'=>'#C4622D'],
                    ['num'=>'✓','title'=>'Votre compte est activé !','desc'=>'Vous accédez automatiquement à votre espace.','bg'=>'#D4EDDA','color'=>'#155724'],
                ] as $step)
                    <div class="d-flex gap-3 {{ !$loop->last ? 'mb-3' : '' }}">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:36px;height:36px;background:{{ $step['bg'] }};
                                    color:{{ $step['color'] }};font-weight:700;font-size:.9rem">
                            {{ $step['num'] }}
                        </div>
                        <div>
                            <div class="fw-600" style="color:{{ $step['color'] }}">{{ $step['title'] }}</div>
                            <div class="text-muted" style="font-size:.85rem">{!! $step['desc'] !!}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Renvoyer --}}
            <div class="card p-4 mb-4" style="background:#F5EFE6;border-color:#ECD8C6">
                <p class="fw-600 mb-1">
                    <i class="bi bi-envelope me-2 text-clay"></i>Email non reçu ?
                </p>
                <p class="text-muted mb-3" style="font-size:.85rem">
                    Vérifiez vos <strong>spams / courriers indésirables</strong>.
                </p>
                <form action="{{ route('email.send-verification') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-clay">
                        <i class="bi bi-send me-2"></i>Renvoyer l'email
                    </button>
                </form>
            </div>

            <div class="text-center">
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-box-arrow-right me-1"></i>Se déconnecter
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
