const fs=require('fs'),path=require('path');
const {chromium}=require('playwright');
const shared=fs.readFileSync('includes/contrast.css','utf8')+'\n'+fs.readFileSync('includes/sidebar.php','utf8').match(/<style>\r?\n([\s\S]*?)<\/style>/)[1];
const pages=['login.php',...['admin','employee','hr'].flatMap(d=>fs.readdirSync(d).filter(f=>f.endsWith('.php')).map(f=>d+'/'+f)),'includes/attendance_page.php','includes/payslip_page.php'];
(async()=>{
 const browser=await chromium.launch({headless:true,channel:process.env.CONTRAST_BROWSER || 'msedge'});
 const page=await browser.newPage({viewport:{width:1440,height:1000}});
 await page.route('**/*',r=>r.abort());
 const failures=[];let checked=0;
 const fixtures={ 'employee/dashboard.php':'<div class="task-card"><span class="status-chip pending">Pending</span><span class="status-chip submitted">Submitted</span><span class="status-chip reviewed">Reviewed</span><span class="task-meta-list"><span class="overdue">Overdue</span></span><div class="submission-form"><button>Submit</button><button disabled>Closed</button></div></div>', 'hr/task_submissions.php':'<div class="task-card"><span class="status-badge submitted">Submitted</span><span class="status-badge reviewed">Reviewed</span><span class="status-badge scored">Scored</span></div>', 'employee/documents.php':'<span class="green">Verified</span><span class="red">Rejected</span><span class="blue">Pending</span>', 'hr/201_files.php':'<span class="green">Verified</span><span class="red">Rejected</span><span class="blue">Pending</span>', 'hr/kpi.php':'<span class="excellent">Excellent</span><span class="good">Good</span><span class="watch">Watch</span><span class="risk">Risk</span>', 'employee/kpi.php':'<span class="excellent">Excellent</span><span class="good">Good</span><span class="watch">Watch</span><span class="risk">Risk</span>' }; 
 for(const file of pages){
  let source=fs.readFileSync(file,'utf8');
  if(!/<(?:html|style|head)[\s>]/i.test(source))continue;
  source=source.replace(/<\?php[\s\S]*?\?>|<\?=[\s\S]*?\?>/g,'').replace(/<script\b[\s\S]*?<\/script>/gi,'');
  source=source.replace(/<link\b[^>]*href="([^"]+\.css)"[^>]*>/g,(all,href)=>{const p=path.resolve(path.dirname(file),href);return fs.existsSync(p)?'<style>'+fs.readFileSync(p,'utf8')+'</style>':'';});
  source=source.replace(/<link\b[^>]*>/g,'');
  if(file!=='login.php')source=source.replace(/<body[^>]*>/,'$&<style>'+shared+'</style>');
  for(const theme of file==='login.php'?['dark']:['light','dark']){
   await page.setContent(source);
   await page.evaluate(t=>document.documentElement.dataset.theme=t,theme);
   await page.addStyleTag({content:'*, *::before, *::after { transition: none !important; animation: none !important; }'});
   await page.evaluate(html=>{const host=document.querySelector('.main')||document.body;host.insertAdjacentHTML('beforeend',html);}, fixtures[file]||'');
   if(file!=='login.php')await page.evaluate(()=>document.body.insertAdjacentHTML('beforeend','<div class="sidebar"><h2>Pagecom HRIS</h2><a href="#">Dashboard</a><a href="#" class="active">Active page</a><button class="theme-toggle-btn">Dark Mode</button></div>'));
   const audit=()=>page.evaluate(()=>{
    const rgb=c=>(c.match(/[\d.]+/g)||[]).map(Number);
    const blend=(a,b)=>{let alpha=a[3]??1;return a.slice(0,3).map((x,i)=>x*alpha+b[i]*(1-alpha));};
    const lum=c=>c.slice(0,3).map(v=>{v/=255;return v<=.04045?v/12.92:((v+.055)/1.055)**2.4;}).reduce((a,v,i)=>a+v*[.2126,.7152,.0722][i],0);
    const ratio=(a,b)=>(Math.max(lum(a),lum(b))+.05)/(Math.min(lum(a),lum(b))+.05);
    function bg(el){if(!el)return [255,255,255];const s=getComputedStyle(el);const stops=s.backgroundImage.match(/rgba?\([^)]+\)/g);return stops?blend(rgb(stops[0]),bg(el.parentElement)):blend(rgb(s.backgroundColor),bg(el.parentElement));}
    const bad=[];let count=0;
    for(const el of document.querySelectorAll('body *')){
     if(['STYLE','SCRIPT','OPTION'].includes(el.tagName)||!el.getClientRects().length)continue;
     const s=getComputedStyle(el);if(s.visibility==='hidden'||s.display==='none')continue;
     const txt=[...el.childNodes].filter(n=>n.nodeType===3).map(n=>n.textContent).join('').trim();
     if(!txt&&!['INPUT','TEXTAREA','SELECT'].includes(el.tagName))continue;
     if(el.closest('svg'))continue;
     let b=bg(el),a=blend(rgb(s.color),b); const r=ratio(a,b);
     const large=parseFloat(s.fontSize)>=24||(parseFloat(s.fontSize)>=18.66&&parseInt(s.fontWeight)>=700);
     count++;
     if(r<(large?3:4.5))bad.push({selector:el.tagName.toLowerCase()+(el.className?'.'+String(el.className).trim().replace(/\s+/g,'.'):''),text:txt.slice(0,65),ratio:+r.toFixed(2),color:s.color,bg:b});
    }
    return {count,bad};
   });
   const result=await audit();
   checked+=result.count;
   failures.push(...result.bad.map(x=>({file,theme,...x})));
   // Exercise CSS hover rules without submitting forms or running application scripts.
   const interactive=page.locator('button, a');
   for(let i=0;i<await interactive.count();i++){
    const el=interactive.nth(i);if(!await el.isVisible()||!await el.isEnabled())continue;
    await el.hover({force:true,timeout:1500}).catch(()=>{});
    const state=await audit();checked+=state.count;
    for(const x of state.bad)if(!failures.some(f=>f.file===file&&f.theme===theme&&f.selector===x.selector&&f.text===x.text&&f.ratio===x.ratio))failures.push({file,theme,state:'hover',...x});
   }
  }
 }
 await browser.close();
 fs.writeFileSync('tests/contrast-results.json',JSON.stringify({checked,failures},null,2));
 console.log(JSON.stringify({checked,failures},null,2));
 process.exitCode=failures.length?1:0;
})().catch(e=>{console.error(e);process.exit(1)});
