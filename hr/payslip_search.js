(() => {
    const search = document.getElementById('payee-search');
    const select = document.getElementById('employee_id');
    const clear = document.getElementById('payee-search-clear');
    const result = document.getElementById('payee-search-result');
    if (!search || !select || !clear || !result) return;
    const options = [...select.options].filter(option => option.value).map(option => option.cloneNode(true));
    let chosen = select.value;
    const normalize = value => value.normalize('NFKC').trim().toLocaleLowerCase();
    function filter() {
        const term = normalize(search.value);
        const matches = options.filter(option => normalize(option.dataset.search || option.textContent).includes(term));
        const placeholder = new Option(matches.length ? 'Choose a payee' : 'No matching staff', '');
        select.replaceChildren(placeholder, ...matches.map(option => option.cloneNode(true)));
        // Filtering must not silently select a different person for payroll generation.
        select.value = matches.some(option => option.value === chosen) ? chosen : '';
        clear.hidden = !search.value;
        result.textContent = matches.length
            ? `${matches.length} staff ${matches.length === 1 ? 'record' : 'records'} found. Choose a payee, then Generate & Save.`
            : 'No matching staff. Try another name, ID, department or role, or clear the search.';
    }
    select.addEventListener('change', () => { chosen = select.value; });
    search.addEventListener('input', filter);
    search.addEventListener('keydown', event => {
        if (event.key === 'Enter') { event.preventDefault(); select.focus(); }
    });
    clear.addEventListener('click', () => { search.value = ''; filter(); search.focus(); });
    filter();
})();
