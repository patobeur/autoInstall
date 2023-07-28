window.addEventListener('DOMContentLoaded', function() {
    var themeToggleCheckbox = document.getElementById('theme-toggle-checkbox');
    var themeToggleLabel = document.getElementById('theme-toggle-label');

    themeToggleCheckbox.addEventListener('change', function() {
        if (this.checked) {
            document.body.classList.add('dark-theme');
            themeToggleLabel.setAttribute('aria-label', 'Activer le thème jour');
        } else {
            document.body.classList.remove('dark-theme');
            themeToggleLabel.setAttribute('aria-label', 'Activer le thème nuit');
        }
    });
});