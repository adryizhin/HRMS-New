const {chromium}=require('playwright');
(async()=>{const browser=await chromium.launch({channel:'msedge',headless:true}),base='http://127.0.0.1:8091/HRMS/';try{
for(const role of ['employee','hr']){
 const context=await browser.newContext();await context.route('**/*',r=>r.request().url().startsWith(base)?r.continue():r.abort());await context.request.post(base+'login/login_process.php',{form:{email:role+'@feature.example',password:'TemporaryFeaturePassword!'}});const page=await context.newPage(),errors=[];page.on('pageerror',e=>errors.push(e.message));await page.goto(base+(role==='employee'?'employee/request_leave.php':'hr/hr_leave_requests.php'));
 for(const theme of ['light','dark'])for(const width of [1440,390,320]){await page.setViewportSize({width,height:900});await page.evaluate(t=>document.documentElement.dataset.theme=t,theme);if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+1))throw Error(role+' leave page overflow '+width);}
 if(role==='employee'){
  await page.locator('#hr_id').selectOption('2');await page.locator('#leave_type').selectOption('Sick Leave');await page.locator('#start_date').fill('2027-04-05');await page.locator('#end_date').fill('2027-04-06');await page.locator('#reason').fill('Browser end-to-end leave request');await page.getByRole('button',{name:'Check working days'}).click();await page.locator('.leave-preview').waitFor();if(!await page.locator('#reason').inputValue())throw Error('Preview cleared form');await page.getByRole('button',{name:'Submit Request',exact:true}).click();await page.locator('.success').waitFor();await page.locator('.leave-history-card').filter({hasText:'Browser end-to-end leave request'}).waitFor();
 }else{
  const row=page.locator('.leave-table tbody tr').filter({hasText:'Browser end-to-end leave request'});await row.waitFor();await row.locator('textarea').fill('Approved after checking the requested dates.');await row.getByRole('button',{name:'Approve',exact:true}).click();await page.locator('.leave-notice').waitFor();if(!await page.getByText('Approved after checking the requested dates.',{exact:false}).count())throw Error('Review notes missing');
 }
 if(errors.length)throw Error(errors.join('; '));console.log('PASS '+role+' leave workflow, details, light/dark and desktop/mobile layouts');await context.close();
}
}finally{await browser.close();}})().catch(e=>{console.error(e);process.exitCode=1;});
