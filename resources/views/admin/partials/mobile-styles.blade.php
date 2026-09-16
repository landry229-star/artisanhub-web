<style>
    /* ===== Corrections affichage mobile pour tout le back-office admin ===== */

    /* Les KPI passent à 2 par ligne sur mobile au lieu d'être écrasés à 4 */
    @media (max-width: 767.98px) {
        .stat-card { padding: 14px !important; }
        .stat-value { font-size: 1.15rem !important; }
        .stat-label { font-size: .72rem !important; }
        .content-card { padding: 14px !important; }
        .section-title, h1 { word-break: break-word; }
    }

    /* Bascule tableau -> liste de cartes en dessous de 768px */
    .mobile-cards { display: none; }
    @media (max-width: 767.98px) {
        .table-responsive.desktop-only-table { display: none !important; }
        .mobile-cards { display: block; }
    }

    .mcard {
        border: 1px solid #ECD8C6;
        border-radius: 12px;
        padding: 12px;
        margin-bottom: 10px;
        background: #fff;
    }
    .mcard-row {
        display: flex;
        justify-content: space-between;
        gap: 8px;
        font-size: .82rem;
        padding: 3px 0;
    }
    .mcard-row .label { color: #9A8070; }
    .mcard-actions {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        margin-top: 8px;
    }
    .mcard-actions form { flex: 1 1 auto; }
    .mcard-actions .btn { width: 100%; }

    /* Formulaires de filtre : empilés proprement sur mobile */
    @media (max-width: 767.98px) {
        form.row.g-2 > [class*="col-"] { width: 100%; }
    }

    /* Champs "signaler un message" : passent en colonne sur mobile pour éviter le débordement */
    @media (max-width: 575.98px) {
        .flag-form-inline { flex-direction: column; align-items: stretch !important; }
    }

    /* Select de réassignation livreur : pleine largeur sur mobile */
    @media (max-width: 575.98px) {
        .reassign-form { flex-direction: column; align-items: stretch !important; }
        .reassign-form select { max-width: 100% !important; }
    }

    /* Modales toujours lisibles sur petit écran */
    @media (max-width: 575.98px) {
        .modal-dialog { margin: .5rem; }
    }
</style>
