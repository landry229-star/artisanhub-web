/**
 * ArtisanHub — Validation JS globale v1.0
 * Couvre : register, login, commande, profil artisan, portfolio, services, messagerie, avis
 */

// ═══════════════════════════════════════════════════════════════════════════
// UTILITAIRES
// ═══════════════════════════════════════════════════════════════════════════

const AH = {

    showError(field, message) {
        field.classList.add('is-invalid');
        field.classList.remove('is-valid');
        let fb = field.parentElement.querySelector('.ah-feedback');
        if (!fb) {
            fb = document.createElement('div');
            fb.className = 'ah-feedback invalid-feedback';
            field.parentElement.appendChild(fb);
        }
        fb.textContent = message;
        fb.style.display = 'block';
    },

    showValid(field) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
        const fb = field.parentElement.querySelector('.ah-feedback');
        if (fb) fb.style.display = 'none';
    },

    clearField(field) {
        field.classList.remove('is-invalid', 'is-valid');
        const fb = field.parentElement.querySelector('.ah-feedback');
        if (fb) fb.style.display = 'none';
    },

    validate(field, rules) {
        const val = field.value.trim();
        if (rules.required && val === '') {
            this.showError(field, rules.required);
            return false;
        }
        if (val === '' && !rules.required) { return true; }
        if (rules.min && val.length < rules.min.length) {
            this.showError(field, rules.min.message);
            return false;
        }
        if (rules.max && val.length > rules.max.length) {
            this.showError(field, rules.max.message);
            return false;
        }
        if (rules.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
            this.showError(field, rules.email);
            return false;
        }
        if (rules.minValue && Number(val) < rules.minValue.val) {
            this.showError(field, rules.minValue.message);
            return false;
        }
        if (rules.match) {
            const other = document.getElementById(rules.match.field) ||
                          document.querySelector(`[name="${rules.match.field}"]`);
            if (other && val !== other.value.trim()) {
                this.showError(field, rules.match.message);
                return false;
            }
        }
        this.showValid(field);
        return true;
    },

    charCounter(field, max) {
        const wrapper = field.parentElement;
        let counter = wrapper.querySelector('.ah-counter');
        if (!counter) {
            counter = document.createElement('small');
            counter.className = 'ah-counter text-muted d-block mt-1';
            wrapper.appendChild(counter);
        }
        const update = () => {
            const len = field.value.length;
            counter.textContent = `${len} / ${max} caractères`;
            counter.style.color = (max - len) < 20 ? '#C4622D' : '';
        };
        field.addEventListener('input', update);
        update();
    },

    setLoading(btn, loading) {
        if (loading) {
            btn.dataset.originalText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Envoi en cours...';
            btn.disabled = true;
        } else {
            btn.innerHTML = btn.dataset.originalText || btn.innerHTML;
            btn.disabled = false;
        }
    },

    formatXOF(n) {
        return new Intl.NumberFormat('fr-FR').format(n) + ' XOF';
    },

    liveValidate(field, rules) {
        field.addEventListener('blur', () => this.validate(field, rules));
        field.addEventListener('input', () => {
            if (field.classList.contains('is-invalid')) this.validate(field, rules);
        });
    },
};

// ═══════════════════════════════════════════════════════════════════════════
// INITIALISATION AU CHARGEMENT
// ═══════════════════════════════════════════════════════════════════════════

