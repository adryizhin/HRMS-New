const fs=require('fs'),path=require('path');const {chromium}=require('playwright');
(async()=>{
 const browser=await chromium.launch({channel:'msedge',headless:true});
 const out='C:/Users/Administrator/.codex/visualizations/2026/10/09/01a12073-b003-76b1-ad1d-2670f464c7ff';
 try{
  for(const role of ['admin','hr']){
   const context=await browser.newContext();const page=await context.newPage();
   await context.route('**/*',r=>r.request().url().startsWith('http://127.0.0.1:8091/')?r.continue():r.abort());
   await context.request.post('http://127.0.0.1:8091/HRMS/login/login_process.php',{form:{email:role+'@feature.example',password:'TemporaryFeaturePassword!'}});
   const errors=[];page.on('pageerror',e=>errors.push(e.message));
   await page.goto('http://127.0.0.1:8091/HRMS/'+role+'/dashboard.php');
   await page.waitForFunction(()=>typeof window.openChat==='function');
   const sender=await browser.newContext();const opposite=role==='admin'?'hr':'admin';
   await sender.request.post('http://127.0.0.1:8091/HRMS/login/login_process.php',{form:{email:opposite+'@feature.example',password:'TemporaryFeaturePassword!'}});
   await sender.request.post('http://127.0.0.1:8091/HRMS/chat/send_message.php',{form:{receiver_id:role==='admin'?'1':'2',message:'Browser unread notification fixture'}});
   await page.waitForFunction(()=>!document.getElementById('chatNotif').hidden,{},{timeout:12000});
   if(await page.locator('#chatNotif').textContent()==='0')throw Error('Badge shows zero');
   await page.locator('#chatAvatar').click();
   await page.locator('.msg').first().waitFor();
   await page.waitForFunction(()=>document.getElementById('chatNotif').hidden,{},{timeout:12000});
   await page.locator('#chatInput').fill('Reply from browser');await page.locator('#chatInput').press('Enter');
   await page.waitForFunction(()=>document.getElementById('chatInput').value==='');
   for(const theme of ['light','dark'])for(const width of [1440,390,320]){
    await page.setViewportSize({width,height:900});await page.evaluate(t=>document.documentElement.dataset.theme=t,theme);await page.waitForTimeout(300);
    const bounds=await page.locator('#chatBox').boundingBox();if(bounds.x<0||bounds.y<0||bounds.x+bounds.width>width)throw Error('Chatbox overflow');
    if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+1))throw Error(role+' dashboard overflow '+width);
    if(width===390&&theme==='dark')await page.screenshot({path:path.join(out,'todo-'+role+'-chat-mobile.png')});
   }
   for(const [width,height] of [[390,844],[320,568],[280,480],[667,375],[320,320]]){
    await page.setViewportSize({width,height});await page.locator('#chatInput').fill(Array(24).fill('Long draft text to check the message composer.').join('\n'));await page.waitForTimeout(250);
    for(const selector of ['#chatBox','#chatInput','#chatSend']){
     const bounds=await page.locator(selector).boundingBox();if(bounds.x<0||bounds.y<0||bounds.x+bounds.width>width+1||bounds.y+bounds.height>height+1)throw Error('Small chat control outside viewport '+role+' '+selector+' '+width+'x'+height);
    }
    if(!await page.locator('#chatBox [aria-label="Close chat"]').evaluate(el=>{const r=el.getBoundingClientRect();return el.contains(document.elementFromPoint(r.x+r.width/2,r.y+r.height/2));}))throw Error('Chat close button is covered by mobile header');
    const textState=await page.locator('#chatInput').evaluate(el=>{el.scrollTop=el.scrollHeight;return {scrollbar:getComputedStyle(el).scrollbarWidth,pseudo:getComputedStyle(el,'::-webkit-scrollbar').display,scrollable:el.scrollHeight>el.clientHeight,scrollTop:el.scrollTop};});
    if(textState.scrollbar!=='none'||textState.pseudo!=='none'||!textState.scrollable||textState.scrollTop===0)throw Error('Composer scrollbar/long draft behavior failed');
    if(await page.locator('#chatBody').evaluate(el=>el.scrollWidth>el.clientWidth+1))throw Error('Narrow message overflow');
    if(width===320&&height===568)await page.screenshot({path:path.join(out,'chat-'+role+'-small-screen.png')});
   }
   await page.setViewportSize({width:390,height:844});
   await page.evaluate(()=>{window.__chatTestViewport=window.visualViewport;Object.defineProperty(window,'visualViewport',{configurable:true,value:{height:300,offsetTop:0}});window.dispatchEvent(new Event('resize'));});
   const keyboardBox=await page.locator('#chatBox').boundingBox(),keyboardSend=await page.locator('#chatSend').boundingBox();
   if(keyboardBox.y<0||keyboardBox.y+keyboardBox.height>300+1||keyboardSend.y+keyboardSend.height>300+1)throw Error('Chat overlaps simulated keyboard');
   await page.evaluate(()=>{Object.defineProperty(window,'visualViewport',{configurable:true,value:window.__chatTestViewport});delete window.__chatTestViewport;window.dispatchEvent(new Event('resize'));});
   await page.locator('#chatInput').fill('');
   console.log('PASS '+role+' narrow/short chat layouts, hidden composer scrollbar, long draft scrolling and keyboard viewport fit');
   if(errors.length)throw Error(role+': '+errors.join('; '));
   console.log('PASS '+role+' robot, unread badge, reading/clearing, sending, both themes and responsive layouts');
   if(role==='admin'){
    await page.setViewportSize({width:1440,height:900});await page.goto('http://127.0.0.1:8091/HRMS/admin/hr_evaluations.php');
    if(await page.getByRole('link',{name:'HR Evaluations',exact:true}).count()!==1)throw Error('Combined sidebar link count');
    await page.getByRole('link',{name:'Review Evaluations',exact:true}).click();await page.locator('.submission-card').first().waitFor();
    for(const width of [1440,390,320]){
     await page.setViewportSize({width,height:900});await page.waitForTimeout(250);
     if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+1))throw Error('Combined review tab overflow '+width);
     if(width===1440)await page.screenshot({path:path.join(out,'combined-hr-evaluations-review.png'),fullPage:true});
    }
    await page.getByRole('link',{name:'Score',exact:true}).first().click();await page.getByRole('link',{name:'Back to HR Evaluations',exact:true}).click();
    await page.getByRole('link',{name:'Assign Evaluations',exact:true}).click();await page.locator('input[name="title"]').waitFor();
    for(const width of [1440,390,320]){
     await page.setViewportSize({width,height:900});await page.waitForTimeout(250);
     if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+1))throw Error('Combined assignment tab overflow '+width);
     if(width===390)await page.screenshot({path:path.join(out,'combined-hr-evaluations-assign-mobile.png'),fullPage:true});
    }
    console.log('PASS combined System Administrator HR evaluation tabs, one sidebar link, scoring return and responsive layouts');
   }
   if(role==='hr'){
    await page.goto('http://127.0.0.1:8091/HRMS/hr/dashboard.php');
    await page.locator('#send-evaluation').waitFor();
    if(await page.getByRole('link',{name:'Send Evaluation',exact:true}).count())throw Error('Extra evaluation sidebar link remains');
    if(await page.locator('.sent-tasks').getAttribute('open')!==null)throw Error('Sent history should start collapsed');
    for(const theme of ['light','dark'])for(const width of [1440,1100,390,320]){
     await page.setViewportSize({width,height:900});await page.evaluate(t=>document.documentElement.dataset.theme=t,theme);await page.waitForTimeout(350);
     if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+1))throw Error('Combined dashboard overflow '+width);
     const messages=await page.locator('.chat-contacts').boundingBox(),evaluation=await page.locator('#send-evaluation').boundingBox();
     if(width>1000 && (evaluation.x<=messages.x || Math.abs(evaluation.y-messages.y)>1))throw Error('Panels are not side by side '+JSON.stringify({width,messages,evaluation}));
     if(width<1000 && evaluation.y<messages.y+messages.height)throw Error('Mobile panels do not stack');
     if(width===1440&&theme==='dark')await page.screenshot({path:path.join(out,'hr-dashboard-two-columns.png'),fullPage:true});
     if(width===390&&theme==='dark')await page.screenshot({path:path.join(out,'hr-dashboard-stacked-mobile.png'),fullPage:true});
    }
    await page.locator('.sent-tasks summary').click();if(!await page.locator('.sent-tasks .task-grid').isVisible())throw Error('Cannot expand sent evaluations');
    console.log('PASS HR dashboard left/right panels, mobile stacking and collapsible history');
    await page.goto('http://127.0.0.1:8091/HRMS/hr/hr_leave_requests.php');
    for(const theme of ['light','dark'])for(const width of [1440,390,320]){
     await page.setViewportSize({width,height:900});await page.evaluate(t=>document.documentElement.dataset.theme=t,theme);await page.waitForTimeout(300);
     if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+1))throw Error('Leave overflow '+width);
     if(width===390)await page.screenshot({path:path.join(out,'todo-leave-'+theme+'-mobile.png'),fullPage:true});
    }
    if(await page.locator('script').filter({hasText:'Leave reason fixture'}).count())throw Error('Unescaped leave reason');
    await page.locator('#leave-status').selectOption('pending');await page.getByRole('button',{name:'Filter',exact:true}).click();
    if(await page.locator('.leave-status.approved').count())throw Error('Leave filter failed');
    console.log('PASS HR leave table/cards, both themes, mobile layout and status filter');
   }
   await sender.close();await context.close();
  }
  for(const role of ['admin','hr','employee']){
   const context=await browser.newContext();const page=await context.newPage();const errors=[];page.on('pageerror',e=>errors.push(e.message));
   await context.route('**/*',r=>r.request().url().startsWith('http://127.0.0.1:8091/')?r.continue():r.abort());
   await context.request.post('http://127.0.0.1:8091/HRMS/login/login_process.php',{form:{email:role+'@feature.example',password:'TemporaryFeaturePassword!'}});
   await page.goto('http://127.0.0.1:8091/HRMS/'+role+'/dashboard.php');
   if(await page.locator('.sidebar .activity-toggle,.sidebar-activity-badge').count())throw Error('Notifications remain in sidebar');
   for(const width of [1440,390,320]){
    await page.setViewportSize({width,height:900});await page.waitForTimeout(250);
    const toggle=page.locator(width>1024?'.activity-desktop-tools .activity-toggle':'.mobile-header .activity-toggle');await toggle.click();
    await page.locator('#activity-panel').waitFor({state:'visible'});
    if(await page.getByRole('link',{name:/Unread messages/}).count())throw Error('Chat notification appears in bell');
    if(width===1440){
     const roleBounds=await page.locator('.activity-desktop-tools .activity-role').boundingBox(),bellBounds=await toggle.boundingBox();
     if(bellBounds.x<roleBounds.x+roleBounds.width||Math.abs((bellBounds.y+bellBounds.height/2)-(roleBounds.y+roleBounds.height/2))>2)throw Error('Bell is not beside role label');
     if(await toggle.locator('.activity-total').isVisible()){
      const badge=await toggle.locator('.activity-total').boundingBox();if(badge.x<bellBounds.x+bellBounds.width/2||badge.y>=bellBounds.y)throw Error('Unread badge does not overlap bell '+JSON.stringify({bellBounds,badge}));
     }
     await page.screenshot({path:path.join(out,'notifications-'+role+'-header-desktop.png')});
    }
    const bounds=await page.locator('#activity-panel').boundingBox();if(bounds.x<0||bounds.x+bounds.width>width+1)throw Error('Notification panel overflow '+role+' '+width);
    if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+1))throw Error('Notification page overflow '+role+' '+width);
    if(width===390)await page.screenshot({path:path.join(out,'notifications-'+role+'-mobile.png')});
    await page.getByRole('button',{name:'Close notifications',exact:true}).click();
    if(await toggle.getAttribute('aria-expanded')!=='false')throw Error('Bell did not collapse');
   }
   if(role==='employee'){
    await page.locator('.mobile-header .activity-toggle').click();await page.getByRole('button',{name:'Mark displayed updates as read',exact:true}).click();
    await page.waitForFunction(()=>document.getElementById('activity-read').hidden);
    if(await page.locator('#activity-items').textContent()!=='You’re all caught up.')throw Error('Personal notification did not clear');
   }
   if(errors.length)throw Error(role+' notification errors: '+errors.join('; '));
   console.log('PASS '+role+' notification bell, panel, mobile layout and read controls');await context.close();
  }
 }finally{await browser.close()}
})().catch(e=>{console.error(e);process.exit(1)});
