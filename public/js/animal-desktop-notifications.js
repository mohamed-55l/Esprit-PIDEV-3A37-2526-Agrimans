/**
 * Notifications « système » via l’API Web Notifications du navigateur (toast Windows / macOS / Linux).
 * Nécessite HTTPS en production et l’autorisation de l’utilisateur (bouton bureau dans la barre).
 */
(function () {
    const pollUrl = document.body && document.body.dataset.animalNotifPoll;
    if (!pollUrl || typeof window.Notification === 'undefined') {
        return;
    }

    const STORAGE_KEY = 'agrimans_animal_notif_since';
    const POLL_MS = 45000;

    let pollTimer = null;

    function getSince() {
        const v = sessionStorage.getItem(STORAGE_KEY);
        return v ? parseInt(v, 10) : 0;
    }

    function setSince(id) {
        sessionStorage.setItem(STORAGE_KEY, String(id));
    }

    function showDesktopToast(item) {
        if (Notification.permission !== 'granted') {
            return;
        }
        const opts = {
            body: item.message,
            tag: 'agrimans-animal-' + item.id,
            data: { link: item.link || '' },
        };
        const n = new Notification(item.title, opts);
        n.onclick = function () {
            window.focus();
            const link = this.data && this.data.link;
            if (link) {
                window.location.href = link.startsWith('http') ? link : window.location.origin + link;
            }
            this.close();
        };
    }

    async function pollOnce() {
        let since = getSince();
        const url = pollUrl + '?since=' + encodeURIComponent(String(since));
        const res = await fetch(url, {
            credentials: 'same-origin',
            headers: { Accept: 'application/json' },
        });
        if (!res.ok) {
            return;
        }
        const data = await res.json();
        const maxId = typeof data.maxId === 'number' ? data.maxId : 0;

        if (since === 0) {
            setSince(maxId);
            return;
        }

        const items = Array.isArray(data.items) ? data.items : [];
        for (const item of items) {
            showDesktopToast(item);
        }

        if (maxId > since) {
            setSince(maxId);
        } else if (items.length > 0) {
            const last = items[items.length - 1].id;
            if (typeof last === 'number' && last > since) {
                setSince(last);
            }
        }
    }

    function startPolling() {
        if (pollTimer) {
            clearInterval(pollTimer);
        }
        pollTimer = setInterval(pollOnce, POLL_MS);
        void pollOnce();
    }

    async function enableFromUserGesture() {
        if (Notification.permission === 'denied') {
            window.alert('Les notifications sont bloquées pour ce site. Autorisez-les dans les paramètres du navigateur (icône cadenas / site).');
            return;
        }
        const perm = await Notification.requestPermission();
        if (perm !== 'granted') {
            return;
        }
        sessionStorage.removeItem(STORAGE_KEY);
        startPolling();
    }

    document.addEventListener('DOMContentLoaded', function () {
        const btn = document.getElementById('agrimans-desktop-notif-btn');
        if (btn) {
            if (Notification.permission === 'denied') {
                btn.style.opacity = '0.4';
                btn.title = 'Notifications bloquées dans le navigateur';
                btn.disabled = true;
                return;
            }
            btn.addEventListener('click', function () {
                void enableFromUserGesture();
            });
        }

        if (Notification.permission === 'granted') {
            startPolling();
        }
    });
})();