document.addEventListener('DOMContentLoaded', () => {

    // ── INSCRIPTION ────────────────────────────────────────────────────────
    const registerForm = document.getElementById('form-register');
    if (registerForm) {
        const name     = registerForm.querySelector('[name="name"]');
        const email    = registerForm.querySelector('[name="email"]');
        const city     = registerForm.querySelector('[name="city"]');
        const pwd      = registerForm.querySelector('[name="password"]');
        const pwdConf  = registerForm.querySelector('[name="password_confirmation"]');
        const specialty= registerForm.querySelector('[name="specialty"]');
        const category = registerForm.querySelector('[name="category"]');
        const roleInput= document.getElementById('role-input');

        if (name)    AH.liveValidate(name,    { required: 'Le nom est obligatoire.', min: { length: 3, message: 'Min. 3 caractères.' } });
        if (email)   AH.liveValidate(email,   { required: 'L\'email est obligatoire.', email: 'Email invalide.' });
        if (pwd)     AH.liveValidate(pwd,     { required: 'Mot de passe obligatoire.', min: { length: 8, message: 'Min. 8 caractères.' } });
        if (pwdConf) AH.liveValidate(pwdConf, { required: 'Confirmez le mot de passe.', match: { field: 'password', message: 'Les mots de passe ne correspondent pas.' } });

        registerForm.addEventListener('submit', (e) => {
            let valid = true;
            if (name     && !AH.validate(name,     { required: 'Le nom est obligatoire.', min: { length: 3, message: 'Min. 3 caractères.' } })) valid = false;
            if (email    && !AH.validate(email,    { required: 'L\'email est obligatoire.', email: 'Email invalide.' })) valid = false;
            if (city     && city.value === '')  { AH.showError(city, 'La ville est obligatoire.'); valid = false; }
            if (pwd      && !AH.validate(pwd,      { required: 'Mot de passe obligatoire.', min: { length: 8, message: 'Min. 8 caractères.' } })) valid = false;
            if (pwdConf  && !AH.validate(pwdConf,  { required: 'Confirmez le mot de passe.', match: { field: 'password', message: 'Les mots de passe ne correspondent pas.' } })) valid = false;

            // Rôle obligatoire
            if (roleInput && !roleInput.value) {
                document.querySelectorAll('.role-card').forEach(c => c.style.border = '2px solid #dc3545');
                const alert = document.createElement('div');
                alert.className = 'alert alert-danger mt-2 mb-0';
                alert.textContent = 'Veuillez choisir votre rôle (Artisan, Client ou Livreur).';
                const sel = document.getElementById('role-selector');
                if (sel && !sel.querySelector('.alert')) sel.appendChild(alert);
                valid = false;
            }

            // Champs artisan
            if (roleInput && roleInput.value === 'artisan') {
                if (specialty && !AH.validate(specialty, { required: 'La spécialité est obligatoire.' })) valid = false;
                if (category  && category.value === '') { AH.showError(category, 'La catégorie est obligatoire.'); valid = false; }
            }

            if (!valid) { e.preventDefault(); return; }
            const btn = registerForm.querySelector('[type="submit"]');
            if (btn) AH.setLoading(btn, true);
        });
    }

    // ── CONNEXION ──────────────────────────────────────────────────────────
    const loginForm = document.getElementById('form-login');
    if (loginForm) {
        const email = loginForm.querySelector('[name="email"]');
        const pwd   = loginForm.querySelector('[name="password"]');

        if (email) AH.liveValidate(email, { required: 'Email obligatoire.', email: 'Email invalide.' });
        if (pwd)   AH.liveValidate(pwd,   { required: 'Mot de passe obligatoire.' });

        loginForm.addEventListener('submit', (e) => {
            let valid = true;
            if (email && !AH.validate(email, { required: 'Email obligatoire.', email: 'Email invalide.' })) valid = false;
            if (pwd   && !AH.validate(pwd,   { required: 'Mot de passe obligatoire.' })) valid = false;
            if (!valid) { e.preventDefault(); return; }
            const btn = loginForm.querySelector('[type="submit"]');
            if (btn) AH.setLoading(btn, true);
        });
    }

    // ── FORMULAIRE COMMANDE ─────────────────────────────────────────────────
    const orderForm = document.getElementById('form-create-order');
    if (orderForm) {
        const title       = orderForm.querySelector('[name="title"]');
        const description = orderForm.querySelector('[name="description"]');
        const budget      = orderForm.querySelector('[name="budget"]');
        const delivCheck  = orderForm.querySelector('[name="needs_delivery"]');
        const delivCity   = orderForm.querySelector('[name="delivery_city"]');

        if (title)       AH.charCounter(title, 150);
        if (description) AH.charCounter(description, 2000);
        if (title)       AH.liveValidate(title, { required: 'Le titre est obligatoire.', min: { length: 5, message: 'Min. 5 caractères.' } });
        if (description) AH.liveValidate(description, { required: 'La description est obligatoire.', min: { length: 20, message: 'Décrivez davantage votre commande (min. 20 car.).' } });

        // Aperçu budget + commission
        if (budget) {
            budget.addEventListener('input', () => {
                const val = parseInt(budget.value) || 0;
                let preview = document.getElementById('budget-preview');
                if (!preview) {
                    preview = document.createElement('div');
                    preview.id = 'budget-preview';
                    preview.className = 'p-2 rounded mt-2';
                    preview.style.background = '#F5EFE6';
                    preview.style.fontSize = '.82rem';
                    budget.parentElement.appendChild(preview);
                }
                if (val >= 1000) {
                    const comm = Math.round(val * 0.10);
                    preview.innerHTML = `
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Commission ArtisanHub (10%)</span>
                            <strong style="color:#C4622D">${AH.formatXOF(comm)}</strong>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <span class="text-muted">Net artisan</span>
                            <strong style="color:#155724">${AH.formatXOF(val - comm)}</strong>
                        </div>`;
                    preview.style.display = 'block';
                } else {
                    preview.style.display = 'none';
                }
            });
        }

        orderForm.addEventListener('submit', (e) => {
            let valid = true;
            if (title       && !AH.validate(title,       { required: 'Le titre est obligatoire.', min: { length: 5, message: 'Min. 5 caractères.' } })) valid = false;
            if (description && !AH.validate(description, { required: 'La description est obligatoire.', min: { length: 20, message: 'Min. 20 caractères.' } })) valid = false;
            if (budget && budget.value && Number(budget.value) < 1000) {
                AH.showError(budget, 'Budget minimum : 1 000 XOF.');
                valid = false;
            }
            if (delivCheck && delivCheck.checked && delivCity) {
                if (!AH.validate(delivCity, { required: 'Choisissez votre ville de livraison.' })) valid = false;
            }
            if (!valid) { e.preventDefault(); return; }
            const btn = orderForm.querySelector('[type="submit"]');
            if (btn) AH.setLoading(btn, true);
        });
    }

    // ── PROFIL ARTISAN ─────────────────────────────────────────────────────
    const profileForm = document.getElementById('form-profile');
    if (profileForm) {
        const bio        = profileForm.querySelector('[name="bio"]');
        const name       = profileForm.querySelector('[name="name"]');
        const specialty  = profileForm.querySelector('[name="specialty"]');
        const hourlyRate = profileForm.querySelector('[name="hourly_rate"]');

        if (bio) AH.charCounter(bio, 500);
        if (name)      AH.liveValidate(name,     { required: 'Le nom est obligatoire.' });
        if (specialty) AH.liveValidate(specialty, { required: 'La spécialité est obligatoire.' });

        profileForm.addEventListener('submit', (e) => {
            let valid = true;
            if (name      && !AH.validate(name,      { required: 'Le nom est obligatoire.' })) valid = false;
            if (specialty && !AH.validate(specialty,  { required: 'La spécialité est obligatoire.' })) valid = false;
            if (hourlyRate && hourlyRate.value && Number(hourlyRate.value) < 0) {
                AH.showError(hourlyRate, 'Le tarif ne peut pas être négatif.');
                valid = false;
            }
            if (!valid) { e.preventDefault(); return; }
            const btn = profileForm.querySelector('[type="submit"]');
            if (btn) AH.setLoading(btn, true);
        });
    }

    // ── PORTFOLIO ──────────────────────────────────────────────────────────
    const portfolioForm = document.getElementById('form-portfolio');
    if (portfolioForm) {
        const titleF = portfolioForm.querySelector('[name="title"]');
        const imageF = portfolioForm.querySelector('[name="image"]');
        const descF  = portfolioForm.querySelector('[name="description"]');

        if (titleF) { AH.charCounter(titleF, 100); AH.liveValidate(titleF, { required: 'Le titre est obligatoire.' }); }
        if (descF)  AH.charCounter(descF, 500);

        if (imageF) {
            imageF.addEventListener('change', () => {
                const file = imageF.files[0];
                if (!file) return;
                if (file.size > 3 * 1024 * 1024) { AH.showError(imageF, 'Image trop lourde (max 3 Mo).'); imageF.value = ''; return; }
                if (!['image/jpeg','image/png','image/webp'].includes(file.type)) { AH.showError(imageF, 'Format non accepté (JPG, PNG, WebP).'); imageF.value = ''; return; }
                AH.showValid(imageF);
            });
        }

        portfolioForm.addEventListener('submit', (e) => {
            let valid = true;
            if (titleF && !AH.validate(titleF, { required: 'Le titre est obligatoire.' })) valid = false;
            if (imageF && !imageF.files[0]) { AH.showError(imageF, 'Veuillez choisir une image.'); valid = false; }
            if (!valid) { e.preventDefault(); return; }
            const btn = portfolioForm.querySelector('[type="submit"]');
            if (btn) AH.setLoading(btn, true);
        });
    }

    // ── SERVICES ───────────────────────────────────────────────────────────
    const serviceForm = document.getElementById('form-service');
    if (serviceForm) {
        const titleF = serviceForm.querySelector('[name="title"]');
        const descF  = serviceForm.querySelector('[name="description"]');
        const priceF = serviceForm.querySelector('[name="price"]');
        const daysF  = serviceForm.querySelector('[name="delivery_days"]');

        if (titleF) { AH.charCounter(titleF, 100); AH.liveValidate(titleF, { required: 'Le titre est obligatoire.', min: { length: 5, message: 'Min. 5 caractères.' } }); }
        if (descF)  { AH.charCounter(descF, 500);  AH.liveValidate(descF,  { required: 'La description est obligatoire.', min: { length: 20, message: 'Min. 20 caractères.' } }); }
        if (priceF) AH.liveValidate(priceF, { required: 'Le prix est obligatoire.', minValue: { val: 500, message: 'Min. 500 XOF.' } });

        // Aperçu net artisan
        if (priceF) {
            priceF.addEventListener('input', () => {
                const val = parseInt(priceF.value) || 0;
                let preview = document.getElementById('service-price-preview');
                if (!preview) {
                    preview = document.createElement('small');
                    preview.id = 'service-price-preview';
                    preview.className = 'd-block mt-1';
                    preview.style.color = '#155724';
                    priceF.parentElement.appendChild(preview);
                }
                preview.textContent = val >= 500
                    ? `Vous recevez : ${AH.formatXOF(Math.round(val * 0.90))} (après 10% de commission)`
                    : '';
            });
        }

        serviceForm.addEventListener('submit', (e) => {
            let valid = true;
            if (titleF && !AH.validate(titleF, { required: 'Le titre est obligatoire.', min: { length: 5, message: 'Min. 5 car.' } })) valid = false;
            if (descF  && !AH.validate(descF,  { required: 'La description est obligatoire.', min: { length: 20, message: 'Min. 20 car.' } })) valid = false;
            if (priceF && !AH.validate(priceF, { required: 'Le prix est obligatoire.', minValue: { val: 500, message: 'Min. 500 XOF.' } })) valid = false;
            if (daysF && daysF.value && Number(daysF.value) < 1) { AH.showError(daysF, 'Min. 1 jour.'); valid = false; }
            if (!valid) { e.preventDefault(); return; }
            const btn = serviceForm.querySelector('[type="submit"]');
            if (btn) AH.setLoading(btn, true);
        });
    }

    // ── MESSAGERIE ─────────────────────────────────────────────────────────
    const msgForm = document.getElementById('msg-form') || document.getElementById('form-message');
    if (msgForm) {
        const bodyF = msgForm.querySelector('[name="body"]');
        const fileF = msgForm.querySelector('[name="attachment"]');

        if (bodyF) {
            bodyF.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    if (bodyF.value.trim() || fileF?.files[0]) msgForm.requestSubmit();
                }
            });
        }

        if (fileF) {
            fileF.addEventListener('change', () => {
                const file = fileF.files[0];
                if (file && file.size > 5 * 1024 * 1024) {
                    alert('Fichier trop volumineux (max 5 Mo).');
                    fileF.value = '';
                }
            });
        }

        msgForm.addEventListener('submit', (e) => {
            if (!bodyF?.value.trim() && !fileF?.files[0]) {
                if (bodyF) AH.showError(bodyF, 'Écrivez un message ou joignez un fichier.');
                e.preventDefault();
                return;
            }
            const btn = msgForm.querySelector('[type="submit"]');
            if (btn) btn.disabled = true;
        });
    }

    // ── AVIS ───────────────────────────────────────────────────────────────
    document.querySelectorAll('.form-review').forEach(form => {
        form.addEventListener('submit', (e) => {
            const rating = form.querySelector('[name="rating"]:checked');
            if (!rating) {
                e.preventDefault();
                let err = form.querySelector('.rating-error');
                if (!err) {
                    err = document.createElement('div');
                    err.className = 'rating-error text-danger mt-1';
                    err.style.fontSize = '.82rem';
                    err.textContent = 'Veuillez choisir une note.';
                    const starWrapper = form.querySelector('.d-flex');
                    if (starWrapper) starWrapper.after(err);
                }
                return;
            }
            const btn = form.querySelector('[type="submit"]');
            if (btn) AH.setLoading(btn, true);
        });
    });

    // ── CONFIRMATIONS DESTRUCTIVES ─────────────────────────────────────────
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', (e) => {
            if (!confirm(el.dataset.confirm)) e.preventDefault();
        });
    });

    // ── AUTO-DISMISS ALERTS (5 secondes) ──────────────────────────────────
    document.querySelectorAll('.alert-dismissible').forEach(alert => {
        setTimeout(() => {
            try { bootstrap.Alert.getOrCreateInstance(alert)?.close(); } catch(e) {}
        }, 5000);
    });

    // ── SCROLL CHAT AUTO ───────────────────────────────────────────────────
    const chatBox = document.getElementById('chat-box');
    if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;

});
