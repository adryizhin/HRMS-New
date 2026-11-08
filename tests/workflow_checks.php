<?php
require_once dirname(__DIR__).'/includes/payslip_page.php';
require_once dirname(__DIR__).'/includes/promotions.php';
ensurePayrollProfiles($conn);ensurePromotionSchema($conn);
verify(payrollCutoff('2026-01-31')===['2026-01-31','2026-02-14'],'Payroll cutoff is exactly 15 inclusive calendar days across month end');
verify(payrollCutoff('2028-02-20')===['2028-02-20','2028-03-05'],'Payroll cutoff handles leap years');
verify(payrollCutoff('2026-01-01','end')===['2025-12-18','2026-01-01'],'Selecting cutoff end computes the start date');
$blocked=false;try{payrollCutoff('2026-02-30');}catch(InvalidArgumentException $e){$blocked=true;}verify($blocked,'Invalid payroll dates are rejected');
[$code,$body]=requestFeature('hr','hr/payroll.php?employee_id=3');preg_match('/name="csrf_token" value="([a-f0-9]+)"/',$body,$payrollToken);
verify($code===200 && str_contains($body,'Staff payroll') && str_contains($body,'Open payroll') && !str_contains($body,'<select id="employee_id"'),'HR payroll uses staff directory instead of Payee selection');
$allowance=['csrf_token'=>$payrollToken[1],'employee_id'=>3,'action'=>'save_allowance','is_tenured'=>'1','allowance_amount'=>'1200.50','effective_from'=>'2026-01-01','allowance_notes'=>'Long-serving staff allowance'];
[$code,$body]=requestFeature('hr','hr/payroll.php',$allowance);
verify($code===200 && (float)payrollProfile($conn,3)['allowance']===1200.5 && (int)$conn->query('SELECT COUNT(*) FROM payslips WHERE user_id=3')->fetch_row()[0]===0,'HR configures recurring allowance without generating payroll');
foreach([1,2] as $person){$a=$allowance;$a['employee_id']=$person;$a['allowance_amount']='500.00';requestFeature('hr','hr/payroll.php',$a);verify((float)payrollProfile($conn,$person)['allowance']===500.0,'HR can configure allowance for '.($person===1?'Admin':'Human Resource'));}
[$code,$body]=requestFeature('hr','hr/payroll.php?q=Employee&allowance_filter=with_allowance&sort=tenured');
verify($code===200 && str_contains($body,'Employee Feature Person') && str_contains($body,'1 staff record(s)'),'HR searches and filters allowance recipients');
$blocked=false;try{savePayrollProfile($conn,1,3,['allowance_amount'=>'999.00']);}catch(InvalidArgumentException $e){$blocked=true;}verify($blocked,'Administrators cannot change HR-controlled allowance configuration');
verify(payrollAllowance(['is_tenured'=>1,'allowance'=>500,'effective_from'=>'2027-01-01'],'2026-12-31')===0.0 && payrollAllowance(['is_tenured'=>0,'allowance'=>500,'effective_from'=>null],'2027-01-01')===0.0,'Disabled and not-yet-effective allowances are not paid');
$generate=['csrf_token'=>$payrollToken[1],'employee_id'=>3,'action'=>'generate','cutoff_start'=>'2026-10-01','cutoff_end'=>'2026-12-31','payout_date'=>'2026-10-16','allowance'=>'99999999','deduction'=>'0','tax'=>'0'];
[$code,$body]=requestFeature('hr','hr/payroll.php',array_replace($generate,['csrf_token'=>'invalid']));verify($code===400 && (int)$conn->query('SELECT COUNT(*) FROM payslips WHERE user_id=3')->fetch_row()[0]===0,'Payroll generation requires CSRF');
[$code,$body]=requestFeature('hr','hr/payroll.php',$generate);
$slip=$conn->query('SELECT * FROM payslips WHERE user_id=3 ORDER BY id DESC LIMIT 1')->fetch_assoc();
verify($code===200 && $slip['cutoff_end']==='2026-10-15' && (float)$slip['allowance']===1200.5,'Payroll enforces 15 days and uses configured allowance instead of submitted amount');
$notifyBefore=(int)$conn->query("SELECT COUNT(*) FROM notifications WHERE user_id=3 AND activity_type='payroll'")->fetch_row()[0];requestFeature('hr','hr/payroll.php',$generate);
verify((int)$conn->query('SELECT COUNT(*) FROM payslips WHERE user_id=3')->fetch_row()[0]===1 && $notifyBefore===1 && (int)$conn->query("SELECT COUNT(*) FROM notifications WHERE user_id=3 AND activity_type='payroll'")->fetch_row()[0]===1,'Regenerating a cutoff does not duplicate payslips or initial availability alerts');
[$code,$body]=requestFeature('employee','employee/payroll.php');verify($code===200 && str_contains($body,'View payslip') && str_contains($body,'payslip_id='.$slip['id']),'Employee payroll lists clickable generated payslips');
[$code,$body]=requestFeature('employee','employee/payroll.php?payslip_id='.$slip['id']);verify($code===200 && str_contains($body,'PHP 1,200.50') && str_contains($body,'Official Salary Breakdown'),'Employee opens saved payroll snapshot including allowance');
$conn->query("INSERT INTO payslips(user_id,cutoff_start,cutoff_end,payout_date) VALUES(1,'2026-09-01','2026-09-15','2026-09-16')");$privateSlip=(int)$conn->insert_id;
[$code,$body]=requestFeature('employee','employee/payroll.php?payslip_id='.$privateSlip);verify($code===404 && str_contains($body,'Payslip not found for your account.'),'Employee cannot open another account payslip');
$updated=$allowance;$updated['allowance_amount']='1600.00';requestFeature('hr','hr/payroll.php',$updated);
verify((float)$conn->query('SELECT allowance FROM payslips WHERE id='.(int)$slip['id'])->fetch_row()[0]===1200.5,'Changing recurring allowance preserves existing payroll snapshots');
[$code,$body]=requestFeature('employee','notifications/activity.php');$data=json_decode($body,true);$payrollUpdates=array_filter($data['updates'],static fn($r)=>str_contains($r['message'],'payslip'));verify(count($payrollUpdates)>0 && str_contains(array_values($payrollUpdates)[0]['url'],'employee/payroll.php'),'Payroll notifications lead to the payroll viewing page');

