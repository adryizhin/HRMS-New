(function () {
    const panel = document.querySelector('.chat-contacts');
    if (!panel) return;
    const search = panel.querySelector('#contact-search');
    const rows = [...panel.querySelectorAll('.contact-row')];
    panel.addEventListener('click', function (event) {
        const row = event.target.closest('.contact-row');
        if (!row) return;
        startChat(Number(row.dataset.contactId), row.dataset.contactName);
        rows.forEach(contact => contact.setAttribute('aria-pressed', String(contact === row)));
    });
    search.addEventListener('input', function () {
        const query = search.value.trim().toLocaleLowerCase();
        rows.forEach(row => { row.hidden = !row.dataset.contactName.toLocaleLowerCase().includes(query); });
        panel.querySelectorAll('.contact-group').forEach(group => {
            group.hidden = query !== '' && ![...group.querySelectorAll('.contact-row')].some(row => !row.hidden);
        });
        panel.querySelector('.contacts-no-results').hidden = query === '' || rows.some(row => !row.hidden);
    });
})();
