<?php
declare(strict_types=1);
function ensureChatAttachments(mysqli $conn): void {
    static $done=false;if($done)return;
    $columns=array_column($conn->query('SHOW COLUMNS FROM chat_messages')->fetch_all(MYSQLI_ASSOC),'Field');
    foreach(['attachment_path'=>'VARCHAR(80) NULL','attachment_name'=>'VARCHAR(255) NULL','attachment_size'=>'INT NULL'] as $column=>$type)if(!in_array($column,$columns,true))$conn->query("ALTER TABLE chat_messages ADD COLUMN $column $type");
    $done=true;
}
function chatUploadLimit(): int {
    $parse=static function(string $size):int{$unit=strtolower(substr($size,-1));return (int)$size*(['g'=>1073741824,'m'=>1048576,'k'=>1024][$unit]??1);};
    return min(10*1024*1024,$parse(ini_get('upload_max_filesize')),$parse(ini_get('post_max_size')));
}
function validateChatAttachment(array $file): string {
    if($file['error']!==UPLOAD_ERR_OK||$file['size']>chatUploadLimit()||!is_uploaded_file($file['tmp_name']))throw new InvalidArgumentException('File exceeds the upload limit or could not be uploaded.');
    $ext=strtolower(pathinfo($file['name'],PATHINFO_EXTENSION));$mime=(new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $types=['pdf'=>['application/pdf'],'jpg'=>['image/jpeg'],'jpeg'=>['image/jpeg'],'png'=>['image/png'],'webp'=>['image/webp'],'txt'=>['text/plain'],'csv'=>['text/plain','text/csv','application/csv'],'docx'=>['application/zip','application/vnd.openxmlformats-officedocument.wordprocessingml.document'],'xlsx'=>['application/zip','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],'pptx'=>['application/zip','application/vnd.openxmlformats-officedocument.presentationml.presentation']];
    if(!isset($types[$ext])||!in_array($mime,$types[$ext],true))throw new InvalidArgumentException('Supported files: PDF, Office documents, text, CSV, JPG, PNG and WebP.');
    if(in_array($ext,['docx','xlsx','pptx'],true)){$zip=new ZipArchive();$folder=['docx'=>'word/','xlsx'=>'xl/','pptx'=>'ppt/'][$ext];if($zip->open($file['tmp_name'])!==true)throw new InvalidArgumentException('Invalid Office document.');$valid=$zip->locateName('[Content_Types].xml')!==false; $found=false;for($i=0;$i<$zip->numFiles;$i++)if(str_starts_with($zip->getNameIndex($i),$folder)){$found=true;break;}$zip->close();if(!$valid||!$found)throw new InvalidArgumentException('Invalid Office document.');}
    return $ext;
}

/** Supported raster images can be previewed; documents always download. */
function chatAttachmentImageMime(string $path): ?string {
    if (!preg_match('/\A[a-f0-9]{32}\.(jpg|jpeg|png|webp)\z/', $path, $match)) return null;
    return ['jpg'=>'image/jpeg', 'jpeg'=>'image/jpeg', 'png'=>'image/png', 'webp'=>'image/webp'][$match[1]];
}
