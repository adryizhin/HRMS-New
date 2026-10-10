(function () {
    const box = document.getElementById('chatBox');
    const body = document.getElementById('chatBody');
    const input = document.getElementById('chatInput');
    const send = document.getElementById('chatSend');
    const error = document.getElementById('chatError');
    const launcher = document.getElementById('chatAvatar');
    const attachment = document.getElementById('chatAttachment');
    const attachButton = document.getElementById('chatAttach');
    const attachmentPreview = document.getElementById('chatAttachmentPreview');
    const script = document.currentScript;
    const api = new URL('../chat/', script.src);
    let recipient = null, recipientName = '', sending = false, loading = false;
    let previousFocus = null, lastHtml = null, version = 0;
    let unreadSenders = [], checkingUnread = false;
    function emptyState(title, subtitle) {
        const state = document.createElement('div'); state.className = 'chat-empty';
        const avatar = document.createElement('span'); avatar.className = 'chat-empty-avatar';
        avatar.textContent = initials(recipientName || 'Messages');
        const headerPhoto = document.getElementById('chatContactAvatar').querySelector('img');
        setAvatarPhoto(avatar, headerPhoto?.src);
        const heading = document.createElement('strong'); heading.textContent = title;
        const text = document.createElement('p'); text.textContent = subtitle;
        state.append(avatar, heading, text); body.replaceChildren(state);
    }
    function showError(message) { error.textContent = message; error.hidden = !message; }
    function resizeInput() {
        input.style.height = 'auto';
        const maximum = parseFloat(getComputedStyle(input).maxHeight) || 112;
        input.style.height = Math.min(input.scrollHeight, maximum) + 'px';
        send.disabled = !recipient || sending || (!input.value.trim() && !attachment.files.length);
    }
    function fitViewport() {
        const viewport = window.visualViewport;
        const height = viewport?.height || window.innerHeight;
        const offset = viewport ? Math.max(0, window.innerHeight - viewport.height - viewport.offsetTop) : 0;
        box.style.setProperty('--chat-visible-height', height + 'px');
        box.style.setProperty('--chat-keyboard-offset', offset + 'px');
        box.classList.toggle('chat-compact', height < 500);
        if (!box.hidden) resizeInput();
    }
    window.addEventListener('resize', fitViewport);
    window.visualViewport?.addEventListener('resize', fitViewport);
    window.visualViewport?.addEventListener('scroll', fitViewport);
    fitViewport();
    function clearAttachment() {attachment.value='';attachmentPreview.hidden=true;resizeInput();}
    function setAvatarPhoto(avatar, url) {
        if (!url) return;
        const fallback = avatar.textContent;
        const img = document.createElement('img');
        img.alt = '';
        img.addEventListener('error', () => { if (img.parentNode === avatar) avatar.textContent = fallback; });
        img.src = url;
        avatar.replaceChildren(img);
    }
    attachButton.addEventListener('click',()=>attachment.click());
    attachment.addEventListener('change',()=>{
        const file=attachment.files[0];if(!file){clearAttachment();return;}
        if(file.size>Number(box.dataset.uploadLimit)){showError('File is too large. Maximum '+Math.round(Number(box.dataset.uploadLimit)/1048576)+' MB.');clearAttachment();return;}
        document.getElementById('chatAttachmentName').textContent=file.name;attachmentPreview.hidden=false;showError('');resizeInput();
    });
    document.getElementById('chatAttachmentRemove').addEventListener('click',clearAttachment);
    function initials(name) {
        const words = name.trim().split(/\s+/).filter(Boolean);
        if (!words.length) return '?';
        return (Array.from(words[0])[0] + (words.length > 1 ? Array.from(words.at(-1))[0] : '')).toLocaleUpperCase();
    }
    function addMessageAvatars() {
        body.querySelectorAll('.msg').forEach(message => {
            const outgoing = message.classList.contains('user');
            const name = outgoing ? (box.dataset.userName || 'You') : recipientName;
            const row = document.createElement('div');
            row.className = 'chat-message-row ' + (outgoing ? 'outgoing' : 'incoming');
            const avatar = document.createElement('span');
            avatar.className = 'chat-message-avatar';
            avatar.textContent = initials(name);
            const contact=[...document.querySelectorAll('.contact-row')].find(row=>Number(row.dataset.contactId)===recipient);
            setAvatarPhoto(avatar,outgoing?box.dataset.userPhoto:contact?.dataset.contactPhoto);
            avatar.title = name;
            avatar.setAttribute('role', 'img');
            avatar.setAttribute('aria-label', outgoing ? name + ' (you)' : name);
            message.before(row);
            row.append(avatar, message);
        });
    }
    async function loadMessages(scroll = false) {
        if (!recipient || loading || box.hidden) return;
        const selected = recipient, selectedVersion = version;
        loading = true;
        try {
            const response = await fetch(new URL('fetch_messages.php?read=1&receiver_id=' + selected, api), {credentials:'same-origin', cache:'no-store'});
            if (!response.ok || response.redirected) throw new Error('Unable to load messages.');
            const html = await response.text();
            if (selectedVersion !== version || box.hidden) return;
            const atBottom = body.scrollHeight - body.scrollTop - body.clientHeight < 60;
            const oldScroll = body.scrollTop;
            if (lastHtml !== html) {
                if (html.trim()) { body.innerHTML = html; addMessageAvatars(); }
                else emptyState('Say hello to ' + recipientName, 'Start your conversation with a message below.');
                lastHtml = html; refreshUnread();
                body.scrollTop = scroll || atBottom ? body.scrollHeight : oldScroll;
            } else if (scroll) body.scrollTop = body.scrollHeight;
        } catch (_) {
            if (selectedVersion === version && !box.hidden) showError('Messages could not load. We will try again shortly.');
        } finally { loading = false; if (selectedVersion !== version && !box.hidden) loadMessages(true); }
    }
    window.startChat = function (id, name) {
        const changed = recipient !== id;
        if (changed) { input.value = ''; clearAttachment(); lastHtml = null; version++; }
        recipient = id; recipientName = name;
        document.getElementById('chatContactName').textContent = name;
        const contact = [...document.querySelectorAll('.contact-row')].find(row=>Number(row.dataset.contactId) === id);
        const parts = name.trim().split(/\s+/);
        document.getElementById('chatContactAvatar').textContent = initials(name);
        document.getElementById('chatContactRole').textContent = contact?.querySelector('.contact-subtitle')?.textContent || 'Direct message';
        setAvatarPhoto(document.getElementById('chatContactAvatar'),contact?.dataset.contactPhoto);
        attachButton.disabled=false; input.disabled = false; showError('');
        if (changed || lastHtml === null) emptyState('Loading conversation…', 'Your messages will appear here.');
        window.openChat(); resizeInput(); input.focus();
    };
    window.openChat = function () {
        if (box.hidden) previousFocus = document.activeElement;
        box.hidden = false; launcher.setAttribute('aria-expanded','true'); fitViewport();
        if (!recipient && unreadSenders.length) { window.startChat(Number(unreadSenders[0].id), unreadSenders[0].full_name); return; }
        if (recipient) { loadMessages(true); input.focus(); }
        else { emptyState('Your conversations', 'Choose someone from the Messages list to start chatting.'); box.querySelector('[aria-label="Close chat"]').focus(); }
    };
    window.closeChat = function () {
        box.hidden = true; launcher.setAttribute('aria-expanded','false');
        (previousFocus && previousFocus.isConnected ? previousFocus : launcher).focus();
    };
    async function sendMessage(event) {
        event.preventDefault();
        const message = input.value.trim(), selected = recipient, selectedVersion = version;
        const selectedFile=attachment.files[0];
        if (!selected || (!message && !selectedFile) || sending) return;
        sending = true; showError(''); resizeInput();
        try {
            const form = new FormData();form.append('receiver_id',String(selected));form.append('message',message);form.append('csrf_token',box.dataset.uploadToken);if(selectedFile)form.append('attachment',selectedFile);
            const response = await fetch(new URL('send_message.php', api), {method:'POST',credentials:'same-origin',body:form});
            if (!response.ok || (await response.text()).trim() !== 'success') throw new Error('Send failed');
            if (version === selectedVersion) {
                if (input.value.trim() === message) input.value = '';
                if(attachment.files[0]===selectedFile)clearAttachment();
                lastHtml = null; await loadMessages(true);
            }
        } catch (_) { if (version === selectedVersion) showError('Message was not sent. Please try again.'); }
        finally { sending = false; resizeInput(); }
    }
    document.getElementById('chatComposer').addEventListener('submit', sendMessage);
    input.addEventListener('input', resizeInput);
    input.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) { event.preventDefault(); document.getElementById('chatComposer').requestSubmit(); }
    });
    box.addEventListener('keydown', event=>{if(event.key==='Escape') window.closeChat()});
    async function refreshUnread() {
        if (checkingUnread || document.hidden) return;
        checkingUnread = true;
        try {
            const response = await fetch(new URL('unread_messages.php', api), {credentials:'same-origin',cache:'no-store'});
            if (!response.ok || response.redirected) throw new Error('Unavailable');
            const data = await response.json(); unreadSenders = data.senders;
            const badge = document.getElementById('chatNotif');
            badge.textContent = data.count > 99 ? '99+' : String(data.count);
            badge.hidden = data.count === 0;
            launcher.setAttribute('aria-label', data.count ? 'Open messages, ' + data.count + ' unread' : 'Open messages');
        } catch (_) { document.getElementById('chatNotif').hidden=true; }
        finally { checkingUnread=false; }
    }
    refreshUnread();
    setInterval(refreshUnread,5000);
    document.addEventListener('visibilitychange', refreshUnread);
    setInterval(()=>{if(!document.hidden) loadMessages()},3000);
})();