[$code,$body]=requestFeature('employee','employee/request_leave.php');preg_match('/name="csrf_token" value="([a-f0-9]+)"/',$body,$leaveToken);preg_match('/name="request_key" value="([a-f0-9]+)"/',$body,$leaveKey);
verify(!str_contains($body,'name="hr_id"') && str_contains($body,'Mandatory Leave'),'Employee can request Mandatory Leave without selecting HR');
$beforeBalance=remainingLeaveDays($conn,3,2027);$request=['csrf_token'=>$leaveToken[1],'request_key'=>$leaveKey[1],'hr_id'=>99999,'leave_type'=>'Mandatory Leave','start_date'=>'2027-05-03','end_date'=>'2027-05-04','reason'=>'Shared mandatory leave fixture'];
[$code,$body]=requestFeature('employee','employee/request_leave.php',$request);$leave=$conn->query("SELECT * FROM leave_requests WHERE request_key='".$leaveKey[1]."'")->fetch_assoc();
verify($code===200 && $leave['hr_id']===null && remainingLeaveDays($conn,3,2027)===$beforeBalance-2,'Mandatory Leave enters shared queue and uses the shared balance');
foreach(['hr','otherhr'] as $viewer){[$code,$body]=requestFeature($viewer,'hr/hr_leave_requests.php');verify($code===200 && str_contains($body,'Shared mandatory leave fixture'),'Shared leave request appears for '.$viewer);}
[$code,$body]=requestFeature('otherhr','hr/hr_leave_requests.php');preg_match('/name="csrf_token" value="([a-f0-9]+)"/',$body,$otherLeaveToken);
requestFeature('otherhr','hr/hr_leave_requests.php',['csrf_token'=>$otherLeaveToken[1],'id'=>$leave['id'],'status'=>'rejected','review_notes'=>'Shared queue decision']);
verify(remainingLeaveDays($conn,3,2027)===$beforeBalance && (int)$conn->query('SELECT reviewed_by FROM leave_requests WHERE id='.(int)$leave['id'])->fetch_row()[0]===1004,'Any active HR can decide shared leave and rejection restores days');
verify(!reviewLeaveRequest($conn,2,(int)$leave['id'],'approved','Second attempt'),'A second HR cannot apply a second leave decision');

