(() => {
 const input = document.getElementById('promotion-employee');
 if (!input) return;
 const id = document.getElementById('promotion-employee-id');
 const list = document.getElementById('promotion-employee-results');
 const status = document.getElementById('promotion-employee-status');
 const picker = input.closest('.promotion-employee-picker');
 const buttons = [...list.querySelectorAll('button')];
 const close = () => { list.hidden = true; input.setAttribute('aria-expanded', 'false'); };
 const visible = () => buttons.filter(button => !button.parentElement.hidden);
 const search = () => {
  const term = input.value.trim().toLocaleLowerCase();
  buttons.forEach(button => { button.parentElement.hidden = !term || !button.dataset.employeeLabel.toLocaleLowerCase().includes(term); });
  const count = visible().length;
  list.hidden = !term || count === 0;
  input.setAttribute('aria-expanded', String(!list.hidden));
  status.textContent = term ? (count ? `${count} matching employee(s). Choose a result.` : 'No matching employees found. Try another name or ID.') : 'No employee selected.';
 };
 input.addEventListener('input', () => { id.value = ''; input.setCustomValidity(''); search(); });
 input.addEventListener('focus', () => { if (!id.value) search(); });
 buttons.forEach(button => button.addEventListener('click', () => {
  id.value = button.dataset.employeeId;
  input.value = button.dataset.employeeLabel;
  input.setCustomValidity('');
  status.textContent = `Selected: ${button.dataset.employeeLabel}`;
  close();
  input.focus();
 }));
 picker.addEventListener('keydown', event => {
  if (event.key === 'Escape') { event.preventDefault(); close(); input.focus(); }
  if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
   if (document.activeElement === input && list.hidden && !id.value) search();
   const matches = visible();
   if (list.hidden || !matches.length) return;
   event.preventDefault();
   const index = matches.indexOf(document.activeElement);
   const next = event.key === 'ArrowDown' ? (index + 1) % matches.length : (index <= 0 ? matches.length - 1 : index - 1);
   matches[next].focus();
  }
  if (event.key === 'Enter' && event.target === input && !id.value) {
   event.preventDefault();
   search();
   if (visible().length) visible()[0].focus();
  }
 });
 picker.addEventListener('focusout', event => { if (!picker.contains(event.relatedTarget)) close(); });
 input.form.addEventListener('submit', event => {
  if (!id.value) {
   event.preventDefault();
   input.setCustomValidity('Choose an employee from the search results.');
   input.reportValidity();
  }
 });
})();
