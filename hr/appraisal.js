(() => {
 const form=document.getElementById('appraisal-form');if(!form)return;
 function update(){let total=0,count=0;form.querySelectorAll('[data-criterion]').forEach(section=>{const key=section.dataset.criterion,selected=section.querySelector('input[type=radio]:checked'),rating=selected?Number(selected.value):0,points=rating/4*Number(section.dataset.weight);if(rating)count++;form.querySelector('[data-rating="'+key+'"]').textContent=rating||'—';form.querySelector('[data-points="'+key+'"]').textContent=points.toFixed(2);total+=points;});document.getElementById('appraisal-total').textContent=total.toFixed(2);document.getElementById('appraisal-completeness').textContent=count===6?'All six sections rated. Weighted total is calculated out of 100%.':count+' of 6 sections rated. Complete all sections before saving.';}
 form.addEventListener('change',update);update();
 let heights=[];window.addEventListener('beforeprint',()=>{heights=[...form.querySelectorAll('textarea')].map(el=>[el,el.style.height]);heights.forEach(([el])=>{el.style.height=el.scrollHeight+'px';});});window.addEventListener('afterprint',()=>heights.forEach(([el,height])=>el.style.height=height));
})();
