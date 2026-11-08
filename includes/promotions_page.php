<?php
require_once __DIR__.'/../includes/auth_check.php';checkRole(['admin','hr']);
require_once __DIR__.'/sidebar.php';require_once __DIR__.'/promotions.php';
ensurePromotionSchema($conn);ensureActivitySchema($conn);$_SESSION['promotion_csrf']??=bin2hex(random_bytes(32));
$role=$_SESSION['role'];$userId=(int)$_SESSION['user_id'];$error='';$notice=(string)($_SESSION['promotion_notice']??'');unset($_SESSION['promotion_notice']);
$filter=in_array($_GET['status']??'all',['all','pending','approved','rejected'],true)?($_GET['status']??'all'):'all';
$search=$role==='hr'&&is_string($_GET['q']??null)?trim($_GET['q']):'';
$postText=static fn($key)=>is_string($_POST[$key]??null)?$_POST[$key]:'';
if($_SERVER['REQUEST_METHOD']==='POST'){
 try{
  if(!is_string($_POST['csrf_token']??null)||!hash_equals($_SESSION['promotion_csrf'],$_POST['csrf_token'])){http_response_code(400);throw new InvalidArgumentException('Reload the promotions page and try again.');}
  if($role==='admin'){suggestPromotion($conn,$userId,(int)($_POST['employee_id']??0),$postText('proposed_position'),$postText('reason'));$_SESSION['promotion_notice']='Promotion suggestion sent to Human Resource.';}
  else{$changed=decidePromotion($conn,$userId,(int)($_POST['id']??0),$postText('decision'),$postText('decision_notes'));$_SESSION['promotion_notice']=$changed?'Promotion decision saved. The employee and suggesting administrator have been notified.':'This suggestion was already decided. No position was changed.';}
  $query=$role==='hr'?http_build_query(['status'=>$filter,'q'=>$search]):'';
  header('Location: '.route($role.'.promotions').($query!==''?'?'.$query:''));exit;
 }catch(InvalidArgumentException $e){$error=$e->getMessage();}catch(Throwable $e){$error='Unable to save. Please try again.';}
}
$sql="SELECT p.*,u.full_name,u.employee_id AS staff_number,a.full_name AS admin_name,h.full_name AS hr_name FROM promotion_suggestions p JOIN users u ON u.id=p.employee_id JOIN users a ON a.id=p.suggested_by LEFT JOIN users h ON h.id=p.decided_by WHERE 1=1";
$params=[];$types='';
if($role==='admin'){$sql.=' AND p.suggested_by=?';$params[]=$userId;$types.='i';}
if($filter!=='all'){$sql.=' AND p.status=?';$params[]=$filter;$types.='s';}
if($search!==''){
 $columns=['u.full_name','u.employee_id','p.current_position','p.proposed_position','a.full_name','p.reason','p.decision_notes'];
 $sql.=' AND ('.implode(' OR ',array_map(static fn($column)=>$column." LIKE ? ESCAPE '!'",$columns)).')';
 $pattern='%'.str_replace(['!','%','_'],['!!','!%','!_'],$search).'%';
 foreach($columns as $_){$params[]=$pattern;$types.='s';}
}
$sql.=' ORDER BY p.id DESC';$statement=$conn->prepare($sql);if($params)$statement->bind_param($types,...$params);$statement->execute();$rows=$statement->get_result();
$staff=$role==='admin'?$conn->query("SELECT id,full_name,employee_id,job_title,role FROM users WHERE role IN ('employee','admin') AND status='active' ORDER BY full_name")->fetch_all(MYSQLI_ASSOC):[];
$h=static fn($v)=>htmlspecialchars(is_scalar($v)?(string)$v:'',ENT_QUOTES,'UTF-8');
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Promotion Suggestions</title><link rel="stylesheet" href="<?= $h(app_url('hr/hr_leave_requests.css')) ?>"><link rel="stylesheet" href="<?= $h(app_url('hr/promotions.css').'?v='.filemtime(__DIR__.'/../hr/promotions.css')) ?>"></head><body>
<?php renderSidebar($role,$role.'.promotions'); ?><main class="main promotions-main"><header class="topbar"><div><h1><i class="fa-solid fa-arrow-trend-up" aria-hidden="true"></i> Promotions</h1><p><?= $role==='admin'?'Recommend employees and IT administrators for promotion. Human Resource makes the final decision.':'Review administrators’ suggestions and approve or reject promotions.' ?></p></div></header>
<?php if($notice): ?><p class="leave-notice" role="status"><?= $h($notice) ?></p><?php endif ?><?php if($error): ?><p class="leave-notice" role="alert"><?= $h($error) ?></p><?php endif ?>
<?php if($role==='admin'): ?><section class="promotion-panel"><h2>Suggest a promotion</h2><form method="post"><input type="hidden" name="csrf_token" value="<?= $h($_SESSION['promotion_csrf']) ?>"><?php require __DIR__.'/promotion_employee_search.php'; ?><label for="promotion-position">Proposed position</label><input id="promotion-position" name="proposed_position" maxlength="120" required value="<?= $h($_POST['proposed_position']??'') ?>"><label for="promotion-reason">Reason and supporting details</label><textarea id="promotion-reason" name="reason" rows="4" maxlength="3000" required><?= $h($_POST['reason']??'') ?></textarea><button type="submit">Send suggestion to HR</button></form></section><?php endif ?>
<section class="promotion-panel"><div class="leave-panel-header"><h2><?= $role==='admin'?'My suggestions':'All promotion suggestions' ?></h2><form method="get" class="leave-filter<?= $role==='hr'?' promotion-search-form':'' ?>"><?php if($role==='hr'): ?><div class="promotion-search-field"><label for="promotion-search">Search suggestions</label><input type="search" id="promotion-search" name="q" value="<?= $h($search) ?>" placeholder="Employee, ID, position, admin or notes"></div><?php endif ?><label for="promotion-filter">Status</label><select id="promotion-filter" name="status"><?php foreach(['all','pending','approved','rejected'] as $status): ?><option <?= $status===$filter?'selected':'' ?>><?= $status ?></option><?php endforeach ?></select><button type="submit"><?php if($role==='hr'): ?><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Search<?php else: ?>Filter<?php endif ?></button><?php if($role==='hr'&&($search!==''||$filter!=='all')): ?><a class="promotion-clear" href="<?= $h(route('hr.promotions')) ?>"><i class="fa-solid fa-filter-circle-xmark" aria-hidden="true"></i> Clear</a><?php endif ?></form></div>
<?php if($role==='hr'): ?><p class="promotion-result-count" role="status"><?= (int)$rows->num_rows ?> suggestion(s) found<?= $search!==''?' for “'.$h($search).'”':'' ?>.</p><?php endif ?>
<?php if(!$rows->num_rows): ?><p>No promotion suggestions match your filters.</p><?php endif ?>
<?php while($row=$rows->fetch_assoc()): ?><article class="promotion-card"><header><h3><?= $h($row['full_name']) ?></h3><span class="leave-status <?= $h($row['status']) ?>"><?= $h(ucfirst($row['status'])) ?></span></header><p><?= $h($row['staff_number']) ?> · <?= $h($row['current_position']) ?> → <strong><?= $h($row['proposed_position']) ?></strong></p><p>Suggested by <?= $h($row['admin_name']) ?> · <?= $h($row['created_at']) ?></p><p class="promotion-notes"><?= nl2br($h($row['reason'])) ?></p>
<?php if($row['status']==='pending'&&$role==='hr'): ?><form method="post"><input type="hidden" name="csrf_token" value="<?= $h($_SESSION['promotion_csrf']) ?>"><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><label for="decision-<?= (int)$row['id'] ?>">HR decision notes</label><textarea id="decision-<?= (int)$row['id'] ?>" name="decision_notes" rows="3" maxlength="3000" required></textarea><div class="promotion-actions"><button type="submit" name="decision" value="approved">Approve promotion</button><button type="submit" name="decision" value="rejected" class="secondary">Reject promotion</button></div><small>Approval updates the employee’s position. Account access role is unchanged.</small></form><?php elseif($row['status']!=='pending'): ?><p>Decided by <?= $h($row['hr_name']??'Human Resource') ?> · <?= $h($row['decided_at']) ?></p><p class="promotion-notes"><?= nl2br($h($row['decision_notes'])) ?></p><?php endif ?></article><?php endwhile ?></section></main><?php if($role==='admin'): ?><script src="<?= $h(app_url('admin/promotion_employee_search.js').'?v='.filemtime(__DIR__.'/../admin/promotion_employee_search.js')) ?>" defer></script><?php endif ?></body></html>
