(() => {
 const dialog=document.getElementById('employee-department-dialog');if(!dialog)return;
 const user=document.getElementById('department-user-id'),select=document.getElementById('edit-department-id');
 document.querySelectorAll('[data-edit-department]').forEach(button=>button.addEventListener('click',()=>{
  user.value=button.dataset.userId;document.getElementById('department-employee-name').textContent=button.dataset.userName;
  select.value=button.dataset.departmentId==='0'?'':button.dataset.departmentId;
  dialog.showModal();select.focus();
 }));
 dialog.querySelectorAll('[data-close-department]').forEach(button=>button.addEventListener('click',()=>dialog.close()));
})();
