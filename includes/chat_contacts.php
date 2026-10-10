<?php
declare(strict_types=1);
require_once __DIR__.'/account_profile.php';

function renderChatContactGroup(string $title, array $contacts, string $group): void
{
    $h = static fn($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    $label = $group === 'hr' ? 'HR team' : 'Administrator';
    ?>
    <section class="contact-group" aria-labelledby="contacts-<?= $h($group) ?>-heading">
      <div class="contact-group-heading"><h3 id="contacts-<?= $h($group) ?>-heading"><?= $h($title) ?></h3><span class="contact-count" aria-label="<?= count($contacts) ?> contacts"><?= count($contacts) ?></span></div>
      <div class="contact-list">
      <?php foreach ($contacts as $contact):
          $name = trim((string) $contact['full_name']);
          $parts = preg_split('/\s+/u', $name, -1, PREG_SPLIT_NO_EMPTY) ?: [];
          $initials = $parts ? mb_substr($parts[0], 0, 1) . (count($parts) > 1 ? mb_substr($parts[count($parts)-1], 0, 1) : '') : '?';
      ?>
        <button type="button" class="contact-row" data-contact-photo="<?= $h(profilePhotoUrl($contact)) ?>" data-contact-id="<?= (int) $contact['id'] ?>" data-contact-name="<?= $h($name) ?>" aria-label="Chat with <?= $h($name) ?>" aria-pressed="false">
          <span class="contact-avatar contact-avatar-<?= $h($group) ?>" aria-hidden="true"><?php if(profilePhotoUrl($contact)): ?><img src="<?= $h(profilePhotoUrl($contact)) ?>" alt="" width="48" height="48"><?php else: ?><?= $h(mb_strtoupper($initials)) ?><?php endif ?></span>
          <span class="contact-details"><span class="contact-name"><?= $h($name) ?></span><span class="contact-subtitle"><?= $label ?></span></span>
          <span class="contact-message-icon" aria-hidden="true"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M21 11.5a8.5 8.5 0 0 1-8.5 8.5H5l-3 2v-8.5A8.5 8.5 0 0 1 10.5 5H13a8 8 0 0 1 8 6.5Z"/><path d="M7 12h.01M12 12h.01M17 12h.01" stroke-width="3" stroke-linecap="round"/></svg></span>
        </button>
      <?php endforeach ?>
      </div>
      <?php if (!$contacts): ?><p class="contacts-empty">No <?= $group === 'hr' ? 'HR staff' : 'other admins' ?> available.</p><?php endif ?>
    </section>
    <?php
}
