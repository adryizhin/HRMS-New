(() => {
    const sidebar = document.getElementById('app-sidebar');
    const toggle = document.getElementById('sidebar-toggle');
    const close = document.getElementById('sidebar-close');
    const backdrop = document.getElementById('sidebar-backdrop');
    if (!sidebar || !toggle || !close || !backdrop) return;
    const mobile = window.matchMedia('(max-width: 1024px)');
    const main = document.querySelector('.main');
    const header = document.querySelector('.mobile-header');
    let open = false;

    function setOpen(value, restoreFocus = true) {
        open = mobile.matches && value;
        document.body.classList.toggle('sidebar-open', open);
        toggle.setAttribute('aria-expanded', String(open));
        toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
        backdrop.hidden = !open;
        sidebar.inert = mobile.matches && !open;
        sidebar.setAttribute('aria-hidden', String(mobile.matches && !open));
        if (main) main.inert = open;
        if (header) header.inert = open;
        if (open) {
            sidebar.setAttribute('role', 'dialog');
            sidebar.setAttribute('aria-modal', 'true');
            close.focus();
        } else {
            sidebar.removeAttribute('role');
            sidebar.removeAttribute('aria-modal');
            if (restoreFocus && mobile.matches) toggle.focus();
        }
    }
    toggle.addEventListener('click', () => setOpen(!open));
    close.addEventListener('click', () => setOpen(false));
    backdrop.addEventListener('click', () => setOpen(false));
    sidebar.querySelectorAll('a').forEach(link => link.addEventListener('click', () => setOpen(false, false)));
    document.addEventListener('keydown', event => {
        if (!open) return;
        if (event.key === 'Escape') { event.preventDefault(); setOpen(false); }
        if (event.key === 'Tab') {
            const controls = [...sidebar.querySelectorAll('a[href], button:not(:disabled)')].filter(element => element.getClientRects().length > 0);
            const first = controls[0];
            const last = controls[controls.length - 1];
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
            else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
        }
    });
    mobile.addEventListener('change', () => setOpen(false, false));
    setOpen(false, false);

    // Keep wide data tables scrollable within their section on narrow screens.
    document.querySelectorAll('.main table').forEach(table => {
        if (table.parentElement.classList.contains('responsive-table')) return;
        const wrapper = document.createElement('div');
        wrapper.className = 'responsive-table';
        wrapper.tabIndex = 0;
        wrapper.setAttribute('role', 'region');
        wrapper.setAttribute('aria-label', 'Scrollable table');
        table.before(wrapper);
        wrapper.append(table);
    });
})();
