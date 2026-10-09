(function () {
    const box = document.getElementById('chatBox');
    const body = document.getElementById('chatBody');
    const input = document.getElementById('chatInput');
    const send = document.getElementById('chatSend');
    const error = document.getElementById('chatError');
    const launcher = document.getElementById('chatAvatar');
    const script = document.currentScript;
    const api = new URL('../chat/', script.src);
    let recipient = null, recipientName = '', sending = false, loading = false;
    let previousFocus = null, lastHtml = null, version = 0;
    function emptyState(title, subtitle) {
        const state = document.createElement('div'); state.className = 'chat-empty';
        const avatar = document.createElement('span'); avatar.className = 'chat-empty-avatar';
        avatar.textContent = document.getElementById('chatContactAvatar').textContent;
        const heading = document.createElement('strong'); heading.textContent = title;
        const text = document.createElement('p'); text.textContent = subtitle;
        state.append(avatar, heading, text); body.replaceChildren(state);
    }
    function showError(message) { error.textContent = message; error.hidden = !message; }
    function resizeInput() {
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 112) + 'px';
        send.disabled = !recipient || sending || !input.value.trim();
    }
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
            const response = await fetch(new URL('fetch_messages.php?receiver_id=' + selected, api), {credentials:'same-origin', cache:'no-store'});
            if (!response.ok) throw new Error('Unable to load messages.');
            const html = await response.text();
            if (selectedVersion !== version || box.hidden) return;
            const atBottom = body.scrollHeight - body.scrollTop - body.clientHeight < 60;
            const oldScroll = body.scrollTop;
            if (lastHtml !== html) {
                if (html.trim()) { body.innerHTML = html; addMessageAvatars(); }
                else emptyState('Say hello to ' + recipientName, 'Start your conversation with a message below.');
                lastHtml = html;
                body.scrollTop = scroll || atBottom ? body.scrollHeight : oldScroll;
            } else if (scroll) body.scrollTop = body.scrollHeight;
        } catch (_) {
            if (selectedVersion === version && !box.hidden) showError('Messages could not load. We will try again shortly.');
        } finally { loading = false; if (selectedVersion !== version && !box.hidden) loadMessages(true); }
    }
    window.startChat = function (id, name) {
        const changed = recipient !== id;
        if (changed) { input.value = ''; lastHtml = null; version++; }
        recipient = id; recipientName = name;
        document.getElementById('chatContactName').textContent = name;
        const contact = [...document.querySelectorAll('.contact-row')].find(row=>Number(row.dataset.contactId) === id);
        const parts = name.trim().split(/\s+/);
        document.getElementById('chatContactAvatar').textContent = contact?.querySelector('.contact-avatar')?.textContent || (parts[0]?.[0] || '?') + (parts.length > 1 ? parts.at(-1)[0] : '');
        document.getElementById('chatContactRole').textContent = contact?.querySelector('.contact-subtitle')?.textContent || 'Direct message';
        input.disabled = false; showError('');
        if (changed || lastHtml === null) emptyState('Loading conversation…', 'Your messages will appear here.');
        window.openChat(); resizeInput(); input.focus();
    };
    window.openChat = function () {
        if (box.hidden) previousFocus = document.activeElement;
        box.hidden = false; launcher.setAttribute('aria-expanded','true');
        document.getElementById('chatNotif').style.display = 'none';
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
        if (!selected || !message || sending) return;
        sending = true; showError(''); resizeInput();
        try {
            const response = await fetch(new URL('send_message.php', api), {
                method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/x-www-form-urlencoded'},
                body:new URLSearchParams({receiver_id:String(selected), message})
            });
            if (!response.ok || (await response.text()).trim() !== 'success') throw new Error('Send failed');
            if (version === selectedVersion) {
                if (input.value.trim() === message) input.value = '';
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
    setInterval(()=>{if(!document.hidden) loadMessages()},3000);
})();
