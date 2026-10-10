<?php
$h=static fn($v)=>htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');
?>
<div class="activity-desktop-tools"><span class="activity-role"><?= $h($role==='hr'?'Human Resource':ucfirst($role)) ?></span><?= $bell ?></div>
<section id="activity-panel" class="activity-panel" role="dialog" aria-modal="false" aria-labelledby="activity-title" data-url="<?= $h(app_url('notifications/activity.php')) ?>" data-token="<?= $h($_SESSION['activity_csrf']) ?>" hidden>
<header><h2 id="activity-title">Notifications</h2><button type="button" id="activity-close" aria-label="Close notifications"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button></header>
<p class="activity-help">Unread updates and items that need your attention.</p>
<div id="activity-items"></div>
<p id="activity-error" role="status" hidden></p>
<button type="button" id="activity-read" hidden>Mark displayed updates as read</button>
</section>
<script type="application/json" id="activity-initial"><?= json_encode($activity,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?></script>
<script src="<?= $h(app_url('includes/activity.js').'?v='.filemtime(__DIR__.'/activity.js')) ?>" defer></script>
