<?php
require_once __DIR__.'/../includes/auth_check.php';checkRole(['admin','hr','employee']);
require_once __DIR__.'/../includes/sidebar.php';require_once __DIR__.'/../includes/account_profile.php';ensureAccountSchema($conn);
header('Cache-Control: no-store');$_SESSION['account_csrf']??=bin2hex(random_bytes(32));$id=(int)$_SESSION['user_id'];$error='';$newFile=null;
$stmt=$conn->prepare('SELECT * FROM users WHERE id=?');$stmt->bind_param('i',$id);$stmt->execute();$user=$stmt->get_result()->fetch_assoc();
if($_SERVER['REQUEST_METHOD']==='POST'){
 try{
  if(!hash_equals($_SESSION['account_csrf'],(string)($_POST['csrf_token']??'')))throw new InvalidArgumentException('Reload settings and try again.');
  $name=trim((string)($_POST['full_name']??''));$email=trim((string)($_POST['email']??''));$phone=trim((string)($_POST['phone_number']??''));$password=(string)($_POST['new_password']??'');
  if($name===''||strlen($name)>255||!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($email)>191||($phone!==''&&!preg_match('/\A[0-9+(). -]{3,40}\z/',$phone)))throw new InvalidArgumentException('Enter a name, valid email and valid phone number.');
  if($password!==''&&(strlen($password)<12||strlen($password)>72||$password!==(string)($_POST['confirm_password']??'')))throw new InvalidArgumentException('Use matching passwords of 12–72 bytes.');
  $conn->begin_transaction();$stmt=$conn->prepare('SELECT * FROM users WHERE id=? FOR UPDATE');$stmt->bind_param('i',$id);$stmt->execute();$user=$stmt->get_result()->fetch_assoc();
  $sensitive=$password!==''||strcasecmp($email,$user['email'])!==0;
  if($sensitive&&!password_verify((string)($_POST['current_password']??''),$user['password']))throw new InvalidArgumentException('Enter your current password to change email or password.');
  $photo=$user['profile_picture'];$upload=$_FILES['profile_picture']??null;
  if($upload&&$upload['error']!==UPLOAD_ERR_NO_FILE){$ext=validateProfileUpload($upload);$newFile=bin2hex(random_bytes(16)).'.'.$ext;if(!move_uploaded_file($upload['tmp_name'],__DIR__.'/../uploads/profile_photos/'.$newFile))throw new RuntimeException('Photo upload failed.');$photo=$newFile;}
  elseif(!empty($_POST['remove_photo']))$photo=null;
  $hash=$password!==''?password_hash($password,PASSWORD_DEFAULT):$user['password'];
  $stmt=$conn->prepare('UPDATE users SET full_name=?,email=?,phone_number=?,profile_picture=?,password=? WHERE id=?');$stmt->bind_param('sssssi',$name,$email,$phone,$photo,$hash,$id);$stmt->execute();
  if($sensitive){$stmt=$conn->prepare("UPDATE login_logs SET logout_time=NOW(),logout_reason='account_updated' WHERE user_id=? AND session_token<>? AND logout_time IS NULL");$stmt->bind_param('is',$id,$_SESSION['session_token']);$stmt->execute();}
  $conn->commit();$_SESSION['name']=$name;
  if($user['profile_picture']&&$user['profile_picture']!==$photo&&preg_match('/\A[a-f0-9]{32}\.(jpg|png|webp)\z/',$user['profile_picture'])){$old=__DIR__.'/../uploads/profile_photos/'.$user['profile_picture'];if(is_file($old))unlink($old);}
  $_SESSION['account_notice']='Account settings saved.';header('Location: '.route('account.settings'));exit;
 }catch(Throwable $e){$conn->rollback();if($newFile&&is_file(__DIR__.'/../uploads/profile_photos/'.$newFile))unlink(__DIR__.'/../uploads/profile_photos/'.$newFile);$error=$e instanceof InvalidArgumentException?$e->getMessage():($e instanceof mysqli_sql_exception&&$e->getCode()===1062?'That email is already in use.':'Unable to save settings. Please try again.');}
}
$notice=(string)($_SESSION['account_notice']??'');unset($_SESSION['account_notice']);$h=static fn($v)=>htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');$role=$_SESSION['role'];
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Account Settings</title><link rel="stylesheet" href="<?= $h(app_url('admin/dashboard.css')) ?>"><link rel="stylesheet" href="<?= $h(app_url('account/settings.css')) ?>"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"></head><body>
<?php renderSidebar($role,$role.'.settings'); ?><main class="main settings-main"><header><h1>Account settings</h1><p>Manage your profile and sign-in details.</p></header>
<?php if($notice): ?><div class="settings-notice" role="status"><?= $h($notice) ?></div><?php endif ?><?php if($error): ?><div class="settings-notice" role="alert"><?= $h($error) ?></div><?php endif ?>
<form method="post" enctype="multipart/form-data" class="settings-form"><input type="hidden" name="csrf_token" value="<?= $h($_SESSION['account_csrf']) ?>">
<section class="settings-card"><h2><i class="fa-regular fa-user" aria-hidden="true"></i> Profile</h2><div class="settings-photo-row"><span class="settings-avatar"><?php if($user['profile_picture']): ?><img src="<?= $h(profilePhotoUrl($user)) ?>" alt="Your profile picture"><?php else: ?><?= $h(mb_strtoupper(mb_substr($user['full_name'],0,1))) ?><?php endif ?></span><div><label for="profile_picture">Profile picture</label><input type="file" id="profile_picture" name="profile_picture" accept="image/jpeg,image/png,image/webp"><small>JPG, PNG or WebP, up to 2 MB. Preview, zoom and align your photo before saving.</small><?php if($user['profile_picture']): ?><button type="button" id="adjustCurrentPhoto" class="photo-secondary">Adjust current photo</button><?php endif ?><?php if($user['profile_picture']): ?><label class="settings-check"><input type="checkbox" name="remove_photo" value="1"> Remove current photo</label><?php endif ?></div></div>
<p id="profilePhotoError" class="photo-error" role="alert" hidden></p>
<div id="profilePhotoEditor" class="photo-editor" hidden>
  <div class="photo-editor-heading"><div><h3>Adjust profile picture</h3><p>Drag the image or use the controls to align it. The circle shows your avatar crop.</p></div></div>
  <div class="photo-editor-layout">
    <div class="photo-preview-wrap"><canvas id="profileCropPreview" width="512" height="512" aria-label="Profile photo crop preview"></canvas><span class="photo-preview-caption">Live preview</span></div>
    <div class="photo-controls">
      <label for="photoZoom">Zoom <output id="photoZoomValue" for="photoZoom">100%</output></label><input type="range" id="photoZoom" min="1" max="3" step="0.05" value="1">
      <label for="photoHorizontal">Horizontal alignment</label><input type="range" id="photoHorizontal" min="0" max="100" value="50"><div class="photo-range-labels"><span>Left</span><span>Right</span></div>
      <label for="photoVertical">Vertical alignment</label><input type="range" id="photoVertical" min="0" max="100" value="50"><div class="photo-range-labels"><span>Top</span><span>Bottom</span></div>
      <div class="photo-editor-actions"><button type="button" id="photoReset" class="photo-secondary">Reset alignment</button><button type="button" id="photoCancel" class="photo-secondary">Cancel photo change</button></div>
      <small>Your adjusted photo is saved as a 512 × 512 image when you select Save changes.</small>
    </div>
  </div>
