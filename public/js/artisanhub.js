/**
 * ArtisanHub — Validation JS globale
 * Gère tous les formulaires de la plateforme.
 */

'use strict';

/* ─── Utilitaires ────────────────────────────────────────────────────────── */

function showError(input, msg) {
    input.classList.add('is-invalid');
    let fb = input.parentElement.querySelector('.invalid-feedback');
    if (!fb) {
        fb = document.createElement('div');
        fb.className = 'invalid-feedback';
        input.parentElement.appendChild(fb);
    }
    fb.textContent = msg;
}

function clearError(input) {
    input.classList.remove('is-invalid');
    input.classList.add('is-valid');
    const fb = input.parentElement.querySelector('.invalid-feedback');
    if (fb) fb.textContent = '';
}

function validateField(input) {
    const val = input.value.trim();
    if (input.hasAttribute('required') && !val) {
        showError(input, 'Ce champ est obligatoire.');
        return false;
    }
    if (input.type === 'email' && val && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
        showError(input, 'Adresse email invalide.');
        return false;
    }
    if (input.type === 'number') {
        const min = parseFloat(input.min);
        if (!isNaN(min) && parseFloat(val) < min) {
            showError(input, `La valeur minimum est ${min.toLocaleString('fr-FR')}.`);
            return false;
        }
    }
    if (input.type === 'tel' && val && !/^[+\d\s\-()]{7,20}$/.test(val)) {
        showError(input, 'Numéro de téléphone invalide.');
        return false;
    }
    if (input.tagName === 'TEXTAREA' && input.hasAttribute('minlength')) {
        const min = parseInt(input.minLength);
        if (val.length < min) {
            showError(input, `Minimum ${min} caractères (${val.length} actuellement).`);
            return false;
        }
    }
    clearError(input);
    return true;
}

function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;
    let valid = true;
    form.querySelectorAll('input, textarea, select').forEach(input => {
        if (input.type === 'hidden' || input.type === 'file' || input.disabled) return;
        if (!validateField(input)) valid = false;
    });
    return valid;
}

