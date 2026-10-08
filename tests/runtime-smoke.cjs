const fs=require('fs');
const {request}=require('playwright');
const accounts=JSON.parse(fs.readFileSync('.runtime/test-accounts.json','utf8'));
const base='http://127.0.0.1:8088/HRMS/';
const paths={admin:['dashboard.php','create_user_account.php','departments.php','attendance.php','holidays.php','payroll.php','login_logs.php'],hr:['dashboard.php','task_submissions.php','201_files.php','hr_leave_requests.php','attendance.php','kpi.php','payroll.php'],employee:['dashboard.php','documents.php','request_leave.php','attendance.php','kpi.php','payroll.php']};
(async()=>{
 const results=[];
 for(const role of Object.keys(paths)){
  const user=accounts.users.find(x=>x.role===role);if(!user)throw Error('Missing '+role);
  const ctx=await request.newContext({baseURL:base});
  const login=await ctx.post('login/login_process.php',{form:{email:user.email,password:accounts.password}});
  results.push({role,path:'login',status:login.status(),url:login.url(),ok:login.status()===200&&login.url().includes(role+'/dashboard.php')});
  for(const p of paths[role]){
   const res=await ctx.get(role+'/'+p);const body=await res.text();
   results.push({role,path:p,status:res.status(),ok:res.status()===200&&!/Fatal error|Parse error|Warning:|Deprecated:/.test(body)&&!res.url().includes('login.php')});
  }
  if(role==='hr'){
   const employee=accounts.users.find(x=>x.role==='employee');
   const res=await ctx.post('hr/post_task.php',{form:{title:'Runtime smoke check '+Date.now(),description:'Temporary evaluation in isolated verification database.',deadline:'2026-12-31','employee_ids[]':employee.id}});
   results.push({role,path:'post_task.php',status:res.status(),url:res.url(),ok:res.status()===200&&res.url().includes('task_sent=1')});
  }
  if(role==='hr'){
   const list=await ctx.get('hr/task_submissions.php');const html=await list.text();
   const match=html.match(/score_submission.php\?id=(\d+)/);
   if(!match)throw Error('Evaluation review link missing');
   const id=match[1];
   for(const file of ['score_submission.php','view_submission.php']){
    const response=await ctx.get('hr/'+file+'?id='+id);
    results.push({role,path:file,status:response.status(),ok:response.status()===200});
   }
   const saved=await ctx.post('hr/score_submission.php?id='+id,{form:{hr_score:'85',hr_feedback:'Isolated test score'}});
   results.push({role,path:'save score',status:saved.status(),ok:saved.status()===200&&(await saved.text()).includes('Score saved successfully')});
  }
  const logout=await ctx.get('logout.php');
  results.push({role,path:'logout',status:logout.status(),ok:logout.url().includes('login.php')});
  await ctx.dispose();
 }
 fs.writeFileSync('tests/runtime-results.json',JSON.stringify(results,null,2));
 console.log(JSON.stringify(results,null,2));
 if(results.some(r=>!r.ok))process.exitCode=1;
})().catch(e=>{console.error(e);process.exit(1)});
