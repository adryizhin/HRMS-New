(() => {
    const dialog = document.getElementById('payroll-preview');
    if (!dialog) return;
    let returnFocus = document.querySelector('[data-open-payroll="' + dialog.dataset.employee + '"]');
    function open() {
        if (!dialog.open) dialog.showModal();
        document.body.classList.add('payroll-modal-open');
    }
    dialog.querySelector('[data-payroll-close]').addEventListener('click', () => dialog.close());
    dialog.addEventListener('click', event => {
        const rect = dialog.getBoundingClientRect();
        if (event.target === dialog && (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom)) dialog.close();
    });
    dialog.addEventListener('close', () => {
        if (dialog.open) return;
        document.body.classList.remove('payroll-modal-open');
        const url = new URL(window.location.href);
        url.searchParams.delete('employee_id');
        window.history.replaceState(null, '', url);
        (returnFocus || document.querySelector('.payroll-filters input'))?.focus();
    });
    document.querySelectorAll('[data-open-payroll]').forEach(link => {
        link.addEventListener('click', event => {
            if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button !== 0) return;
            if (link.dataset.openPayroll !== dialog.dataset.employee) return;
            event.preventDefault();
            returnFocus = link;
            window.history.replaceState(null, '', link.href);
            open();
        });
    });
    open();
})();