function lockSubmit(btn, loadingText = 'Envoi en cours...') {
    btn.disabled = true;
    btn.dataset.original = btn.innerHTML;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>${loadingText}`;
}

function unlockSubmit(btn) {
    btn.disabled = false;
    if (btn.dataset.original) btn.innerHTML = btn.dataset.original;
}

/* ─── Validation en temps réel sur blur ──────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('input, textarea, select').forEach(input => {
        input.addEventListener('blur', () => {
            if (input.closest('form')) validateField(input);
        });
        // Compteur de caractères pour les textareas avec maxlength
        if (input.tagName === 'TEXTAREA' && input.maxLength > 0) {
            const counter = document.createElement('small');
            counter.className = 'text-muted d-block text-end';
            counter.style.fontSize = '.75rem';
            input.parentElement.appendChild(counter);
            const update = () => {
                const left = input.maxLength - input.value.length;
                counter.textContent = `${input.value.length}/${input.maxLength}`;
                counter.style.color = left < 30 ? '#dc3545' : '#6c757d';
            };
            input.addEventListener('input', update);
            update();
        }
    });
});

/* ─── Toggle mot de passe ────────────────────────────────────────────────── */
window.togglePwd = function(inputId = 'pwd', iconId = 'pwd-icon') {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    if (!input) return;
    if (input.type === 'password') {
        input.type = 'text';
        if (icon) icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        if (icon) icon.className = 'bi bi-eye';
    }
};

/* ─── Prévisualisation avatar / image ────────────────────────────────────── */
window.previewAvatar = function(input, previewId = 'avatar-preview') {
    const preview = document.getElementById(previewId);
    if (!preview || !input.files?.[0]) return;
    const reader = new FileReader();
    reader.onload = e => preview.src = e.target.result;
    reader.readAsDataURL(input.files[0]);
};

window.previewServiceImage = function(input) {
    const wrapper = document.getElementById('service-preview-wrapper');
    const img     = document.getElementById('service-preview-img');
    if (!wrapper || !img || !input.files?.[0]) return;
    const reader = new FileReader();
    reader.onload = e => { img.src = e.target.result; wrapper.classList.remove('d-none'); };
    reader.readAsDataURL(input.files[0]);
};

/* ─── Toggle livraison (formulaire commande client) ──────────────────────── */
window.toggleDelivery = function(checked) {
    const block  = document.getElementById('delivery-city-block');
    const select = document.getElementById('delivery_city');
    if (!block) return;
    block.style.display = checked ? 'block' : 'none';
    if (select) select.required = checked;
};

/* ─── Sélection rôle (page register) ────────────────────────────────────── */
window.selectRole = function(role, cardEl) {
    document.getElementById('role-input').value = role;
    document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
    cardEl.classList.add('selected');
    const submitBtn = document.getElementById('submit-btn');
    if (submitBtn) submitBtn.disabled = false;
    document.querySelectorAll('.artisan-only').forEach(f => {
        f.style.display = role === 'artisan' ? 'block' : 'none';
    });
};

/* ─── Aperçu prix net (catalogue services artisan) ──────────────────────── */
window.updatePricePreview = function(price) {
    const preview     = document.getElementById('price-preview');
    const priceDisplay = document.getElementById('price-display');
    const netDisplay   = document.getElementById('net-display');
    if (!preview) return;
    const p = parseInt(price);
    if (p >= 500) {
        priceDisplay.textContent = p.toLocaleString('fr-FR') + ' XOF';
        netDisplay.textContent   = Math.round(p * 0.95).toLocaleString('fr-FR') + ' XOF';
        preview.classList.remove('d-none');
    } else {
        preview.classList.add('d-none');
    }
};

/* ════════════════════════════════════════════════════════════════════════════
   VALIDATION PAR FORMULAIRE
   ════════════════════════════════════════════════════════════════════════════ */

document.addEventListener('DOMContentLoaded', () => {

    /* ── Connexion ─────────────────────────────────────────────────────────── */
    const formLogin = document.getElementById('form-login');
    if (formLogin) {
        formLogin.addEventListener('submit', function(e) {
            const email = this.querySelector('[name=email]');
            const pwd   = this.querySelector('[name=password]');
            let ok = true;
            if (!validateField(email)) ok = false;
            if (!pwd.value) { showError(pwd, 'Le mot de passe est obligatoire.'); ok = false; }
            else clearError(pwd);
            if (!ok) { e.preventDefault(); return; }
            lockSubmit(this.querySelector('[type=submit]'), 'Connexion...');
        });
    }

    /* ── Inscription ───────────────────────────────────────────────────────── */
    const formRegister = document.getElementById('register-form');
    if (formRegister) {
        formRegister.addEventListener('submit', function(e) {
            const role = document.getElementById('role-input')?.value;
            const pwd  = document.getElementById('pwd');
            const pwd2 = document.getElementById('pwd2');
            let errors = [];
            if (!role)            errors.push('Veuillez choisir votre rôle.');
            if (pwd?.value.length < 8) errors.push('Le mot de passe doit contenir au moins 8 caractères.');
            if (pwd?.value !== pwd2?.value) errors.push('Les mots de passe ne correspondent pas.');
            if (!validateForm('register-form')) errors.push('Veuillez corriger les champs en rouge.');
            if (errors.length) { e.preventDefault(); alert(errors.join('\n')); return; }
            lockSubmit(this.querySelector('[type=submit]'), 'Création...');
        });
    }

    /* ── Profil artisan ────────────────────────────────────────────────────── */
    const formProfile = document.getElementById('form-profile');
    if (formProfile) {
        formProfile.addEventListener('submit', function(e) {
            if (!validateForm('form-profile')) {
                e.preventDefault();
                this.querySelector('.is-invalid')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
            lockSubmit(this.querySelector('[type=submit]'), 'Enregistrement...');
        });
    }

    /* ── Profil client ─────────────────────────────────────────────────────── */
    const formClientProfile = document.getElementById('form-client-profile');
    if (formClientProfile) {
        formClientProfile.addEventListener('submit', function(e) {
            if (!validateForm('form-client-profile')) {
                e.preventDefault();
                return;
            }
            lockSubmit(this.querySelector('[type=submit]'), 'Enregistrement...');
        });
    }

    /* ── Nouvelle commande client ──────────────────────────────────────────── */
    const formOrder = document.getElementById('order-form');
    if (formOrder) {
        formOrder.addEventListener('submit', function(e) {
            const title  = this.querySelector('[name=title]');
            const desc   = this.querySelector('[name=description]');
            const budget = this.querySelector('[name=budget]');
            const needsDel = this.querySelector('[name=needs_delivery]');
            const delCity  = this.querySelector('[name=delivery_city]');
            let ok = true;
            if (!validateField(title))  ok = false;
            if (!validateField(desc))   ok = false;
            if (budget && !validateField(budget)) ok = false;
            if (needsDel?.checked && delCity && !delCity.value) {
                showError(delCity, 'Veuillez choisir votre ville de livraison.');
                ok = false;
            }
            if (!ok) { e.preventDefault(); this.querySelector('.is-invalid')?.scrollIntoView({ behavior:'smooth', block:'center' }); return; }
            lockSubmit(this.querySelector('[type=submit]'), 'Envoi de la commande...');
        });
    }

    /* ── Catalogue services artisan ────────────────────────────────────────── */
    const formService = document.getElementById('service-form');
    if (formService) {
        // Compteur titre
        const titleInput = formService.querySelector('[name=title]');
        if (titleInput) {
            titleInput.addEventListener('input', function() {
                const counter = document.getElementById('title-count');
                if (counter) counter.textContent = `${this.value.length}/${this.maxLength}`;
            });
        }
        // Prix → aperçu net
        const priceInput = formService.querySelector('[name=price]');
        if (priceInput) {
            priceInput.addEventListener('input', () => updatePricePreview(priceInput.value));
        }
        // Validation soumission
        formService.addEventListener('submit', function(e) {
            if (!validateForm('service-form')) {
                e.preventDefault();
                return;
            }
            lockSubmit(this.querySelector('[type=submit]'), 'Publication...');
        });
    }

    /* ── Messagerie ────────────────────────────────────────────────────────── */
    const formMessage = document.getElementById('form-message');
    if (formMessage) {
        formMessage.addEventListener('submit', function(e) {
            const body = this.querySelector('[name=body]');
            if (!body?.value.trim()) {
                e.preventDefault();
                showError(body, 'Le message ne peut pas être vide.');
                return;
            }
            clearError(body);
            const btn = this.querySelector('[type=submit]');
            if (btn) lockSubmit(btn, 'Envoi...');
        });
    }

    /* ── Portfolio artisan ─────────────────────────────────────────────────── */
    const formPortfolio = document.getElementById('form-portfolio');
    if (formPortfolio) {
        formPortfolio.addEventListener('submit', function(e) {
            const img = this.querySelector('[name=image]');
            if (img && !img.files?.length) {
                showError(img, 'Veuillez sélectionner une image.');
                e.preventDefault();
                return;
            }
            lockSubmit(this.querySelector('[type=submit]'), 'Upload en cours...');
        });
    }

    /* ── Arbitrage admin ───────────────────────────────────────────────────── */
    const formArbitrate = document.getElementById('form-arbitrate');
    if (formArbitrate) {
        formArbitrate.addEventListener('submit', function(e) {
            const note = this.querySelector('[name=admin_note]');
            if (!note?.value.trim()) {
                showError(note, 'La note d\'arbitrage est obligatoire.');
                e.preventDefault();
                return;
            }
            if (!confirm('Confirmer l\'arbitrage de cette commande ?')) { e.preventDefault(); return; }
            lockSubmit(this.querySelector('[type=submit]'), 'Arbitrage en cours...');
        });
    }

    /* ── Anti double-clic global sur tous les boutons de formulaires ───────── */
    document.querySelectorAll('form').forEach(form => {
        // Ignorer les forms déjà gérés ci-dessus
        const managedIds = ['form-login','register-form','form-profile','form-client-profile',
                            'order-form','service-form','form-message','form-portfolio','form-arbitrate'];
        if (managedIds.includes(form.id)) return;

        form.addEventListener('submit', function() {
            const btn = this.querySelector('[type=submit]');
            if (btn && !btn.disabled) {
                setTimeout(() => lockSubmit(btn), 0);
            }
        });
    });

});
