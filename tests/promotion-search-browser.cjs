const {chromium}=require('playwright');
const assert=require('node:assert/strict');
(async()=>{
 const browser=await chromium.launch({channel:'msedge',headless:true});
 try{
  const base='http://127.0.0.1:8091/HRMS/';
  const context=await browser.newContext();
  await context.route('**/*',r=>r.request().url().startsWith(base)?r.continue():r.abort());
  await context.request.post(base+'login/login_process.php',{form:{email:'admin@feature.example',password:'TemporaryFeaturePassword!'}});
  const page=await context.newPage();const errors=[];page.on('pageerror',e=>errors.push(e.message));
  await page.goto(base+'admin/promotions.php');
  const input=page.locator('#promotion-employee'),id=page.locator('#promotion-employee-id'),results=page.locator('#promotion-employee-results');
  assert.equal(await page.locator('select[name=employee_id]').count(),0);
  assert.equal(await results.isVisible(),false);
  await input.fill('no such employee');assert.match(await page.locator('#promotion-employee-status').innerText(),/No matching employees/);
  await input.fill('IT Admin');assert.equal(await page.locator('[data-employee-id="1005"]').isVisible(),true);await page.locator('[data-employee-id="1005"]').click();assert.equal(await id.inputValue(),'1005');
  await input.fill('Employee Feature');assert.equal(await page.locator('#promotion-employee-results li:visible').count(),1);
  await page.locator('[data-employee-id="3"]').click();assert.equal(await id.inputValue(),'3');
  await input.fill('TEST-3');assert.equal(await id.inputValue(),'');
  await page.locator('#promotion-position').fill('Operations Coordinator');await page.locator('#promotion-reason').fill('Recommend review of readiness for wider operations responsibilities.');
  await page.getByRole('button',{name:'Send suggestion to HR',exact:true}).click();
  assert.equal(await input.evaluate(el=>el.validity.customError),true);
  await input.fill('TEST-3');await input.press('ArrowDown');await page.keyboard.press('Enter');assert.equal(await id.inputValue(),'3');
  await input.fill('Senior Operations');assert.equal(await page.locator('#promotion-employee-results li:visible').count(),1);
  for(const width of [1440,390,320])for(const theme of ['light','dark']){
   await page.setViewportSize({width,height:950});await page.evaluate(t=>document.documentElement.dataset.theme=t,theme);
   assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+1),false);
  }
  await input.press('Escape');assert.equal(await results.isVisible(),false);
  await input.fill('TEST-3');await page.locator('[data-employee-id="3"]').click();
  await Promise.all([page.waitForNavigation(),page.getByRole('button',{name:'Send suggestion to HR',exact:true}).click()]);
  assert.match(await page.locator('.leave-notice[role=status]').innerText(),/sent to Human Resource/);
  assert.equal(await page.locator('.promotion-card').filter({hasText:'Operations Coordinator'}).count(),1);
  assert.deepEqual(errors,[]);
  console.log('PASS admin employee search by name, ID and position, selection validation, keyboard controls, responsive themes, and suggestion submission');
 }finally{await browser.close();}
})().catch(error=>{console.error(error);process.exitCode=1;});
