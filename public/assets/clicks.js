(function() {
    const path = location.pathname;
    const parts = path.split('/').filter(Boolean);
    const toolsIndex = parts.indexOf('tools');
    let id = null;

    if (path === '/' || path.endsWith('/index.html')) {
        id = 'home';
    } else if (toolsIndex !== -1 && parts[toolsIndex + 1]) {
        id = parts[toolsIndex + 1];
    }

    if (!id) {
        return;
    }

    const payload = JSON.stringify({ id });
    if (navigator.sendBeacon) {
        navigator.sendBeacon('/api/clicks', new Blob([payload], { type: 'application/json' }));
        return;
    }
    fetch('/api/clicks', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: payload,
        keepalive: true
    });
})();
