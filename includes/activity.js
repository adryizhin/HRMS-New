(() => {
 const panel=document.getElementById('activity-panel');if(!panel)return;
 const items=document.getElementById('activity-items'),mark=document.getElementById('activity-read'),error=document.getElementById('activity-error');
 const tools=document.querySelector('.activity-desktop-tools');
 const slot=document.querySelector('.activity-header-slot');
 const topbar=document.querySelector('.main .topbar');
 if(slot)slot.append(tools);else if(topbar){topbar.classList.add('activity-topbar');topbar.append(tools);}
 const toggles=[...document.querySelectorAll('.activity-toggle')];let data=JSON.parse(document.getElementById('activity-initial').textContent),busy=false,previous=null;
 const display=n=>n>99?'99+':String(n);
 function render(next){
  data=next;
  document.querySelectorAll('.activity-toggle .activity-total').forEach(b=>{b.textContent=display(data.total);b.hidden=!data.total;});
  toggles.forEach(b=>b.setAttribute('aria-label','Notifications'+(data.total?', '+data.total+' items needing attention':'')));
  items.replaceChildren();
  for(const group of data.groups){const link=document.createElement('a');link.className='activity-item';link.href=group.url;const icon=document.createElement('i');icon.className='fa-solid '+group.icon;icon.setAttribute('aria-hidden','true');const text=document.createElement('span');text.textContent=group.label;const count=document.createElement('span');count.className='activity-total';count.textContent=display(group.count);link.append(icon,text,count);items.append(link);}
  for(const update of data.updates){const link=document.createElement('a');link.className='activity-item activity-update';link.href=update.url;const icon=document.createElement('i');icon.className='fa-solid fa-circle-info';icon.setAttribute('aria-hidden','true');const text=document.createElement('span');text.textContent=update.message;const time=document.createElement('small');time.textContent=update.created_at;text.append(time);link.append(icon,text);items.append(link);}
  if(!data.total){const empty=document.createElement('p');empty.className='activity-empty';empty.textContent='You’re all caught up.';items.append(empty);}
  mark.hidden=!data.updates.length;
 }
 function close(){panel.hidden=true;toggles.forEach(b=>b.setAttribute('aria-expanded','false'));if(previous?.isConnected&&!previous.closest('[inert]'))previous.focus();}
 toggles.forEach(button=>button.addEventListener('click',()=>{if(!panel.hidden){close();return;}previous=button;panel.hidden=false;toggles.forEach(b=>b.setAttribute('aria-expanded','true'));document.getElementById('activity-close').focus();refresh();}));
 document.getElementById('activity-close').addEventListener('click',close);
 document.addEventListener('keydown',event=>{if(event.key==='Escape'&&!panel.hidden)close();});
 document.addEventListener('click',event=>{if(!panel.hidden&&!panel.contains(event.target)&&!event.target.closest('.activity-toggle'))close();});
 async function refresh(options){if(busy||document.hidden)return;busy=true;mark.disabled=true;try{const response=await fetch(panel.dataset.url,{credentials:'same-origin',cache:'no-store',...options});if(!response.ok||response.redirected)throw Error();render(await response.json());error.hidden=true;}catch(_){error.textContent='Notifications could not refresh. Try again.';error.hidden=false;}finally{busy=false;mark.disabled=false;}}
 mark.addEventListener('click',()=>{if(busy)return;mark.disabled=true;const body=new URLSearchParams({csrf_token:panel.dataset.token,ids:data.updates.map(u=>u.id).join(',')});refresh({method:'POST',body});});
 render(data);setInterval(()=>refresh(),15000);document.addEventListener('visibilitychange',()=>{if(!document.hidden)refresh();});
})();
