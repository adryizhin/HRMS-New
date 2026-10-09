(function () {
    const list = document.getElementById('online-users-list');
    const script = document.currentScript || document.querySelector('script[src$="online_users.js"]');
    if (!list || !script) return;
    const url = new URL('online_users.php', script.src);
    let pending = false;
    async function refresh() {
        if (pending || document.hidden) return;
        pending = true;
        try {
            const response = await fetch(url, {credentials:'same-origin', cache:'no-store'});
            if (response.ok && !response.redirected) list.innerHTML = await response.text();
            else list.replaceChildren();
        } catch (_) { list.replaceChildren(); }
        finally { pending = false; }
    }
    setInterval(refresh, 30000);
    document.addEventListener('visibilitychange', refresh);
})();
