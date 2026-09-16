@extends('layouts.app')
@section('title', 'Support client')
@section('meta_description', "Besoin d'aide sur ArtisanHub ? Contactez notre support client pour toute question sur vos commandes, paiements ou votre compte artisan.")

@section('content')
<div class="container py-5" style="max-width:900px">

    <div class="mb-5 text-center">
        <p style="color:#C4622D;font-size:.75rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase" class="mb-2">AIDE</p>
        <h1 style="font-family:'Playfair Display',serif;font-size:2rem">Comment pouvons-nous vous aider ?</h1>
        <p class="text-muted">Notre équipe répond sous 24h ouvrées</p>
    </div>

    {{-- Canaux de contact rapide --}}
    <div class="row g-3 mb-5">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4 h-100 text-center">
                <div style="font-size:2rem;margin-bottom:12px">📧</div>
                <h3 style="font-size:1rem;font-weight:700;color:#2C1A0E">Email</h3>
                <p class="text-muted" style="font-size:.9rem">Pour les questions générales et les litiges</p>
                <a href="mailto:support@artisanhub.bj" style="color:#C4622D;font-weight:600">support@artisanhub.bj</a>
                <div class="mt-2 text-muted" style="font-size:.8rem">Réponse sous 24h</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4 h-100 text-center">
                <div style="font-size:2rem;margin-bottom:12px">💬</div>
                <h3 style="font-size:1rem;font-weight:700;color:#2C1A0E">WhatsApp</h3>
                <p class="text-muted" style="font-size:.9rem">Pour les urgences et problèmes de paiement</p>
                <a href="https://wa.me/22997000000" target="_blank" style="color:#C4622D;font-weight:600">+229 97 00 00 00</a>
                <div class="mt-2 text-muted" style="font-size:.8rem">Lun–Ven, 8h–18h</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4 h-100 text-center">
                <div style="font-size:2rem;margin-bottom:12px">⚖️</div>
                <h3 style="font-size:1rem;font-weight:700;color:#2C1A0E">Litige</h3>
                <p class="text-muted" style="font-size:.9rem">Problème avec une commande spécifique</p>
                @auth
                    <a href="{{ route('client.orders.index') }}" style="color:#C4622D;font-weight:600">Signaler un litige</a>
                @else
                    <a href="{{ route('login') }}" style="color:#C4622D;font-weight:600">Se connecter d'abord</a>
                @endauth
                <div class="mt-2 text-muted" style="font-size:.8rem">Décision sous 48h ouvrées</div>
            </div>
        </div>
    </div>

    <div class="row g-4">

        {{-- Formulaire de contact --}}
        <div class="col-md-7">
            <div class="card border-0 shadow-sm p-4">
                <h2 style="font-size:1.2rem;font-weight:700;color:#2C1A0E;margin-bottom:20px">📝 Nous écrire</h2>

                @if(session('success'))
                    <div class="alert alert-success">
                        ✅ {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('support.send') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Votre nom</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', auth()->user()?->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Votre email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email', auth()->user()?->email) }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Sujet</label>
                        <select name="subject" class="form-select @error('subject') is-invalid @enderror" required>
                            <option value="">Choisir un sujet</option>
                            <option value="paiement" {{ old('subject') == 'paiement' ? 'selected' : '' }}>💳 Problème de paiement</option>
                            <option value="commande"  {{ old('subject') == 'commande'  ? 'selected' : '' }}>📦 Problème de commande</option>
                            <option value="compte"    {{ old('subject') == 'compte'    ? 'selected' : '' }}>👤 Problème de compte</option>
                            <option value="remboursement" {{ old('subject') == 'remboursement' ? 'selected' : '' }}>💰 Demande de remboursement</option>
                            <option value="livreur"   {{ old('subject') == 'livreur'   ? 'selected' : '' }}>🚴 Problème de livraison</option>
                            <option value="autre"     {{ old('subject') == 'autre'     ? 'selected' : '' }}>❓ Autre</option>
                        </select>
                        @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Votre message</label>
                        <textarea name="message" rows="5"
                            class="form-control @error('message') is-invalid @enderror"
                            placeholder="Décrivez votre problème en détail. Si c'est lié à une commande, indiquez son numéro."
                            required>{{ old('message') }}</textarea>
                        @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" class="btn w-100 text-white fw-semibold"
                        style="background:#C4622D;padding:10px">
                        Envoyer le message
                    </button>
                </form>
            </div>
        </div>

        {{-- FAQ rapide --}}
        <div class="col-md-5">
            <div class="card border-0 shadow-sm p-4">
                <h2 style="font-size:1.2rem;font-weight:700;color:#2C1A0E;margin-bottom:20px">❓ Questions fréquentes</h2>

                @php
                $faqs = [
                    ['q' => 'Comment payer une commande ?', 'r' => 'Le paiement s\'effectue via Mobile Money (MTN ou Moov) après avoir validé la livraison. Vous serez redirigé vers FedaPay.'],
                    ['q' => 'Que faire si l\'artisan ne répond pas ?', 'r' => 'Si l\'artisan n\'a pas répondu sous 48h, vous pouvez annuler la commande depuis votre espace client.'],
                    ['q' => 'Comment signaler un litige ?', 'r' => 'Depuis la page de votre commande, cliquez sur "Signaler un litige". Notre équipe intervient sous 48h ouvrées.'],
                    ['q' => 'Puis-je obtenir un remboursement ?', 'r' => 'Oui, selon les cas prévus dans notre politique de remboursement. Consultez la page dédiée pour en savoir plus.'],
                    ['q' => 'Comment modifier mon profil ?', 'r' => 'Rendez-vous dans votre tableau de bord → Mon profil → Modifier.'],
                    ['q' => 'Ma commande est bloquée, que faire ?', 'r' => 'Contactez-nous via ce formulaire ou WhatsApp en précisant le numéro de commande.'],
                ];
                @endphp

                <div class="accordion accordion-flush" id="faqAccordion">
                    @foreach($faqs as $i => $faq)
                    <div class="accordion-item border-bottom" style="border:none">
                        <h3 class="accordion-header">
                            <button class="accordion-button collapsed px-0 py-3"
                                style="font-size:.9rem;font-weight:600;background:transparent;box-shadow:none;color:#2C1A0E"
                                type="button" data-bs-toggle="collapse" data-bs-target="#faq{{ $i }}">
                                {{ $faq['q'] }}
                            </button>
                        </h3>
                        <div id="faq{{ $i }}" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body px-0 py-2 text-muted" style="font-size:.9rem">
                                {{ $faq['r'] }}
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="mt-3 p-3 rounded-3" style="background:#F5EFE6">
                    <div style="font-size:.85rem;color:#5C3D1E">
                        <strong>Vous ne trouvez pas la réponse ?</strong><br>
                        Écrivez-nous via le formulaire ou sur WhatsApp. Nous répondons sous 24h.
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
