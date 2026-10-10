<?php
require_once __DIR__.'/account_profile.php';require_once __DIR__.'/chat_attachments.php';ensureAccountSchema($conn);
$_SESSION['chat_upload_csrf']??=bin2hex(random_bytes(32));
$photoStmt=$conn->prepare('SELECT id,profile_picture FROM users WHERE id=?');$photoStmt->bind_param('i',$_SESSION['user_id']);$photoStmt->execute();$chatProfile=$photoStmt->get_result()->fetch_assoc();
?>
<!-- MESSAGING POPUP -->
<button id="chatAvatar" type="button" onclick="openChat()" aria-label="Open messages" aria-controls="chatBox" aria-expanded="false">
  <span id="chatNotif" aria-hidden="true" hidden></span>
  <img src="../images/chat-robot-cutout.png" alt="" class="chat-launcher-image">
</button>
<section id="chatBox" data-upload-token="<?= htmlspecialchars($_SESSION['chat_upload_csrf'],ENT_QUOTES,'UTF-8') ?>" data-upload-limit="<?= chatUploadLimit() ?>" data-user-photo="<?= htmlspecialchars(profilePhotoUrl($chatProfile),ENT_QUOTES,'UTF-8') ?>" data-user-name="<?= htmlspecialchars((string) ($_SESSION['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" role="dialog" aria-modal="false" aria-labelledby="chatContactName" hidden>
  <header class="chat-header">
    <span id="chatContactAvatar" class="chat-contact-avatar" aria-hidden="true">PC</span>
    <div class="chat-contact-heading"><h2 id="chatContactName">Messages</h2><p id="chatContactRole">Choose a contact to get started</p></div>
    <button type="button" class="chat-icon-button" onclick="closeChat()" aria-label="Minimize chat"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14"/></svg></button>
    <button type="button" class="chat-icon-button" onclick="closeChat()" aria-label="Close chat"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg></button>
  </header>
  <div class="chat-body" id="chatBody" role="log" aria-label="Conversation" aria-live="polite" aria-relevant="additions"></div>
  <div id="chatError" class="chat-error" role="alert" hidden></div>
  <div id="chatAttachmentPreview" class="chat-attachment-preview" hidden><span id="chatAttachmentName"></span><button type="button" id="chatAttachmentRemove" aria-label="Remove attachment">&times;</button></div>
  <form id="chatComposer" class="chat-footer">
    <input type="file" id="chatAttachment" accept=".pdf,.docx,.xlsx,.pptx,.txt,.csv,.jpg,.jpeg,.png,.webp" hidden>
    <button id="chatAttach" type="button" aria-label="Attach a file" disabled><svg viewBox="0 0 24 24" width="21" height="21" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m8 12 6-6a3 3 0 0 1 4 4l-8 8a5 5 0 0 1-7-7l9-9"/></svg></button>
    <label class="contacts-sr-only" for="chatInput">Message</label>
    <textarea id="chatInput" rows="1" placeholder="Write a message…" aria-describedby="chatComposerHint" disabled></textarea>
    <button id="chatSend" type="submit" aria-label="Send message" disabled><svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor" aria-hidden="true"><path d="m3 3 19 9-19 9 3-8 9-1-9-1-3-8Z"/></svg></button>
  </form>
  <p id="chatComposerHint" class="chat-composer-hint">Enter to send · Shift + Enter for a new line</p>
</section>
<script src="<?= htmlspecialchars(app_url('admin/chat_box.js') . '?v=' . filemtime(__DIR__ . '/../admin/chat_box.js')); ?>" defer></script>
