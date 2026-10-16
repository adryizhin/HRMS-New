const fs=require('fs'),path=require('path');const {chromium}=require('playwright');
(async()=>{
 const browser=await chromium.launch({channel:'msedge',headless:true});const base='http://127.0.0.1:8093/HRMS/';
 const out='C:/Users/Administrator/.codex/visualizations/2026/10/09/01a12073-b003-76b1-ad1d-2670f464c7ff';
 try{
  const loginPage=await browser.newPage();await loginPage.route('**/*',r=>r.request().url().startsWith(base)?r.continue():r.abort());await loginPage.goto(base+'login.php');
  await loginPage.locator('#password').fill('PrivatePassword!');await loginPage.getByRole('button',{name:'Show password',exact:true}).click();if(await loginPage.locator('#password').getAttribute('type')!=='text')throw Error('Eye toggle did not reveal password');await loginPage.getByRole('button',{name:'Hide password',exact:true}).click();if(await loginPage.locator('#password').getAttribute('type')!=='password'||await loginPage.locator('#password').inputValue()!=='PrivatePassword!')throw Error('Eye toggle changed password');console.log('PASS login password eye toggle');await loginPage.close();
  for(const role of ['admin','hr']){
   const context=await browser.newContext();await context.route('**/*',r=>r.request().url().startsWith(base)?r.continue():r.abort());
   await context.request.post(base+'login/login_process.php',{form:{email:role+'@feature.example',password:role==='admin'?'UpdatedAccountPassword!':'TemporaryFeaturePassword!'}});
   const page=await context.newPage();const errors=[];page.on('pageerror',e=>errors.push(e.message));await page.goto(base+role+'/dashboard.php');await page.waitForFunction(()=>typeof startChat==='function');
   await page.evaluate(id=>startChat(id,id===1?'Admin Updated':'Hr Feature Person'),role==='admin'?2:1);
   await page.locator('#chatAttachment').setInputFiles({name:role+'-browser-report.txt',mimeType:'text/plain',buffer:Buffer.from('Browser attachment fixture.')});
   if(!await page.locator('#chatAttachmentPreview').isVisible())throw Error('Attachment preview missing');
   await page.getByRole('button',{name:'Remove attachment',exact:true}).click();if(await page.locator('#chatAttachmentPreview').isVisible())throw Error('Remove attachment failed');
   await page.locator('#chatAttachment').setInputFiles({name:role+'-browser-report.txt',mimeType:'text/plain',buffer:Buffer.from('Browser attachment fixture.')});await page.getByRole('button',{name:'Send message',exact:true}).click();await page.waitForFunction(()=>document.getElementById('chatAttachmentPreview').hidden);await page.locator('.chat-file-link').filter({hasText:role+'-browser-report.txt'}).waitFor();
   const download=page.waitForEvent('download');await page.locator('.chat-file-link').filter({hasText:role+'-browser-report.txt'}).click();if((await download).suggestedFilename()!==role+'-browser-report.txt')throw Error('Download name wrong');
   await page.locator('#chatAttachment').setInputFiles({name:role+'-browser-picture.png',mimeType:'image/png',buffer:fs.readFileSync(path.join(__dirname,'../images/logo.png'))});
   await page.getByRole('button',{name:'Send message',exact:true}).click();
   const picture=page.locator('.chat-message-image[alt="'+role+'-browser-picture.png"]');
   await picture.waitFor();await picture.evaluate(img=>img.complete&&img.naturalWidth?true:new Promise((resolve,reject)=>{img.addEventListener('load',resolve,{once:true});img.addEventListener('error',()=>reject(Error('Picture failed to load')),{once:true});}));
   if(!(await picture.evaluate(img=>img.naturalWidth>0)))throw Error('Sent picture did not render');
   const frame=await picture.evaluate(img=>[img,img.parentElement,img.closest('.msg')].map(el=>{const s=getComputedStyle(el);return {border:s.borderTopWidth,shadow:s.boxShadow,background:s.backgroundImage,color:s.backgroundColor};}));
   if(frame.some(s=>s.border!=='0px'||s.shadow!=='none')||frame[2].background!=='none'||frame[2].color!=='rgba(0, 0, 0, 0)')throw Error('Picture has a border or bubble frame');
   for(const theme of ['light','dark'])for(const width of [1440,390,320]){await page.setViewportSize({width,height:900});await page.evaluate(t=>document.documentElement.dataset.theme=t,theme);await page.waitForTimeout(250);if(await page.locator('#chatBody').evaluate(el=>el.scrollWidth>el.clientWidth))throw Error('Attachment bubble overflow');}
   await page.goto(base+'account/settings.php');
   for(const theme of ['light','dark'])for(const width of [1440,390,320]){await page.setViewportSize({width,height:900});await page.evaluate(t=>document.documentElement.dataset.theme=t,theme);await page.waitForTimeout(250);if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+1))throw Error('Settings overflow '+role+' '+width);if(width===390&&theme==='dark')await page.screenshot({path:path.join(out,'account-settings-'+role+'-mobile.png'),fullPage:true});}
   if(role==='admin') {
    await page.getByRole('button',{name:'Adjust current photo',exact:true}).click();await page.locator('#profilePhotoEditor').waitFor({state:'visible'});
    await page.getByRole('button',{name:'Cancel photo change',exact:true}).click();if(await page.locator('#profilePhotoEditor').isVisible())throw Error('Crop cancel failed');
    const sourceImage=await page.evaluate(()=>{const c=document.createElement('canvas');c.width=900;c.height=300;const x=c.getContext('2d');x.fillStyle='rgb(255,0,0)';x.fillRect(0,0,300,300);x.fillStyle='rgb(0,0,255)';x.fillRect(300,0,300,300);x.fillStyle='rgb(0,128,0)';x.fillRect(600,0,300,300);return c.toDataURL('image/png').split(',')[1]});
    await page.locator('#profile_picture').setInputFiles({name:'alignment-fixture.png',mimeType:'image/png',buffer:Buffer.from(sourceImage,'base64')});await page.locator('#profilePhotoEditor').waitFor({state:'visible'});
    const pixel=()=>page.locator('#profileCropPreview').evaluate(c=>[...c.getContext('2d').getImageData(256,256,1,1).data].slice(0,3));
    if((await pixel()).join(',')!=='0,0,255')throw Error('Default center crop incorrect');
    await page.locator('#photoHorizontal').evaluate(s=>{s.value='0';s.dispatchEvent(new Event('input'))});if((await pixel()).join(',')!=='255,0,0')throw Error('Left alignment preview incorrect');
    await page.locator('#photoHorizontal').evaluate(s=>{s.value='100';s.dispatchEvent(new Event('input'))});if((await pixel()).join(',')!=='0,128,0')throw Error('Right alignment preview incorrect');
    await page.locator('#photoZoom').evaluate(s=>{s.value='2';s.dispatchEvent(new Event('input'))});if(await page.locator('#photoZoomValue').textContent()!=='200%')throw Error('Zoom preview incorrect');
    await page.getByRole('button',{name:'Reset alignment',exact:true}).click();if(await page.locator('#photoZoom').inputValue()!=='1'||await page.locator('#photoHorizontal').inputValue()!=='50')throw Error('Reset failed');
    await page.locator('#profileCropPreview').scrollIntoViewIfNeeded();
    const crop=await page.locator('#profileCropPreview').boundingBox();await page.mouse.move(crop.x+crop.width/2,crop.y+crop.height/2);await page.mouse.down();await page.mouse.move(crop.x+crop.width/2+25,crop.y+crop.height/2);await page.mouse.up();if(Number(await page.locator('#photoHorizontal').inputValue())>=50)throw Error('Drag alignment failed');
    for(const theme of ['light','dark'])for(const width of [1440,390,320]){await page.setViewportSize({width,height:900});await page.evaluate(t=>document.documentElement.dataset.theme=t,theme);await page.waitForTimeout(250);if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+1))throw Error('Crop editor overflow');if(width===390&&theme==='dark')await page.locator('.settings-card').first().screenshot({path:path.join(out,'profile-photo-crop-mobile.png')});}
    await page.locator('#photoHorizontal').evaluate(s=>{s.value='100';s.dispatchEvent(new Event('input'))});
    await Promise.all([page.waitForURL('**/account/settings.php'),page.getByRole('button',{name:'Save changes',exact:true}).click()]);
    await page.getByRole('status').filter({hasText:'Account settings saved.'}).waitFor();
    await page.waitForFunction(()=>document.querySelector('.settings-avatar img')?.complete);
    const saved=await page.locator('.settings-avatar img').evaluate(img=>{const c=document.createElement('canvas');c.width=c.height=512;c.getContext('2d').drawImage(img,0,0);return {width:img.naturalWidth,height:img.naturalHeight,pixel:[...c.getContext('2d').getImageData(256,256,1,1).data].slice(0,3)}});
    if(saved.width!==512||saved.height!==512||saved.pixel.join(',')!=='0,128,0')throw Error('Saved photo did not match adjusted crop');
    console.log('PASS current-photo editing, cancel, zoom, alignment, dragging, reset, responsive preview and matching saved 512px crop');
   }
   if(role==='admin'&&await page.locator('.settings-avatar img').count()!==1)throw Error('Profile photo missing');
   if(errors.length)throw Error(errors.join('; '));console.log('PASS '+role+' attachment select/remove/send/download and settings layouts in both themes');await context.close();
  }
 }finally{await browser.close()}
})().catch(e=>{console.error(e);process.exit(1)});