[$code,$body]=requestFeature('hr','hr/task_submissions.php?q=Appraisal+feature+fixture');verify($code===200 && str_contains($body,'Appraisal feature fixture') && !str_contains($body,'<h3>Admin accomplishment</h3>'),'HR review search filters evaluation titles');
[$code,$body]=requestFeature('hr','hr/task_submissions.php?q=TEST-3');verify($code===200 && str_contains($body,'Appraisal feature fixture'),'HR review search supports staff employee ID');
[$code,$body]=requestFeature('employee','employee/dashboard.php?evaluation_status=submitted&q=accomplishment');verify($code===200 && str_contains($body,'Employee accomplishment') && !str_contains($body,'<h3>Admin accomplishment</h3>'),'Employee filters submitted evaluations within own assignments');
[$code,$body]=requestFeature('employee','employee/dashboard.php?evaluation_status=not_submitted&q=Appraisal+feature+fixture');verify(!str_contains($body,'<h3>Appraisal feature fixture</h3>'),'Employee can filter out already submitted work');

[$code,$body]=requestFeature('admin','admin/promotions.php');preg_match('/name="csrf_token" value="([a-f0-9]+)"/',$body,$promotionToken);
$proposal=['csrf_token'=>$promotionToken[1],'employee_id'=>3,'proposed_position'=>'Senior Operations Specialist','reason'=>'Consistent delivery and readiness for wider responsibilities.'];
[$code,$body]=requestFeature('admin','admin/promotions.php',array_replace($proposal,['csrf_token'=>'bad']));verify($code===400 && (int)$conn->query('SELECT COUNT(*) FROM promotion_suggestions')->fetch_row()[0]===0,'Promotion suggestion requires CSRF');
[$code,$body]=requestFeature('admin','admin/promotions.php',$proposal);$promotion=$conn->query('SELECT * FROM promotion_suggestions ORDER BY id DESC LIMIT 1')->fetch_assoc();
verify($code===200 && $promotion['status']==='pending' && $conn->query('SELECT job_title FROM users WHERE id=3')->fetch_row()[0]===null,'Admin sends promotion suggestion without changing the employee position');
requestFeature('admin','admin/promotions.php',$proposal);verify((int)$conn->query("SELECT COUNT(*) FROM promotion_suggestions WHERE employee_id=3 AND status='pending'")->fetch_row()[0]===1,'Duplicate pending promotion suggestions are blocked');
foreach(['hr','otherhr'] as $viewer){[$code,$body]=requestFeature($viewer,'hr/promotions.php');verify($code===200 && str_contains($body,'Senior Operations Specialist'),'Promotion suggestion appears to '.$viewer);}
[$code,$body]=requestFeature('hr','notifications/activity.php');verify((json_decode($body,true)['badges']['hr.promotions']??0)===1,'Pending promotion alerts Human Resource through the header bell');
foreach(['Employee Feature Person','TEST-3','Senior Operations','Admin Feature Person','Consistent delivery'] as $term){
 [$code,$body]=requestFeature('hr','hr/promotions.php?status=pending&q='.rawurlencode($term));
 verify($code===200 && str_contains($body,'<strong>Senior Operations Specialist</strong>') && str_contains($body,'1 suggestion(s) found'),'HR promotion search matches '.$term);
}
foreach(['unmatched search','%',"' OR 1=1 --"] as $term){
 [$code,$body]=requestFeature('hr','hr/promotions.php?q='.rawurlencode($term));
 verify($code===200 && !str_contains($body,'<strong>Senior Operations Specialist</strong>') && str_contains($body,'No promotion suggestions match your filters.'),'HR promotion search safely excludes unmatched literal '.$term);
}
[$code,$body]=requestFeature('hr','hr/promotions.php?status=approved&q=Senior');
verify($code===200 && !str_contains($body,'<strong>Senior Operations Specialist</strong>'),'HR promotion search combines with status filter');

