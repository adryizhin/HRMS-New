const {chromium}=require('playwright'),path=require('path');
(async()=>{
 const browser=await chromium.launch({channel:'msedge',headless:true});
 const base='http://127.0.0.1:8091/HRMS/',out='C:/Users/Administrator/.codex/visualizations/2026/10/09/01a12073-b003-76b1-ad1d-2670f464c7ff';
 const dates={cutoff_start:'2026-10-01',cutoff_end:'2026-10-15',payout_date:'2026-10-16'};
 async function login(role){const c=await browser.newContext();await c.route('**/*',r=>r.request().url().startsWith(base)?r.continue():r.abort());await c.request.post(base+'login/login_process.php',{form:{email:role+'@feature.example',password:'TemporaryFeaturePassword!'}});return c;}
 try {
  const hr=await login('hr');for(const id of [1,2,3])await hr.request.post(base+'hr/payroll.php',{form:{...dates,employee_id:String(id),allowance:'1200'}});await hr.close();
  for(const role of ['admin','hr','employee']) {
   const context=await login(role),page=await context.newPage();
   await page.goto(base+role+'/payroll.php?'+new URLSearchParams({...dates,...(role==='hr'?{employee_id:'2'}:{})}));
   await page.evaluate(()=>document.documentElement.dataset.theme='dark');
   await page.emulateMedia({media:'print'});
   for(const width of [1440,390]) {
    await page.setViewportSize({width,height:1000});
    for(const selector of ['.sidebar','.mobile-header','.activity-desktop-tools','.form-panel','.topbar','#chatAvatar','#chatBox']){
     if(await page.locator(selector).count()&&await page.locator(selector).first().isVisible())throw Error(role+' prints '+selector);
    }
    if(!await page.locator('.payslip').isVisible()||!(await page.locator('.payslip').innerText()).includes('Official Salary Breakdown'))throw Error(role+' payslip missing');
    const layout=await page.locator('.main').evaluate(el=>({margin:getComputedStyle(el).marginLeft,padding:getComputedStyle(el).padding,background:getComputedStyle(document.body).backgroundColor}));
    if(layout.margin!=='0px'||layout.padding!=='0px'||layout.background!=='rgb(255, 255, 255)')throw Error('Print layout incorrect '+JSON.stringify(layout));
   }
   await page.setViewportSize({width:794,height:1123});
   if(role==='employee') {await page.screenshot({path:path.join(out,'payslip-print-only.png'),fullPage:true});await page.pdf({path:path.join(out,'payslip-print-only.pdf'),format:'A4',margin:{top:'10mm',right:'10mm',bottom:'10mm',left:'10mm'},displayHeaderFooter:false});}
   await page.emulateMedia({media:'screen'});await page.setViewportSize({width:1440,height:1000});if(!await page.locator('.sidebar').isVisible()||!await page.locator('.topbar').isVisible())throw Error('Screen navigation changed');
   console.log('PASS '+role+' print shows only payslip, hides navigation and chat, keeps readable colors and screen navigation');await context.close();
  }
 } finally {await browser.close();}
})().catch(e=>{console.error(e);process.exitCode=1;});
