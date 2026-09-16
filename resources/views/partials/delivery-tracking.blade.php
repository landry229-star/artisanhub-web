{{-- Suivi de livraison en direct — inclus depuis client/orders/show et artisan/orders/show --}}
@if($order->needs_delivery && $order->delivery)
    @php $delivery = $order->delivery; @endphp
    <div class="mb-4 content-card">
        <h5 class="mb-3 fw-700">
            <i class="bi bi-truck me-2 text-clay"></i>Suivi de la livraison
        </h5>

        <div class="mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <span class="badge-status" style="background:{{ $delivery->statusColor() }};color:{{ $delivery->statusTextColor() }}">
                {{ $delivery->statusLabel() }}
            </span>
            @if($delivery->livreur)
                <div class="text-end" style="font-size:.82rem">
                    <div class="fw-600">{{ $delivery->livreur->name }}</div>
                    @if($delivery->livreur->phone)
                        <a href="tel:{{ $delivery->livreur->phone }}" class="text-muted">📞 {{ $delivery->livreur->phone }}</a>
                    @endif
                </div>
            @endif
        </div>

        {{-- Étapes --}}
        <div class="mb-3 gap-1 d-flex" style="font-size:.72rem">
            @php
                $steps = ['acceptee' => 'Accepté', 'en_route' => 'En route', 'recuperee' => 'Récupéré', 'livree' => 'Livré'];
                $order_idx = array_search($delivery->status, array_keys($steps));
            @endphp
            @foreach($steps as $key => $label)
                <div class="flex-fill text-center py-1 rounded"
                     style="background:{{ array_search($key, array_keys($steps)) <= $order_idx ? '#C4622D' : '#F5EFE6' }};
                            color:{{ array_search($key, array_keys($steps)) <= $order_idx ? '#fff' : '#9A8070' }}">
                    {{ $label }}
                </div>
            @endforeach
        </div>

        {{-- Carte --}}
        <div id="tracking-map" style="height:260px;border-radius:10px;overflow:hidden;border:1px solid #ECD8C6"></div>
        <div id="tracking-updated" class="mt-1 text-muted" style="font-size:.72rem"></div>

        @if($delivery->livreur)
        <a href="{{ route('messages.thread', [$order, $delivery->livreur]) }}" class="mt-3 btn btn-outline-clay btn-sm w-100">
            <i class="bi bi-chat-dots me-1"></i>Communiquer avec le livreur
        </a>
        @endif
    </div>

    @push('styles')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
    @endpush

    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
        <script>
        (function () {
            const trackUrl = @json(route('deliveries.track', $order));
            const mapEl = document.getElementById('tracking-map');
            if (!mapEl || typeof L === 'undefined') return;

            const map = L.map('tracking-map').setView([9.3077, 2.3158], 7); // centre Bénin par défaut
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            let livreurMarker = null, pickupMarker = null, deliveryMarker = null;
            const bounds = [];

            function refresh() {
                fetch(trackUrl, { headers: { 'Accept': 'application/json' } })
                    .then(r => r.json())
                    .then(data => {
                        document.getElementById('tracking-updated').textContent =
                            data.updated_at ? ('Position mise à jour ' + data.updated_at) : '';

                        if (data.pickup_lat && data.pickup_lng && !pickupMarker) {
                            pickupMarker = L.marker([data.pickup_lat, data.pickup_lng])
                                .addTo(map).bindPopup('Retrait — artisan');
                            bounds.push([data.pickup_lat, data.pickup_lng]);
                        }
                        if (data.delivery_lat && data.delivery_lng && !deliveryMarker) {
                            deliveryMarker = L.marker([data.delivery_lat, data.delivery_lng])
                                .addTo(map).bindPopup('Livraison — client');
                            bounds.push([data.delivery_lat, data.delivery_lng]);
                        }
                        if (data.livreur_lat && data.livreur_lng) {
                            const pos = [data.livreur_lat, data.livreur_lng];
                            if (!livreurMarker) {
                                livreurMarker = L.marker(pos, {
                                    icon: L.divIcon({ className: '', html: '🚴', iconSize: [24, 24] })
                                }).addTo(map).bindPopup('Livreur');
                            } else {
                                livreurMarker.setLatLng(pos);
                            }
                            bounds.push(pos);
                        }
                        if (bounds.length) map.fitBounds(bounds, { padding: [30, 30], maxZoom: 15 });

                        if (!data.trackable && refreshTimer) clearInterval(refreshTimer);
                    })
                    .catch(() => {});
            }

            refresh();
            const refreshTimer = setInterval(refresh, 12000);
        })();
        </script>
    @endpush
@endif