</div>
<div class="settings-grid"><div><label for="full_name">Full name</label><input id="full_name" name="full_name" value="<?= $h($user['full_name']) ?>" maxlength="255" required autocomplete="name"></div><div><label for="phone_number">Phone number (optional)</label><input id="phone_number" name="phone_number" type="tel" value="<?= $h($user['phone_number']) ?>" maxlength="40" autocomplete="tel"></div></div></section>
<section class="settings-card"><h2><i class="fa-solid fa-lock" aria-hidden="true"></i> Sign-in details</h2><label for="email">Email address</label><input id="email" name="email" type="email" value="<?= $h($user['email']) ?>" maxlength="191" required autocomplete="email"><p class="settings-help">Your current password is required when changing email or password.</p>
<label for="current_password">Current password</label><div class="settings-password"><input id="current_password" name="current_password" type="password" autocomplete="current-password"><button type="button" data-password-target="current_password" aria-label="Show password" aria-pressed="false"><i class="fa-regular fa-eye" aria-hidden="true"></i></button></div>
<div class="settings-grid"><div><label for="new_password">New password (optional)</label><input id="new_password" name="new_password" type="password" minlength="12" maxlength="72" autocomplete="new-password"></div><div><label for="confirm_password">Confirm new password</label><input id="confirm_password" name="confirm_password" type="password" autocomplete="new-password"></div></div><small>Use 12–72 bytes. Other sessions are signed out when email or password changes.</small></section>
<section class="settings-card"><h2><i class="fa-regular fa-id-badge" aria-hidden="true"></i> Employment details</h2><dl><dt>Role</dt><dd><?= $h($role==='hr'?'Human Resource':ucfirst($role)) ?></dd><dt>Employee ID</dt><dd><?= $h($user['employee_id']?:'Not assigned') ?></dd><dt>Work shift</dt><dd><?= $h(ucfirst($user['work_shift'])) ?></dd></dl><small>Contact your administrator to update employment details.</small></section><button class="settings-save" type="submit"><i class="fa-solid fa-check" aria-hidden="true"></i> Save changes</button></form></main><script src="<?= $h(app_url('login/password_toggle.js')) ?>" defer></script><script src="<?= $h(app_url('account/profile_photo.js')) ?>" defer></script></body></html>
