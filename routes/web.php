<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Artisan\DashboardController as ArtisanDashboard;
use App\Http\Controllers\Artisan\ProfileController as ArtisanProfile;
use App\Http\Controllers\Artisan\PortfolioController;
use App\Http\Controllers\Artisan\ServiceController;
use App\Http\Controllers\Artisan\OrderController as ArtisanOrder;
use App\Http\Controllers\Client\DashboardController as ClientDashboard;
use App\Http\Controllers\Client\SearchController;
use App\Http\Controllers\Client\OrderController as ClientOrder;
use App\Http\Controllers\Client\WalletController as ClientWallet;
use App\Http\Controllers\Client\SupportController as ClientSupport;
use App\Http\Controllers\Client\DeliveryChoiceController;
use App\Http\Controllers\ContactHistoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\SupportController as AdminSupport;
use App\Http\Controllers\Admin\UserController as AdminUser;
use App\Http\Controllers\Admin\OrderController as AdminOrder;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Livreur\DashboardController as LivreurDashboard;
use App\Http\Controllers\Livreur\MissionController;
use App\Http\Controllers\Livreur\WalletController as LivreurWallet;
use App\Http\Controllers\Admin\DeliveryController as AdminDelivery;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Artisan\WalletController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\Artisan\StatsController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\KycController;
use App\Http\Controllers\Livreur\ProfileController as LivreurProfile;
use App\Http\Controllers\Admin\ProfileController as AdminProfile;
use App\Http\Controllers\Livreur\LocationController as LivreurLocation;
use App\Http\Controllers\DeliveryTrackingController;
use App\Http\Controllers\SecureFileController;
// ── PUBLIC ────────────────────────────────────────────────────────────────────
Route::get('/', [\App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');
Route::get('/artisans', [SearchController::class, 'index'])->name('artisans.index');
Route::get('/categorie/{category}', [\App\Http\Controllers\Client\LocalSeoController::class, 'category'])->name('category.show');
Route::get('/categorie/{category}/ville/{citySlug}', [\App\Http\Controllers\Client\LocalSeoController::class, 'categoryCity'])->name('category.city.show');
Route::get('/ville/{citySlug}', [\App\Http\Controllers\Client\LocalSeoController::class, 'city'])->name('city.show');
Route::get('/artisans/{idSlug}', [SearchController::class, 'show'])
    ->where('idSlug', '[0-9]+.*')
    ->name('artisans.show');
// // Pages légales (ajouter aux routes publiques existantes)
 Route::get('/mentions-legales',      fn() => view('pages.mentions'))->name('mentions');
 Route::get('/politique-remboursement', fn() => view('pages.remboursement'))->name('remboursement');
 Route::get('/contact',               [SupportController::class, 'index'])->name('contact');
 Route::get('/support',               [SupportController::class, 'index'])->name('support');
 Route::post('/support',              [SupportController::class, 'send'])->name('support.send');
 Route::get('/comment-ca-marche', fn() => view('pages.how_it_works'))->name('how_it_works');
// ── AUTH ──────────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/inscription', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/inscription', [AuthController::class, 'register']);
    Route::get('/connexion',   [AuthController::class, 'showLogin'])->name('login');
    Route::post('/connexion',  [AuthController::class, 'login']);
    Route::get('/mot-de-passe/oublie',         [PasswordResetController::class, 'showForgotForm'])->name('password.forgot');
    // throttle : évite l'énumération d'emails / le spam d'envois
    Route::post('/mot-de-passe/envoyer',       [PasswordResetController::class, 'sendResetLink'])->middleware('throttle:3,1')->name('password.send-reset');
    Route::get('/mot-de-passe/reinitialiser',  [PasswordResetController::class, 'showResetForm'])->name('password.reset-form');
    Route::post('/mot-de-passe/enregistrer',   [PasswordResetController::class, 'resetPassword'])->name('password.reset');
});
Route::post('/deconnexion', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::delete('/compte', [AccountController::class, 'destroy'])
    ->middleware(['auth', 'verify-email'])
    ->name('account.destroy');


Route::middleware(['auth', 'verify-email'])->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/api',          [NotificationController::class, 'index'])->name('api');
    Route::patch('/{id}/lire',  [NotificationController::class, 'markRead'])->name('read');
    Route::post('/lire-tout',   [NotificationController::class, 'markAllRead'])->name('read-all');
});




