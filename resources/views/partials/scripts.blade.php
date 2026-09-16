{{--
    Remplace le bloc de scripts dans les 2 layouts.
    Inclut Bootstrap, artisanhub.js et notifications.js
--}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/artisanhub.js') }}"></script>
<script src="{{ asset('js/notifications.js') }}"></script>
@stack('scripts')
