// Push Notification Handler for My Doctor

console.log('📱 Push notification script loaded');

// Request permission on page load
document.addEventListener('DOMContentLoaded', async function() {
    console.log('📱 Push notification system initializing...');
    
    if ('Notification' in window) {
        console.log('✅ Notifications supported');
        
        if (Notification.permission === 'default') {
            console.log('🔔 Requesting notification permission...');
            const permission = await Notification.requestPermission();
            console.log('📋 Permission result:', permission);
        }
        
        // Register service worker
        await registerServiceWorker();
    } else {
        console.error('❌ Notifications not supported in this browser');
    }
});

// Register service worker
async function registerServiceWorker() {
    if ('serviceWorker' in navigator) {
        try {
            console.log('🔄 Registering service worker...');
            const registration = await navigator.serviceWorker.register('/service-worker.js');
            console.log('✅ Service Worker registered successfully');
            
            // Check if already subscribed
            const subscription = await registration.pushManager.getSubscription();
            if (!subscription && Notification.permission === 'granted') {
                console.log('📡 No subscription found, subscribing user...');
                await subscribeUser(registration);
            } else if (subscription) {
                console.log('✅ User already subscribed');
                // Send existing subscription to server
                await sendSubscriptionToServer(subscription);
            }
        } catch (error) {
            console.error('❌ Service Worker registration failed:', error);
        }
    } else {
        console.error('❌ Service Worker not supported in this browser');
    }
}

// Subscribe user to push notifications
async function subscribeUser(registration) {
    try {
        const publicKeyMeta = document.querySelector('meta[name="vapid-public-key"]');
        if (!publicKeyMeta) {
            console.error('❌ VAPID public key meta tag not found');
            return;
        }
        
        const publicKey = publicKeyMeta.content;
        console.log('🔑 VAPID Public Key found');
        
        const subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(publicKey)
        });
        
        console.log('📡 Push subscription created');
        
        // Send subscription to server
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (!csrfMeta) {
            console.error('❌ CSRF token meta tag not found');
            return;
        }
        
        const response = await fetch('/push-subscriptions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfMeta.content
            },
            body: JSON.stringify(subscription.toJSON())
        });
        
        if (response.ok) {
            console.log('✅ Subscription saved on server');
        } else {
            console.error('❌ Failed to save subscription on server');
        }
        
    } catch (error) {
        console.error('❌ Failed to subscribe user:', error);
    }
}

// Send subscription to server
async function sendSubscriptionToServer(subscription) {
    try {
        console.log('📡 Sending subscription to server...');
        
        // Send subscription to server
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (!csrfMeta) {
            console.error('❌ CSRF token meta tag not found');
            return;
        }
        
        const response = await fetch('/push-subscriptions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfMeta.content
            },
            body: JSON.stringify(subscription.toJSON())
        });
        
        if (response.ok) {
            console.log('✅ Subscription saved on server');
        } else {
            console.error('❌ Failed to save subscription on server');
        }
        
    } catch (error) {
        console.error('❌ Failed to send subscription:', error);
    }
}

// Helper function to convert base64 to Uint8Array
function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
        .replace(/\-/g, '+')
        .replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    
    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

// Unsubscribe user
window.unsubscribeUser = async function() {
    if ('serviceWorker' in navigator) {
        try {
            const registration = await navigator.serviceWorker.ready;
            const subscription = await registration.pushManager.getSubscription();
            
            if (subscription) {
                console.log('🔄 Unsubscribing...');
                await subscription.unsubscribe();
                
                const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                if (csrfMeta) {
                    await fetch('/push-subscriptions/delete', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfMeta.content
                        },
                        body: JSON.stringify({ endpoint: subscription.endpoint })
                    });
                }
                
                console.log('✅ User unsubscribed');
            }
        } catch (error) {
            console.error('❌ Failed to unsubscribe:', error);
        }
    }
};

// Toggle notifications (call from UI)
window.toggleNotifications = async function(enabled) {
    console.log('🔄 Toggling notifications:', enabled ? 'ON' : 'OFF');
    
    if (enabled) {
        if (Notification.permission === 'granted') {
            const registration = await navigator.serviceWorker.ready;
            await subscribeUser(registration);
        } else if (Notification.permission === 'default') {
            const permission = await Notification.requestPermission();
            if (permission === 'granted') {
                location.reload();
            }
        }
    } else {
        await window.unsubscribeUser();
    }
};

// Expose functions globally
window.testNotification = function() {
    if (Notification.permission === 'granted') {
        new Notification('🔔 Test Notification', {
            body: 'Push notifications are working!',
            icon: '/images/logos/applogo.jpg',
            badge: '/images/logos/applogo.jpg',
            vibrate: [200, 100, 200]
        });
        console.log('✅ Test notification sent');
    } else {
        console.log('❌ Cannot send test - permission not granted');
    }
};