// Vérification email
Route::middleware(['auth', 'verify-email'])->group(function () {
    Route::get('/email/verification',    [EmailVerificationController::class, 'notice'])->name('email.notice');
    Route::post('/email/renvoyer',       [EmailVerificationController::class, 'send'])->name('email.send-verification');
});
Route::get('/email/verifier',            [EmailVerificationController::class, 'verify'])->name('email.verify');
// ── ARTISAN ───────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'verify-email', 'role:artisan'])->prefix('artisan')->name('artisan.')->group(function () {

    Route::get('/dashboard', [ArtisanDashboard::class, 'index'])->name('dashboard');
 Route::get('/artisans', [SearchController::class, 'index'])
    ->middleware('persist-search')
    ->name('artisans.index');
    // Profil
    Route::get('/profil/modifier',        [ArtisanProfile::class, 'edit'])->name('profile.edit');
    Route::put('/profil',                 [ArtisanProfile::class, 'update'])->name('profile.update');
    Route::patch('/profil/disponibilite', [ArtisanProfile::class, 'toggleAvailability'])->name('profile.availability');
    Route::put('/profil/mot-de-passe',    [ArtisanProfile::class, 'updatePassword'])->name('profile.password');

    // Portfolio
    Route::get('/portfolio',           [PortfolioController::class, 'index'])->name('portfolio.index');
    Route::post('/portfolio',          [PortfolioController::class, 'store'])->name('portfolio.store');
    Route::delete('/portfolio/{item}', [PortfolioController::class, 'destroy'])->name('portfolio.destroy');

    // Services / Catalogue
    Route::get('/services',                      [ServiceController::class, 'index'])->name('services.index');
    Route::post('/services',                     [ServiceController::class, 'store'])->name('services.store');
    Route::get('/services/{service}/modifier',   [ServiceController::class, 'edit'])->name('services.edit');
    Route::patch('/services/{service}',          [ServiceController::class, 'update'])->name('services.update');
    Route::patch('/services/{service}/toggle',   [ServiceController::class, 'toggle'])->name('services.toggle');
    Route::delete('/services/{service}',         [ServiceController::class, 'destroy'])->name('services.destroy');
    Route::get('/solde', [WalletController::class, 'index'])->name('wallet.index');
    Route::get('/solde/releve.pdf', [WalletController::class, 'statement'])->name('wallet.statement');
    Route::get('/solde/paiement/{payment}/justificatif.pdf', [WalletController::class, 'receipt'])->name('wallet.receipt');
    // Commandes
    Route::get('/commandes',                      [ArtisanOrder::class, 'index'])->name('orders.index');
    Route::get('/commandes/{order}',              [ArtisanOrder::class, 'show'])->name('orders.show');
    Route::patch('/commandes/{order}/accepter',   [ArtisanOrder::class, 'accept'])->name('orders.accept');
    Route::patch('/commandes/{order}/demarrer',   [ArtisanOrder::class, 'start'])->name('orders.start');   // ← NOUVEAU
    Route::patch('/commandes/{order}/livrer',     [ArtisanOrder::class, 'markDelivered'])->name('orders.deliver');
    Route::patch('/commandes/{order}/annuler',    [ArtisanOrder::class, 'cancel'])->name('orders.cancel');
    Route::patch('/commandes/{order}/refuser',    [ArtisanOrder::class, 'reject'])->name('orders.reject');
});
// ── LIVREUR ───────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'verify-email', 'role:livreur'])->prefix('livreur')->name('livreur.')->group(function () {

    Route::get('/dashboard', [LivreurDashboard::class, 'index'])->name('dashboard');
    Route::patch('/disponibilite', [LivreurDashboard::class, 'toggleAvailability'])->name('availability');

    // Solde
    Route::get('/solde', [\App\Http\Controllers\Livreur\WalletController::class, 'index'])->name('wallet.index');
    Route::get('/solde/releve.pdf', [\App\Http\Controllers\Livreur\WalletController::class, 'statement'])->name('wallet.statement');

    // Profil
    Route::get('/profil',              [LivreurProfile::class, 'edit'])->name('profile.edit');
    Route::put('/profil',              [LivreurProfile::class, 'update'])->name('profile.update');
    Route::put('/profil/mot-de-passe', [LivreurProfile::class, 'updatePassword'])->name('profile.password');

    // Missions
    Route::get('/missions',                          [MissionController::class, 'index'])->name('missions.index');
    Route::get('/missions/{delivery}',               [MissionController::class, 'show'])->name('missions.show');
    Route::patch('/missions/{delivery}/accepter',    [MissionController::class, 'accept'])->name('missions.accept');
    Route::patch('/missions/{delivery}/refuser',     [MissionController::class, 'refuse'])->name('missions.refuse');
    Route::patch('/missions/{delivery}/en-route',    [MissionController::class, 'enRoute'])->name('missions.enroute');
    Route::patch('/missions/{delivery}/recuperer',   [MissionController::class, 'pickup'])->name('missions.pickup');
    Route::patch('/missions/{delivery}/livrer',      [MissionController::class, 'deliver'])->name('missions.deliver');
    Route::patch('/missions/{delivery}/probleme',    [MissionController::class, 'fail'])->name('missions.fail');

    // Géolocalisation en direct pendant une mission active
    Route::post('/missions/{delivery}/position', [LivreurLocation::class, 'update'])->middleware('throttle:30,1')->name('missions.location');
});

