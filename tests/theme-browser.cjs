const {chromium}=require('playwright');
(async()=>{
 const browser=await chromium.launch({channel:'msedge',headless:true});
 try {
  for(const role of ['admin','hr','employee']) {
   const context=await browser.newContext();
   await context.route('**/*',r=>r.request().url().startsWith('http://127.0.0.1:8091/')?r.continue():r.abort());
   await context.request.post('http://127.0.0.1:8091/HRMS/login/login_process.php',{form:{email:role+'@feature.example',password:'TemporaryFeaturePassword!'}});
   const page=await context.newPage(),errors=[];page.on('pageerror',e=>errors.push(e.message));
   for(const route of [role+'/dashboard.php','account/settings.php']) {
    await page.goto('http://127.0.0.1:8091/HRMS/'+route);
    for(const width of [1440,390,320]) {
     await page.setViewportSize({width,height:900});
     const toggle=page.locator('.theme-toggle-btn:visible'),bell=page.locator('.activity-toggle:visible');
     if(await toggle.count()!==1||await page.locator('.sidebar .theme-toggle-btn').count())throw Error('Wrong theme button placement');
     const a=await toggle.boundingBox(),b=await bell.boundingBox();
     if(a.x<b.x+b.width||Math.abs(a.y-b.y)>3||a.x+a.width>width)throw Error('Theme control is not beside bell: '+JSON.stringify({role,route,width,a,b}));
     const before=await page.locator('html').getAttribute('data-theme');
     await toggle.click();
     const after=before==='dark'?'light':'dark';
     if(await page.locator('html').getAttribute('data-theme')!==after)throw Error('Theme did not switch');
     if(await toggle.getAttribute('aria-label')!==`Switch to ${after==='dark'?'light':'dark'} mode`)throw Error('Accessible theme label is incorrect');
     const icon=after==='dark'?'fa-sun':'fa-moon';
     if(await page.locator('.theme-toggle-btn i.'+icon).count()!==2)throw Error('Theme icons are not synchronized');
     await page.reload();
     if(await page.locator('html').getAttribute('data-theme')!==after)throw Error('Theme did not persist');
    }
   }
   if(errors.length)throw Error(errors.join('\n'));
   console.log('PASS '+role+' theme beside bell, Font Awesome icons, switching and persistence at desktop/phone sizes');
   await context.close();
  }
 } finally { await browser.close(); }
})().catch(e=>{console.error(e);process.exitCode=1;});
