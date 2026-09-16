@extends('layouts.dashboard')
@section('title', 'Messages — '.$contact->name)
@section('page-title', 'Messages')

@section('sidebar-nav')
    @if(auth()->user()->isArtisan())
        <div class="sidebar-section-title">Principal</div>
        <a href="{{ route('artisan.dashboard') }}" class="sidebar-link">
            <i class="bi bi-speedometer2"></i> Tableau de bord
        </a>
        <a href="{{ route('artisan.orders.index') }}" class="sidebar-link active">
            <i class="bi bi-bag-check"></i> Mes commandes
        </a>
        <a href="{{ route('contacts.index') }}" class="sidebar-link">
            <i class="bi bi-people"></i> Mes contacts
        </a>
        <div class="mt-2 sidebar-section-title">Mon profil</div>
        <a href="{{ route('artisan.portfolio.index') }}" class="sidebar-link">
            <i class="bi bi-images"></i> Mon portfolio
        </a>
        <a href="{{ route('artisan.profile.edit') }}" class="sidebar-link">
            <i class="bi bi-person-gear"></i> Modifier mon profil
        </a>
    @elseif(auth()->user()->isLivreur())
        <div class="sidebar-section-title">Principal</div>
        <a href="{{ route('livreur.dashboard') }}" class="sidebar-link">
            <i class="bi bi-speedometer2"></i> Tableau de bord
        </a>
        <a href="{{ route('livreur.missions.index') }}" class="sidebar-link active">
            <i class="bi bi-bicycle"></i> Mes missions
        </a>
        <a href="{{ route('contacts.index') }}" class="sidebar-link">
            <i class="bi bi-people"></i> Mes contacts
        </a>
    @else
        <div class="sidebar-section-title">Principal</div>
        <a href="{{ route('client.dashboard') }}" class="sidebar-link">
            <i class="bi bi-speedometer2"></i> Tableau de bord
        </a>
        <a href="{{ route('client.orders.index') }}" class="sidebar-link active">
            <i class="bi bi-bag"></i> Mes commandes
        </a>
    <a href="{{ route('contacts.index') }}" class="sidebar-link">
        <i class="bi bi-people"></i> Mes contacts
    </a>
        <div class="mt-2 sidebar-section-title">Découvrir</div>
        <a href="{{ route('artisans.index') }}" class="sidebar-link">
            <i class="bi bi-search"></i> Explorer
        </a>
    @endif
@endsection

@section('topbar-actions')
    @if(auth()->user()->isArtisan())
        <a href="{{ route('artisan.orders.show', $order) }}" class="btn btn-sm btn-outline-clay">
            <i class="bi bi-arrow-left me-1"></i>Retour à la commande
        </a>
    @elseif(auth()->user()->isLivreur())
        <a href="{{ route('livreur.missions.show', $order->delivery) }}" class="btn btn-sm btn-outline-clay">
            <i class="bi bi-arrow-left me-1"></i>Retour à la mission
        </a>
    @else
        <a href="{{ route('client.orders.show', $order) }}" class="btn btn-sm btn-outline-clay">
            <i class="bi bi-arrow-left me-1"></i>Retour à la commande
        </a>
    @endif
@endsection