$blocked=false;try{decidePromotion($conn,1,(int)$promotion['id'],'approved','Not HR');}catch(InvalidArgumentException $e){$blocked=true;}verify($blocked,'Administrators cannot make HR promotion decisions');
[$code,$body]=requestFeature('hr','hr/promotions.php');preg_match('/name="csrf_token" value="([a-f0-9]+)"/',$body,$decisionToken);
[$code,$body]=requestFeature('hr','hr/promotions.php',['csrf_token'=>$decisionToken[1],'id'=>$promotion['id'],'decision'=>'approved','decision_notes'=>'Ready for senior responsibilities.']);
$person=$conn->query('SELECT job_title,role FROM users WHERE id=3')->fetch_assoc();verify($code===200 && $person['job_title']==='Senior Operations Specialist' && $person['role']==='employee','HR approval updates position without changing access role');
verify(!decidePromotion($conn,1004,(int)$promotion['id'],'rejected','Second review'),'Promotion cannot be decided twice');
verify((int)$conn->query("SELECT COUNT(*) FROM notifications WHERE activity_type='promotion' AND user_id IN(1,3)")->fetch_row()[0]===2,'Promotion decision notifies the employee and suggesting administrator once');
$second=suggestPromotion($conn,1,3,'Operations Lead','Potential next step');verify(decidePromotion($conn,2,$second,'rejected','Needs more experience.') && $conn->query('SELECT job_title FROM users WHERE id=3')->fetch_row()[0]==='Senior Operations Specialist','HR rejection preserves the current position');
[$code,$body]=requestFeature('employee','hr/promotions.php');verify(!str_contains($body,'HR decision notes'),'Employee cannot access HR promotion decisions');
[$code,$body]=requestFeature('employee','account/settings.php');verify(str_contains($body,'Senior Operations Specialist'),'Approved position appears in employee account settings');

[$code,$body]=requestFeature('admin','admin/promotions.php');
verify($code===200 && str_contains($body,'data-employee-id="1005"') && str_contains($body,'IT Admin'),'Admin promotion search includes IT administrator accounts');
$itProposal=array_replace($proposal,['employee_id'=>1005,'proposed_position'=>'Senior IT Administrator','reason'=>'Recommend review for expanded infrastructure responsibilities.']);
[$code,$body]=requestFeature('admin','admin/promotions.php',$itProposal);
$itPromotion=$conn->query('SELECT * FROM promotion_suggestions WHERE employee_id=1005 ORDER BY id DESC LIMIT 1')->fetch_assoc();
verify($code===200 && $itPromotion && $itPromotion['status']==='pending' && $itPromotion['current_position']==='IT Administrator','Admin can suggest another IT administrator for promotion');
[$code,$body]=requestFeature('hr','hr/promotions.php?q=Senior+IT');
verify($code===200 && str_contains($body,'<strong>Senior IT Administrator</strong>'),'HR can find IT administrator promotion suggestions');
[$code,$body]=requestFeature('hr','hr/promotions.php',['csrf_token'=>$decisionToken[1],'id'=>$itPromotion['id'],'decision'=>'approved','decision_notes'=>'Approved for senior IT duties.']);
$itPerson=$conn->query('SELECT job_title,role FROM users WHERE id=1005')->fetch_assoc();
verify($code===200 && $itPerson['job_title']==='Senior IT Administrator' && $itPerson['role']==='admin','HR approval promotes IT staff while preserving admin access');
$itSecond=suggestPromotion($conn,1,1005,'IT Team Lead','Consider future leadership responsibilities.');
verify(decidePromotion($conn,2,$itSecond,'rejected','Further review required.') && $conn->query('SELECT job_title FROM users WHERE id=1005')->fetch_row()[0]==='Senior IT Administrator','HR can reject IT promotion without changing current position');
$blocked=false;try{suggestPromotion($conn,1,2,'HR Lead','Review');}catch(InvalidArgumentException $e){$blocked=true;}verify($blocked,'Promotion eligibility remains limited to employees and administrators');

if(getenv('HRMS_WORKFLOW_BROWSER')==='1'){
 $process=proc_open(['node',__DIR__.'/workflow-browser.cjs'],[0=>['pipe','r'],1=>STDOUT,2=>STDERR],$bp,dirname(__DIR__),$environment);
 if(!is_resource($process)||proc_close($process)!==0)throw new RuntimeException('Workflow browser checks failed');
}

if(getenv('HRMS_PAYROLL_MODAL_BROWSER')==='1'){
 $process=proc_open(['node',__DIR__.'/payroll-modal-browser.cjs'],[0=>['pipe','r'],1=>STDOUT,2=>STDERR],$bp,dirname(__DIR__),$environment);
 if(!is_resource($process)||proc_close($process)!==0)throw new RuntimeException('Payroll modal browser checks failed');
}

if(getenv('HRMS_PROMOTION_SEARCH_BROWSER')==='1'){
 $process=proc_open(['node',__DIR__.'/promotion-search-browser.cjs'],[0=>['pipe','r'],1=>STDOUT,2=>STDERR],$bp,dirname(__DIR__),$environment);
 if(!is_resource($process)||proc_close($process)!==0)throw new RuntimeException('Promotion search browser checks failed');
}
