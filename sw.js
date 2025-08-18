const CACHE_NAME = 'box-cricket-v1.0.0';
const urlsToCache = [
  '/box_cricket/',
  '/box_cricket/grounds.php',
  '/box_cricket/login.php',
  '/box_cricket/register.php',
  '/box_cricket/my_bookings.php',
  '/box_cricket/public/style.css',
  '/box_cricket/public/icons/icon-192x192.png',
  '/box_cricket/public/icons/icon-512x512.png',
  'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap',
  'https://cdn.tailwindcss.com'
];

// Install event - cache resources
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('Opened cache');
        return cache.addAll(urlsToCache);
      })
      .catch(error => {
        console.log('Cache failed:', error);
      })
  );
});

// Fetch event - serve from cache when offline
self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        // Return cached version or fetch from network
        if (response) {
          return response;
        }
        
        // Clone the request because it's a stream
        const fetchRequest = event.request.clone();
        
        return fetch(fetchRequest).then(response => {
          // Check if we received a valid response
          if (!response || response.status !== 200 || response.type !== 'basic') {
            return response;
          }
          
          // Clone the response because it's a stream
          const responseToCache = response.clone();
          
          caches.open(CACHE_NAME)
            .then(cache => {
              // Don't cache API calls or dynamic content
              if (!event.request.url.includes('/admin/') && 
                  !event.request.url.includes('/superadmin/') &&
                  !event.request.url.includes('make_booking.php') &&
                  !event.request.url.includes('user_cancel_booking.php')) {
                cache.put(event.request, responseToCache);
              }
            });
          
          return response;
        });
      })
      .catch(() => {
        // Return offline page for navigation requests
        if (event.request.mode === 'navigate') {
          return caches.match('/box_cricket/offline.html');
        }
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== CACHE_NAME) {
            console.log('Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});

// Background sync for offline bookings
self.addEventListener('sync', event => {
  if (event.tag === 'background-sync') {
    event.waitUntil(doBackgroundSync());
  }
});

// Push notification handling
self.addEventListener('push', event => {
  const options = {
    body: event.data ? event.data.text() : 'New booking update available!',
    icon: '/box_cricket/public/icons/icon-192x192.png',
    badge: '/box_cricket/public/icons/icon-72x72.png',
    vibrate: [100, 50, 100],
    data: {
      dateOfArrival: Date.now(),
      primaryKey: 1
    },
    actions: [
      {
        action: 'explore',
        title: 'View Bookings',
        icon: '/box_cricket/public/icons/icon-96x96.png'
      },
      {
        action: 'close',
        title: 'Close',
        icon: '/box_cricket/public/icons/icon-96x96.png'
      }
    ]
  };

  event.waitUntil(
    self.registration.showNotification('Box Cricket Booking', options)
  );
});

// Notification click handling
self.addEventListener('notificationclick', event => {
  event.notification.close();

  if (event.action === 'explore') {
    event.waitUntil(
      clients.openWindow('/box_cricket/my_bookings.php')
    );
  }
});

// Background sync function
function doBackgroundSync() {
  // This would handle any pending offline actions
  // For now, just log that sync occurred
  console.log('Background sync completed');
  return Promise.resolve();
}

// Handle offline/online status
self.addEventListener('online', event => {
  console.log('App is online');
  // Could trigger a sync here
});

self.addEventListener('offline', event => {
  console.log('App is offline');
  // Could show offline indicator
});
