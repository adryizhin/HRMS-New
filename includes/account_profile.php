<?php
declare(strict_types=1);
function ensureAccountSchema(mysqli $conn): void {
    static $done=false;if($done)return;
    $columns=array_column($conn->query('SHOW COLUMNS FROM users')->fetch_all(MYSQLI_ASSOC),'Field');
    foreach(['profile_picture'=>'VARCHAR(80) NULL','phone_number'=>'VARCHAR(40) NULL'] as $column=>$definition) if(!in_array($column,$columns,true))$conn->query("ALTER TABLE users ADD COLUMN $column $definition");
    $done=true;
}
function profilePhotoUrl(array $user): string { return empty($user['profile_picture']) ? '' : app_url('account/photo.php?id='.(int)$user['id']); }
function validateProfileUpload(array $file): string {
    if($file['error']!==UPLOAD_ERR_OK||$file['size']>2*1024*1024||!is_uploaded_file($file['tmp_name']))throw new InvalidArgumentException('Choose a JPG, PNG or WebP photo up to 2 MB.');
    $mime=(new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);$types=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
    $dimensions=getimagesize($file['tmp_name']);if(!isset($types[$mime])||!$dimensions||$dimensions[0]>5000||$dimensions[1]>5000)throw new InvalidArgumentException('Choose a valid JPG, PNG or WebP photo with dimensions up to 5,000 pixels.');
    return $types[$mime];
}
