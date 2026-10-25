// LocalMarket — tiny helpers, no framework.
document.addEventListener('DOMContentLoaded', function () {
    // Confirmation dialog for destructive actions: <form data-confirm="¿Seguro?">
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!window.confirm(form.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });

    // Disable submit buttons after the first click to avoid double orders.
    document.querySelectorAll('form[data-once]').forEach(function (form) {
        form.addEventListener('submit', function () {
            form.querySelectorAll('button[type=submit]').forEach(function (btn) {
                btn.disabled = true;
                btn.dataset.originalText = btn.textContent;
                btn.textContent = 'Procesando…';
            });
        });
    });

    // Mobile menus
    document.querySelectorAll('[data-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = document.querySelector(btn.dataset.toggle);
            if (target) {
                target.classList.toggle('open');
                btn.setAttribute('aria-expanded', target.classList.contains('open'));
            }
        });
    });

    // Checkout: show address fields only for home delivery.
    var radios = document.querySelectorAll('input[name=fulfilment_method]');
    var address = document.getElementById('delivery-fields');
    function syncAddress() {
        var checked = document.querySelector('input[name=fulfilment_method]:checked');
        if (address) {
            address.hidden = !checked || checked.value !== 'delivery';
        }
    }
    radios.forEach(function (r) { r.addEventListener('change', syncAddress); });
    syncAddress();

    // Auto-hide success messages
    document.querySelectorAll('.alert-success[data-autohide]').forEach(function (el) {
        setTimeout(function () { el.style.transition = 'opacity .4s'; el.style.opacity = '0'; }, 5000);
    });
});
