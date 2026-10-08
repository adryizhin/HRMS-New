const fs=require('fs');
const {chromium}=require('playwright');
const accounts=JSON.parse(fs.readFileSync('.runtime/test-accounts.json','utf8'));
(async()=>{
 const browser=await chromium.launch({channel:'msedge',headless:true});const results=[];
 for(const role of ['admin','hr','employee']){
  const ctx=await browser.newContext({baseURL:'http://127.0.0.1:8088/HRMS/'});
  await ctx.route('**/*',r=>new URL(r.request().url()).hostname==='127.0.0.1'?r.continue():r.abort());
  const user=accounts.users.find(u=>u.role===role);
  await ctx.request.post('login/login_process.php',{form:{email:user.email,password:accounts.password}});
  const page=await ctx.newPage();const errors=[];page.on('pageerror',e=>errors.push(e.message));
  await page.goto(role+'/dashboard.php');
  await page.locator('#theme-toggle-btn').click();
  const theme=await page.locator('html').getAttribute('data-theme');
  results.push({role,theme,errors});await ctx.close();
 }
 await browser.close();fs.writeFileSync('tests/browser-results.json',JSON.stringify(results,null,2));console.log(JSON.stringify(results));if(results.some(r=>r.errors.length||r.theme!=='dark'))process.exitCode=1;
})().catch(e=>{console.error(e);process.exit(1)});