@section('content')
<div class="row g-4">

    {{-- ── Autres conversations de cette commande (si livreur assigné) ── --}}
    @if($otherContacts->isNotEmpty())
        <div class="col-12">
            <div class="gap-2 d-flex flex-wrap">
                <a href="{{ route('messages.thread', [$order, $contact]) }}"
                   class="btn btn-sm btn-clay">
                    <i class="bi bi-chat-fill me-1"></i>{{ $contact->name }}
                </a>
                @foreach($otherContacts as $other)
                    <a href="{{ route('messages.thread', [$order, $other]) }}"
                       class="btn btn-sm btn-outline-clay">
                        {{ $other->name }}
                        ({{ ['artisan'=>'artisan','client'=>'client','livreur'=>'livreur'][$other->role] ?? $other->role }})
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ── Chat privé avec $contact ── --}}
    <div class="col-lg-8">
        <div class="content-card d-flex flex-column" style="min-height:520px">

            {{-- En-tête conversation --}}
            <div class="gap-3 pb-3 mb-3 d-flex align-items-center" style="border-bottom:1px solid #ECD8C6">
                <img src="{{ $contact->avatarUrl() }}" class="rounded-circle"
                     width="42" height="42" style="object-fit:cover;border:2px solid #C4622D">
                <div class="flex-grow-1">
                    <div class="fw-700">{{ $contact->name }}</div>
                    <div class="text-muted" style="font-size:.75rem">
                        {{ ['artisan'=>'Artisan','client'=>'Client','livreur'=>'Livreur'][$contact->role] ?? $contact->role }}
                        · conversation privée
                    </div>
                </div>
                <span class="badge-status status-{{ $order->status }}">{{ $order->statusLabel() }}</span>
            </div>

            {{-- Zone messages --}}
            <div id="chat-box" class="mb-3 flex-grow-1"
                 style="overflow-y:auto;max-height:380px;padding:4px 0">

                @forelse($messages as $msg)
                    @php $isMine = $msg->sender_id === auth()->id(); @endphp
                    <div class="d-flex gap-2 mb-3 {{ $isMine ? 'flex-row-reverse' : '' }}">

                        <img src="{{ $msg->sender->avatarUrl() }}" class="rounded-circle"
                             width="34" height="34"
                             style="object-fit:cover;flex-shrink:0;align-self:flex-end">

                        <div style="max-width:70%">
                            {{-- Bulle --}}
                            <div class="px-3 py-2 rounded-3"
                                 style="background:{{ $isMine ? '#C4622D' : '#F5EFE6' }};
                                        color:{{ $isMine ? '#fff' : '#2C1A0E' }};
                                        border-bottom-{{ $isMine ? 'right' : 'left' }}-radius:4px">
                                @if($msg->body)
                                    <p style="font-size:.9rem;margin:0;line-height:1.5">{{ $msg->body }}</p>
                                    @if(!$isMine && $msg->translated_body)
                                        <p class="mt-1 mb-0" style="font-size:.8rem;font-style:italic;opacity:.85;border-top:1px solid {{ $isMine ? 'rgba(255,255,255,.3)' : '#ECD8C6' }};padding-top:4px">
                                            <i class="bi bi-translate me-1"></i>{{ $msg->translated_body }}
                                        </p>
                                    @endif
                                @endif

                                @if($msg->isAudio())
                                    <div class="gap-2 d-flex align-items-center" style="margin-top:{{ $msg->body ? '6px' : '0' }}">
                                        <i class="bi bi-mic-fill" style="font-size:1.1rem"></i>
                                        <audio controls preload="none" style="max-width:210px;height:36px">
                                            <source src="{{ route('files.message', [$msg, 'audio']) }}">
                                        </audio>
                                        @if($msg->audio_duration)
                                            <span style="font-size:.72rem;opacity:.8">{{ sprintf('%d:%02d', intdiv($msg->audio_duration,60), $msg->audio_duration%60) }}</span>
                                        @endif
                                    </div>
                                    @if($msg->audio_transcript)
                                        <p class="mt-1 mb-0" style="font-size:.78rem;opacity:.8">
                                            <i class="bi bi-file-text me-1"></i>{{ $msg->audio_transcript }}
                                        </p>
                                    @endif
                                    @if(!$isMine && $msg->translated_body && !$msg->body)
                                        <p class="mt-1 mb-0" style="font-size:.8rem;font-style:italic;opacity:.85">
                                            <i class="bi bi-translate me-1"></i>{{ $msg->translated_body }}
                                        </p>
                                    @endif
                                @endif

                                @if($msg->attachment_path)
                                    @php
                                        $ext = pathinfo($msg->attachment_path, PATHINFO_EXTENSION);
                                        $isImage = in_array(strtolower($ext), ['jpg','jpeg','png','webp']);
                                    @endphp
                                    @if($isImage)
                                        <img src="{{ route('files.message', [$msg, 'attachment']) }}"
                                             class="mt-2 rounded" style="max-width:200px;max-height:160px;object-fit:cover;display:block">
                                    @else
                                        <a href="{{ route('files.message', [$msg, 'attachment']) }}" target="_blank"
                                           class="gap-1 mt-1 d-flex align-items-center"
                                           style="color:{{ $isMine ? '#FFD0B0' : '#C4622D' }};font-size:.82rem;text-decoration:none">
                                            <i class="bi bi-file-earmark-pdf"></i> Voir le document
                                        </a>
                                    @endif
                                @endif
                            </div>
                            {{-- Meta --}}
                            <div class="mt-1 text-muted"
                                 style="font-size:.7rem;text-align:{{ $isMine ? 'right' : 'left' }}">
                                {{ $msg->created_at->format('d/m/Y à H:i') }}
                                @if($isMine && $msg->read_at)
                                    <i class="bi bi-check2-all ms-1" style="color:#4CAF50" title="Lu"></i>
                                @elseif($isMine)
                                    <i class="bi bi-check2 ms-1" style="color:#9A8070" title="Envoyé"></i>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-5 text-center text-muted">
                        <i class="mb-2 bi bi-chat-dots d-block"
                           style="font-size:2.5rem;opacity:.25"></i>
                        <p class="mb-0">Aucun message pour le moment</p>
                        <p style="font-size:.85rem">Commencez la conversation avec {{ $contact->name }}</p>
                    </div>
                @endforelse
            </div>

            {{-- Formulaire envoi --}}
            @if(!in_array($order->status, ['terminee','annulee']))
                <div style="border-top:1px solid #ECD8C6;padding-top:16px">
                    <form action="{{ route('messages.store', [$order, $contact]) }}" method="POST"
                          enctype="multipart/form-data" id="msg-form">
                        @csrf

                        {{-- Aperçu pièce jointe --}}
                        <div id="attachment-preview" class="gap-2 p-2 mb-2 rounded d-none d-flex align-items-center"
                             style="background:#F5EFE6;border:1px solid #ECD8C6">
                            <i class="bi bi-paperclip text-clay"></i>
                            <span id="attachment-name" style="font-size:.82rem;flex:1"></span>
                            <button type="button" class="p-0 btn btn-sm" onclick="clearAttachment()"
                                    style="color:#721C24;background:none;border:none">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </div>

                        <div class="gap-2 d-flex align-items-center">
                            {{-- Zone normale : champ texte --}}
                            <input type="text" name="body" id="msg-input" class="form-control"
                                   placeholder="Écrivez à {{ $contact->name }}..." style="font-size:.9rem"
                                   autocomplete="off">

                            {{-- Zone enregistrement (masquée par défaut, remplace le champ texte
                                 pendant l'appui maintenu sur le micro — comportement WhatsApp) --}}
                            <div id="recording-bar" class="gap-2 px-3 d-none flex-grow-1 align-items-center rounded"
                                 style="background:#FBEAE3;height:38px;font-size:.85rem;user-select:none">
                                <span id="rec-dot" class="rounded-circle" style="width:10px;height:10px;background:#DC3545;display:inline-block;animation:rec-pulse 1s infinite"></span>
                                <span id="rec-timer" class="fw-700">0:00</span>
                                <span id="rec-hint" class="text-muted flex-grow-1 text-center">◀ Glissez pour annuler</span>
                            </div>

                            <label class="btn btn-outline-secondary d-flex align-items-center"
                                   title="Joindre une image ou un PDF" style="cursor:pointer">
                                <i class="bi bi-paperclip"></i>
                                <input type="file" name="attachment" id="attachment-input"
                                       class="d-none"
                                       accept="image/jpeg,image/png,image/webp,application/pdf"
                                       onchange="previewAttachment(this)">
                            </label>

                            {{-- Micro : maintenir enfoncé pour enregistrer, relâcher pour envoyer,
                                 glisser vers la gauche pour annuler (comme WhatsApp) --}}
                            <button type="button" id="mic-btn" class="btn btn-outline-secondary"
                                    title="Maintenir pour enregistrer un vocal">
                                <i class="bi bi-mic" id="mic-icon"></i>
                            </button>

                            <button type="submit" class="px-3 btn btn-clay" title="Envoyer">
                                <i class="bi bi-send"></i>
                            </button>
                        </div>
                        <div class="mt-1 form-text">
                            Images (JPG, PNG, WebP) ou PDF — max 5 Mo · Maintenez le micro pour un vocal (max 10 min)
                        </div>
                    </form>
                </div>
            @else
                <div class="py-2 text-center text-muted" style="font-size:.85rem;border-top:1px solid #ECD8C6;padding-top:12px">
                    <i class="bi bi-lock me-1"></i>
                    La messagerie est fermée — commande {{ $order->statusLabel() }}
                </div>
            @endif

        </div>
    </div>

    {{-- ── Sidebar commande ── --}}
    <div class="col-lg-4">

        <div class="mb-4 content-card">
            <p class="mb-3 section-label">COMMANDE</p>
            <div class="mb-1 fw-700">{{ $order->title }}</div>
            <div class="mb-3 text-muted" style="font-size:.82rem">
                #{{ $order->id }} · {{ $order->created_at->format('d/m/Y') }}
            </div>

            @if($order->budget)
                <div class="mb-2 d-flex justify-content-between" style="font-size:.875rem">
                    <span class="text-muted">Budget</span>
                    <strong class="text-clay">{{ number_format($order->budget,0,',',' ') }} XOF</strong>
                </div>
            @endif
            <div class="d-flex justify-content-between" style="font-size:.875rem">
                <span class="text-muted">Statut</span>
                <span class="badge-status status-{{ $order->status }}">{{ $order->statusLabel() }}</span>
            </div>
        </div>

        <div class="mb-3 text-center content-card">
            <p class="mb-3 section-label">{{ ['artisan'=>'ARTISAN','client'=>'CLIENT','livreur'=>'LIVREUR'][$contact->role] ?? strtoupper($contact->role) }}</p>
            <img src="{{ $contact->avatarUrl() }}" class="mb-2 rounded-circle"
                 width="56" height="56"
                 style="object-fit:cover;border:2px solid #C4622D">
            <h6 class="mb-0 fw-700">{{ $contact->name }}</h6>
            <p class="mb-0 text-muted" style="font-size:.82rem">📍 {{ $contact->city }}</p>
            @if($contact->phone)
                <p class="mb-0 text-muted" style="font-size:.8rem">📞 {{ $contact->phone }}</p>
            @endif
        </div>

    </div>
</div>

<style>
@keyframes rec-pulse { 0%,100%{opacity:1} 50%{opacity:.3} }
</style>
@endsection

@push('scripts')
<script>
    const chatBox = document.getElementById('chat-box');
    if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;

    function previewAttachment(input) {
        if (input.files && input.files[0]) {
            document.getElementById('attachment-name').textContent = input.files[0].name;
            document.getElementById('attachment-preview').classList.remove('d-none');
        }
    }
    function clearAttachment() {
        document.getElementById('attachment-input').value = '';
        document.getElementById('attachment-preview').classList.add('d-none');
        document.getElementById('attachment-name').textContent = '';
    }

    document.getElementById('msg-input')?.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            document.getElementById('msg-form').requestSubmit();
        }
    });

    // ── Vocal façon WhatsApp : appui maintenu = enregistre, relâcher = envoie,
    //    glisser vers la gauche = annule ────────────────────────────────────
    const micBtn       = document.getElementById('mic-btn');
    const micIcon       = document.getElementById('mic-icon');
    const recordingBar  = document.getElementById('recording-bar');
    const recTimerEl    = document.getElementById('rec-timer');
    const recHintEl     = document.getElementById('rec-hint');
    const msgInput      = document.getElementById('msg-input');
    const msgForm       = document.getElementById('msg-form');
    const CANCEL_THRESHOLD = 80; // px glissés vers la gauche pour annuler

    let mediaRecorder = null, audioChunks = [], stream = null;
    let isRecording = false, isCancelled = false;
    let startX = 0, recordStartedAt = 0, timerInterval = null;

    function fmtTime(sec) {
        const m = Math.floor(sec / 60), s = sec % 60;
        return m + ':' + String(s).padStart(2, '0');
    }

    function showRecordingUI() {
        msgInput.classList.add('d-none');
        recordingBar.classList.remove('d-none');
        recordingBar.classList.add('d-flex');
        micBtn.classList.remove('btn-outline-secondary');
        micBtn.classList.add('btn-danger');
        micIcon.className = 'bi bi-mic-fill';
    }
    function hideRecordingUI() {
        msgInput.classList.remove('d-none');
        recordingBar.classList.add('d-none');
        recordingBar.classList.remove('d-flex');
        micBtn.classList.add('btn-outline-secondary');
        micBtn.classList.remove('btn-danger');
        micIcon.className = 'bi bi-mic';
        recHintEl.textContent = '◀ Glissez pour annuler';
        recHintEl.classList.remove('text-danger');
    }

    function startRecording(e) {
        if (isRecording) return;
        if (!navigator.mediaDevices?.getUserMedia) {
            alert("L'enregistrement audio n'est pas supporté par ce navigateur.");
            return;
        }
        startX = (e.touches ? e.touches[0].clientX : e.clientX);
        navigator.mediaDevices.getUserMedia({ audio: true }).then(s => {
            stream = s;
            audioChunks = [];
            isCancelled = false;
            mediaRecorder = new MediaRecorder(stream);
            mediaRecorder.ondataavailable = ev => audioChunks.push(ev.data);
            mediaRecorder.onstop = () => {
                stream.getTracks().forEach(t => t.stop());
                clearInterval(timerInterval);
                hideRecordingUI();
                if (isCancelled || audioChunks.length === 0) return;
                const duration = Math.round((Date.now() - recordStartedAt) / 1000);
                if (duration < 1) return; // trop court, probablement un clic accidentel
                sendVoiceMessage(new Blob(audioChunks, { type: 'audio/webm' }), duration);
            };
            mediaRecorder.start();
            recordStartedAt = Date.now();
            isRecording = true;
            showRecordingUI();
            recTimerEl.textContent = '0:00';
            timerInterval = setInterval(() => {
                recTimerEl.textContent = fmtTime(Math.round((Date.now() - recordStartedAt) / 1000));
            }, 250);
        }).catch(() => {
            alert("Impossible d'accéder au microphone — vérifiez les autorisations du navigateur.");
        });
    }

    function moveRecording(e) {
        if (!isRecording) return;
        const x = (e.touches ? e.touches[0].clientX : e.clientX);
        const dx = x - startX;
        if (dx < -CANCEL_THRESHOLD) {
            isCancelled = true;
            recHintEl.textContent = 'Relâchez pour annuler';
            recHintEl.classList.add('text-danger');
        } else {
            isCancelled = false;
            recHintEl.textContent = '◀ Glissez pour annuler';
            recHintEl.classList.remove('text-danger');
        }
    }

    function endRecording() {
        if (!isRecording) return;
        isRecording = false;
        if (mediaRecorder && mediaRecorder.state === 'recording') mediaRecorder.stop();
    }

    function sendVoiceMessage(blob, duration) {
        const dt = new DataTransfer();
        dt.items.add(new File([blob], 'vocal.webm', { type: 'audio/webm' }));

        let audioInput = document.getElementById('audio-input');
        if (!audioInput) {
            audioInput = document.createElement('input');
            audioInput.type = 'file';
            audioInput.name = 'audio';
            audioInput.id = 'audio-input';
            audioInput.className = 'd-none';
            msgForm.appendChild(audioInput);
        }
        audioInput.files = dt.files;

        let durationInput = document.getElementById('audio-duration-input');
        if (!durationInput) {
            durationInput = document.createElement('input');
            durationInput.type = 'hidden';
            durationInput.name = 'audio_duration';
            durationInput.id = 'audio-duration-input';
            msgForm.appendChild(durationInput);
        }
        durationInput.value = duration;

        msgForm.requestSubmit();
    }

    if (micBtn) {
        micBtn.addEventListener('mousedown', startRecording);
        micBtn.addEventListener('touchstart', startRecording, { passive: true });
        document.addEventListener('mousemove', moveRecording);
        document.addEventListener('touchmove', moveRecording, { passive: true });
        document.addEventListener('mouseup', endRecording);
        document.addEventListener('touchend', endRecording);
        micBtn.addEventListener('contextmenu', e => e.preventDefault());
    }
</script>
@endpush
