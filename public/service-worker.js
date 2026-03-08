// Service Worker for My Doctor Push Notifications

self.addEventListener('install', (event) => {
    console.log('✅ Service Worker installed');
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    console.log('✅ Service Worker activated');
    event.waitUntil(clients.claim());
});

self.addEventListener('push', function(event) {
    console.log('📨 Push notification received');
    
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }

    let data = {};
    
    if (event.data) {
        try {
            data = event.data.json();
        } catch (e) {
            data = {
                title: 'My Doctor',
                body: event.data.text(),
                icon: '/images/logos/applogo.jpg',
                badge: '/images/logos/applogo.jpg'
            };
        }
    }

    const options = {
        body: data.body || 'Time to take your medicine',
        icon: data.icon || '/images/logos/applogo.jpg',
        badge: data.badge || '/images/logos/applogo.jpg',
        vibrate: [200, 100, 200],
        data: data.data || {},
        actions: data.actions || [
            { action: 'mark_taken', title: '✓ Mark Taken' },
            { action: 'snooze', title: '⏰ Snooze 5 min' }
        ],
        requireInteraction: true,
        silent: false
    };

    event.waitUntil(
        self.registration.showNotification(data.title || 'Medicine Reminder', options)
    );
});

self.addEventListener('notificationclick', function(event) {
    console.log('👆 Notification clicked:', event.action);
    event.notification.close();

    const action = event.action;
    const data = event.notification.data;

    if (action === 'mark_taken') {
        if (data && data.reminder_id) {
            // Get the base URL from the notification data or use relative URL
            const baseUrl = data.base_url || '';
            fetch(`${baseUrl}/medicine/reminders/${data.reminder_id}/taken-from-notification`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': data.csrf_token || ''
                }
            });
        }
    } else if (action === 'snooze') {
        if (data && data.reminder_id) {
            // Get the base URL from the notification data or use relative URL
            const baseUrl = data.base_url || '';
            fetch(`${baseUrl}/medicine/reminders/${data.reminder_id}/snooze`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': data.csrf_token || ''
                },
                body: JSON.stringify({ minutes: 5 })
            });
        }
    }

    // For opening the reminders page, use the URL from data or default to relative path
    const urlToOpen = data.url || '/medicine/reminders';
    
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(windowClients => {
            for (let client of windowClients) {
                if (client.url.includes('/medicine/reminders') && 'focus' in client) {
                    return client.focus();
                }
            }
            return clients.openWindow(urlToOpen);
        })
    );
});