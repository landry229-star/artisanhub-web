/**
 * ArtisanHub — Système de notifications en temps réel
 * Polling AJAX toutes les 30 secondes sur /notifications/api
 */

const AHNotif = {

    // ── Config ─────────────────────────────────────────────────────────────
    pollInterval: 30000, // 30 secondes
    timer:        null,
    lastCount:    0,

    // ── Initialisation ─────────────────────────────────────────────────────
    init() {
        this.bell     = document.getElementById('notif-bell');
        this.badge    = document.getElementById('notif-badge');
        this.dropdown = document.getElementById('notif-dropdown');
        this.list     = document.getElementById('notif-list');
        this.markAllBtn = document.getElementById('notif-mark-all');

        if (!this.bell) return; // Pas connecté ou cloche absente

        // Premier chargement immédiat
        this.fetch();

        // Polling toutes les 30s
        this.timer = setInterval(() => this.fetch(), this.pollInterval);

        // Clic sur "Tout marquer lu"
        if (this.markAllBtn) {
            this.markAllBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.markAllRead();
            });
        }

        // Clic sur la cloche → fetch immédiat
        this.bell.addEventListener('click', () => this.fetch());
    },

    // ── Fetch depuis l'API ──────────────────────────────────────────────────
    async fetch() {
        try {
            const res = await fetch('/notifications/api', {
                headers: {
                    'Accept':           'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                }
            });

            if (!res.ok) return;
            const data = await res.json();

            this.updateBadge(data.unread_count);
            this.renderList(data.notifications);

            // Son si nouvelles notifications
            if (data.unread_count > this.lastCount && this.lastCount > 0) {
                this.playSound();
            }
            this.lastCount = data.unread_count;

        } catch (e) {
            // Silencieux — ne pas bloquer l'UI si le serveur est lent
        }
    },

    // ── Badge (chiffre rouge sur la cloche) ────────────────────────────────
    updateBadge(count) {
        if (!this.badge) return;

        if (count > 0) {
            this.badge.textContent = count > 99 ? '99+' : count;
            this.badge.style.display = 'flex';
            // Animation pulse si changement
            this.badge.classList.remove('ah-pulse');
            void this.badge.offsetWidth; // reflow
            this.badge.classList.add('ah-pulse');
        } else {
            this.badge.style.display = 'none';
        }

        // Titre du document
        const base = document.title.replace(/^\(\d+\+?\) /, '');
        document.title = count > 0 ? `(${count}) ${base}` : base;
    },

    // ── Rendu de la liste déroulante ────────────────────────────────────────
    renderList(notifications) {
        if (!this.list) return;

        if (notifications.length === 0) {
            this.list.innerHTML = `
                <div class="text-center py-4 text-muted" style="font-size:.85rem">
                    <i class="bi bi-bell-slash d-block mb-2" style="font-size:1.5rem;opacity:.4"></i>
                    Aucune notification
                </div>`;
            return;
        }

        this.list.innerHTML = notifications.map(n => `
            <a href="${n.data.url || '#'}"
               class="notif-item d-flex gap-3 align-items-start px-3 py-2 text-decoration-none
                      ${n.read ? 'notif-read' : 'notif-unread'}"
               data-id="${n.id}"
               onclick="AHNotif.markRead('${n.id}', this)">
                <div class="notif-icon flex-shrink-0"
                     style="width:36px;height:36px;border-radius:50%;
                            background:${n.read ? '#F5EFE6' : '#C4622D'};
                            display:flex;align-items:center;justify-content:center;
                            font-size:1rem">
                    ${n.data.icon || '🔔'}
                </div>
                <div class="flex-grow-1 min-width-0">
                    <div class="notif-title fw-600"
                         style="font-size:.85rem;color:${n.read ? '#9A8070' : '#2C1A0E'};
                                white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                        ${this.escape(n.data.title || '')}
                    </div>
                    <div class="notif-msg text-muted"
                         style="font-size:.78rem;line-height:1.4;
                                display:-webkit-box;-webkit-line-clamp:2;
                                -webkit-box-orient:vertical;overflow:hidden">
                        ${this.escape(n.data.message || '')}
                    </div>
                    <div class="notif-time text-muted" style="font-size:.72rem;margin-top:2px">
                        ${n.time}
                    </div>
                </div>
                ${!n.read ? '<div class="flex-shrink-0" style="width:8px;height:8px;border-radius:50%;background:#C4622D;margin-top:6px"></div>' : ''}
            </a>
        `).join('<hr style="margin:0;border-color:#F5EFE6">');
    },

    // ── Marquer une notification lue ────────────────────────────────────────
    async markRead(id, element) {
        try {
            await fetch(`/notifications/${id}/lire`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    'Accept':           'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });
            // Mettre à jour visuellement
            if (element) {
                element.classList.remove('notif-unread');
                element.classList.add('notif-read');
                const dot = element.querySelector('[style*="background:#C4622D"]');
                if (dot) dot.remove();
                const icon = element.querySelector('.notif-icon');
                if (icon) icon.style.background = '#F5EFE6';
            }
            // Décrémenter le badge
            const current = parseInt(this.badge?.textContent) || 0;
            if (current > 0) this.updateBadge(current - 1);
        } catch(e) {}
    },

    // ── Tout marquer lu ─────────────────────────────────────────────────────
    async markAllRead() {
        try {
            await fetch('/notifications/lire-tout', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    'Accept':           'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });
            this.updateBadge(0);
            // Recharger la liste
            this.fetch();
        } catch(e) {}
    },

    // ── Son de notification ─────────────────────────────────────────────────
    playSound() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.frequency.setValueAtTime(880, ctx.currentTime);
            osc.frequency.setValueAtTime(1100, ctx.currentTime + 0.1);
            gain.gain.setValueAtTime(0.1, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.3);
            osc.start(ctx.currentTime);
            osc.stop(ctx.currentTime + 0.3);
        } catch(e) {}
    },

    // ── Escape HTML ─────────────────────────────────────────────────────────
    escape(str) {
        const d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    },
};

// Démarrer quand le DOM est prêt
document.addEventListener('DOMContentLoaded', () => AHNotif.init());