// ── CLIENT ────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'verify-email', 'role:client'])->prefix('client')->name('client.')->group(function () {
    Route::get('/dashboard', [ClientDashboard::class, 'index'])->name('dashboard');
    Route::get('/paiements', [ClientWallet::class, 'index'])->name('wallet.index');
    Route::get('/paiements/releve.pdf', [ClientWallet::class, 'statement'])->name('wallet.statement');
    Route::get('/paiements/{payment}/facture.pdf', [ClientWallet::class, 'receipt'])->name('wallet.receipt');
    Route::get('/support', [ClientSupport::class, 'index'])->name('support.index');
    Route::get('/profil',              [\App\Http\Controllers\Client\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil',              [\App\Http\Controllers\Client\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profil/adresse-livraison', [\App\Http\Controllers\Client\ProfileController::class, 'clearDeliveryAddress'])->name('profile.delivery-address.clear');
    Route::put('/profil/mot-de-passe', [\App\Http\Controllers\Client\ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/commandes/{order}/reclamation-garantie',[\App\Http\Controllers\Client\GuaranteeClaimController::class, 'store'])->name('guarantee-claims.store');
    Route::get('/commandes',                     [ClientOrder::class, 'index'])->name('orders.index');
    Route::get('/commandes/nouvelle/{artisan}',  [ClientOrder::class, 'create'])->name('orders.create');
    Route::post('/commandes',                    [ClientOrder::class, 'store'])->name('orders.store');
    Route::get('/commandes/{order}',             [ClientOrder::class, 'show'])->name('orders.show');
    Route::patch('/commandes/{order}/annuler',   [ClientOrder::class, 'cancel'])->name('orders.cancel');
    Route::patch('/commandes/{order}/valider',   [ClientOrder::class, 'validate'])->name('orders.validate');
    Route::patch('/commandes/{order}/litige',    [ClientOrder::class, 'dispute'])->name('orders.dispute');
    Route::post('/commandes/{order}/signer-remise', [ClientOrder::class, 'signDelivery'])->name('orders.sign-delivery');
    Route::post('/livraisons/{delivery}/appeler/{livreur}', [DeliveryChoiceController::class, 'request'])->name('deliveries.request-livreur');
});

// ── DEVIS NÉGOCIÉ (client + artisan, avant acceptation de la commande) ────────
Route::middleware(['auth', 'verify-email'])->group(function () {
    Route::post('/commandes/{order}/devis',                    [QuoteController::class, 'store'])->name('quotes.store');
    Route::patch('/commandes/{order}/devis/{quote}/accepter',  [QuoteController::class, 'accept'])->name('quotes.accept');
    Route::patch('/commandes/{order}/devis/{quote}/rejeter',   [QuoteController::class, 'reject'])->name('quotes.reject');
});

// ── KYC LÉGER (pièce d'identité + vérification téléphone) ────────────────────
Route::middleware(['auth', 'verify-email'])->prefix('verification')->name('kyc.')->group(function () {
    Route::get('/',                    [KycController::class, 'show'])->name('show');
    Route::post('/piece-identite',     [KycController::class, 'uploadDocument'])->name('document.upload');
    // throttle : évite le spam SMS/WhatsApp (coût) et le reset répété du
    // compteur de tentatives (phone_otp_attempts) via renvois successifs
    Route::post('/telephone/envoyer',  [KycController::class, 'sendPhoneOtp'])->middleware('throttle:3,1')->name('phone.send');
    Route::post('/telephone/confirmer',[KycController::class, 'verifyPhoneOtp'])->name('phone.verify');
    Route::get('/document/{user}',     [KycController::class, 'viewDocument'])->name('document.view');
});

// ── MESSAGERIE (conversations privées par paire) ───────────────────────────────
Route::middleware(['auth', 'verify-email'])->group(function () {
    Route::get('/commandes/{order}/messages',  [MessageController::class, 'index'])->name('messages.index');
    Route::get('/commandes/{order}/messages/avec/{contact}',  [MessageController::class, 'thread'])->name('messages.thread');
    Route::post('/commandes/{order}/messages/avec/{contact}', [MessageController::class, 'store'])->name('messages.store');

    // Suivi de livraison en direct (client, artisan, livreur assignés)
    Route::get('/commandes/{order}/suivi-livraison', [DeliveryTrackingController::class, 'show'])->name('deliveries.track');

    // Historique de contacts (clients ↔ artisans/livreurs déjà sollicités)
    Route::get('/mes-contacts', [ContactHistoryController::class, 'index'])->name('contacts.index');
});

// ── AVIS ──────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'verify-email', 'role:client'])->post('/avis', [ReviewController::class, 'store'])->name('reviews.store');
Route::middleware(['auth', 'verify-email', 'role:artisan'])->patch('/avis/{review}/repondre', [ReviewController::class, 'reply'])->name('reviews.reply');

// ── PAIEMENTS ─────────────────────────────────────────────────────────────────
// L'initiation du paiement se fait via client.orders.validate (Client\OrderController::validate).
Route::post('/paiement/callback', [PaymentController::class, 'callback'])->name('payment.callback')->withoutMiddleware(['web']);
Route::middleware(['auth', 'verify-email'])->group(function () {
    Route::get('/paiement/succes', [PaymentController::class, 'success'])->name('payment.success');
    Route::get('/paiement/echec',  [PaymentController::class, 'failure'])->name('payment.failure');
});
Route::middleware(['auth', 'verify-email'])->group(function () {
    Route::get('/fichiers/contrat/{order}', [SecureFileController::class, 'contract'])->name('files.contract');
    Route::get('/fichiers/contrat/{order}/telecharger', [SecureFileController::class, 'contractDownload'])->name('files.contract.download');
    Route::get('/fichiers/message/{message}/{kind}', [SecureFileController::class, 'message'])->name('files.message');
    Route::get('/fichiers/reclamation/{claim}', [SecureFileController::class, 'claim'])->name('files.claim');
    Route::get('/fichiers/preuve/{order}', [SecureFileController::class, 'completion'])->name('files.completion');
    Route::get('/fichiers/commande/{order}/image/{image}', [SecureFileController::class, 'orderImage'])->name('files.order-image');
});


// ── Pages légales ──────────────────────────────────────────
Route::get('/conditions-utilisation',   fn() => view('pages.cgu'))->name('cgu');
Route::get('/confidentialite',          fn() => view('pages.privacy'))->name('privacy');

// ── Favoris (client) ──────────────────────────────────────
Route::middleware(['auth', 'verify-email', 'role:client'])->group(function () {
    Route::get('/favoris',                        [FavoriteController::class, 'index'])->name('client.favorites.index');
    Route::post('/favoris/{artisan}/toggle',      [FavoriteController::class, 'toggle'])->name('favorites.toggle');
});

// ── Stats artisan ─────────────────────────────────────────
Route::middleware(['auth', 'verify-email', 'role:artisan'])->prefix('artisan')->name('artisan.')->group(function () {
    Route::get('/statistiques', [StatsController::class, 'index'])->name('stats.index');
    Route::get('/avis', [\App\Http\Controllers\Artisan\ReviewController::class, 'index'])->name('reviews.index');
});

// ── Middleware bootstrap/app.php ──────────────────────────
// Ajouter dans ->withMiddleware() :
// $middleware->alias([
//     'role'              => \App\Http\Middleware\CheckRole::class,
//     'persist-search'    => \App\Http\Middleware\PersistSearchFilters::class,
// ]);
// Et sur la route artisans.index :
// Route::get('/artisans', [SearchController::class, 'index'])
//     ->middleware('persist-search')
//     ->name('artisans.index');

// ── ADMIN ─────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'verify-email', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');
    Route::get('/profil',              [AdminProfile::class, 'edit'])->name('profile.edit');
    Route::put('/profil',              [AdminProfile::class, 'update'])->name('profile.update');
    Route::put('/profil/mot-de-passe', [AdminProfile::class, 'updatePassword'])->name('profile.password');
Route::get('/messages',                           [\App\Http\Controllers\Admin\MessageController::class, 'index'])->name('messages.index');
Route::get('/messages/{order}',                   [\App\Http\Controllers\Admin\MessageController::class, 'show'])->name('messages.show');
Route::delete('/messages/{message}',              [\App\Http\Controllers\Admin\MessageController::class, 'destroy'])->name('messages.destroy');
Route::patch('/messages/{message}/signaler',      [\App\Http\Controllers\Admin\MessageController::class, 'flag'])->name('messages.flag');
Route::patch('/messages/{message}/designaler',    [\App\Http\Controllers\Admin\MessageController::class, 'unflag'])->name('messages.unflag');
Route::get('/support',                            [AdminSupport::class, 'index'])->name('support.index');
Route::get('/support/{ticket}',                   [AdminSupport::class, 'show'])->name('support.show');
Route::patch('/support/{ticket}/assigner',        [AdminSupport::class, 'assign'])->name('support.assign');
Route::patch('/support/{ticket}/statut',          [AdminSupport::class, 'status'])->name('support.status');
Route::post('/support/{ticket}/repondre',         [AdminSupport::class, 'reply'])->name('support.reply');
Route::post('/commandes/{order}/alerter',         [\App\Http\Controllers\Admin\MessageController::class, 'alert'])->name('orders.alert');
Route::patch('/commandes/{order}/alerte-retirer', [\App\Http\Controllers\Admin\MessageController::class, 'clearAlert'])->name('orders.clear-alert');
Route::get('/reversements',                      [\App\Http\Controllers\Admin\WalletController::class, 'index'])->name('wallet.index');
Route::get('/reclamations-garantie',[\App\Http\Controllers\Admin\GuaranteeClaimController::class, 'index'])->name('guarantee-claims.index');
Route::patch('/reclamations-garantie/{claim}/approuver',[\App\Http\Controllers\Admin\GuaranteeClaimController::class, 'approve']) ->name('guarantee-claims.approve');
Route::patch('/reclamations-garantie/{claim}/rejeter', [\App\Http\Controllers\Admin\GuaranteeClaimController::class, 'reject'])->name('guarantee-claims.reject');
Route::patch('/reclamations-garantie/{claim}/confirmer-remboursement', [\App\Http\Controllers\Admin\GuaranteeClaimController::class, 'confirmRefund'])->name('guarantee-claims.confirm-refund');

    Route::patch('/reversements/{artisan}/reverser', [\App\Http\Controllers\Admin\WalletController::class, 'reverse'])->name('wallet.reverse');
    Route::patch('/paiements/{payment}/confirmer-remboursement', [\App\Http\Controllers\Admin\WalletController::class, 'confirmRefund'])->name('wallet.confirm-refund');
    Route::get('/utilisateurs',                     [AdminUser::class, 'index'])->name('users.index');
    Route::patch('/utilisateurs/{user}/verifier',   [AdminUser::class, 'verify'])->name('users.verify');
    Route::patch('/utilisateurs/{user}/piece/approuver', [AdminUser::class, 'approveDocument'])->name('users.document.approve');
    Route::patch('/utilisateurs/{user}/piece/rejeter',   [AdminUser::class, 'rejectDocument'])->name('users.document.reject');
    Route::patch('/utilisateurs/{user}/suspendre',  [AdminUser::class, 'suspend'])->name('users.suspend');
    Route::patch('/utilisateurs/{user}/toggle',     [AdminUser::class, 'suspend'])->name('users.toggle');
    Route::delete('/utilisateurs/{user}',           [AdminUser::class, 'destroy'])->name('users.destroy');
    Route::get('/export/utilisateurs',              [AdminUser::class, 'export'])->name('users.export');
    Route::get('/exports',                          [\App\Http\Controllers\Admin\ExportController::class, 'index'])->name('exports.index');
Route::get('/livraisons',                        [AdminDelivery::class, 'index'])->name('deliveries.index');
Route::get('/livraisons/{delivery}',             [AdminDelivery::class, 'show'])->name('deliveries.show');
Route::patch('/livraisons/{delivery}/reassigner',[AdminDelivery::class, 'reassign'])->name('deliveries.reassign');
 Route::get('/commandes',                        [AdminOrder::class, 'index'])->name('orders.index');
    Route::patch('/commandes/{order}/arbitrer',     [AdminOrder::class, 'arbitrate'])->name('orders.arbitrate');
    Route::get('/export/commandes',                 [AdminOrder::class, 'export'])->name('orders.export');
});