// Incrementing OFFLINE_VERSION will kick off the install event and force
// previously cached resources to be updated from the network.
const OFFLINE_VERSION = 1;
// Customize this with a different URL if needed.
const offlineUrl = 'core/html/offline.html';

self.addEventListener('install', (event) => {
  caches.delete('BibleWiki Offline');
  event.waitUntil((async () => {
    const cache = await caches.open('BibleWiki Offline');
    // Setting {cache: 'reload'} in the new request will ensure that the response
    // isn't fulfilled from the HTTP cache; i.e., it will be from the network.
    await cache.add(new Request(offlineUrl, {cache: 'reload'}));

    caches.open('BibleWiki Logos').then(function(cache) {
      return cache.addAll(
        [
          '/ressources/images/biblewiki_logo_32x32.png',
          '/ressources/images/biblewiki_logo_64x64.png',
          '/ressources/images/biblewiki_logo_128x128.png',
          '/ressources/images/biblewiki_logo_256x256.png',
          '/ressources/images/biblewiki_logo_512x512.png',
          '/ressources/images/logo.svg',
          '/ressources/images/logo-mini.svg',
          '/ressources/images/logo-white.svg',
          '/ressources/images/favicon.png'
        ]
      );
    })
  })());
});

/*
self.addEventListener('fetch', function (event) {
  event.respondWith(
    // This method looks at the request and
    // finds any cached results from any of the
    // caches that the Service Worker has created.
    caches.match(event.request)
      .then(function (response) {
        // If a cache is hit, we can return thre response.
        if (response) {
          return response;
        }

        // Clone the request. A request is a stream and
        // can only be consumed once. Since we are consuming this
        // once by cache and once by the browser for fetch, we need
        // to clone the request.
        var fetchRequest = event.request.clone();

        // A cache hasn't been hit so we need to perform a fetch,
        // which makes a network request and returns the data if
        // anything can be retrieved from the network.
        return fetch(fetchRequest).then(
          function (response) {
            // Check if we received a valid response
            if (!response || response.status !== 200 || response.type !== 'basic') {
              return response;
            }

            // Cloning the response since it's a stream as well.
            // Because we want the browser to consume the response
            // as well as the cache consuming the response, we need
            // to clone it so we have two streams.
            var responseToCache = response.clone();

            //caches.open(CACHE_NAME)
             // .then(function (cache) {
                // Add the request to the cache for future queries.
             //   cache.put(event.request, responseToCache);
             // });

            return response;
          }).catch(error => {

            console.log('offline');
            
      
          }
        );
      }),
      fetch(event.request.url).catch(error => {
        // Return the offline page
        return caches.match(offlineUrl);
    })
  );
});
*/




self.addEventListener('activate', (event) => {
  event.waitUntil((async () => {
    // Enable navigation preload if it's supported.
    // See https://developers.google.com/web/updates/2017/02/navigation-preload
    if ('navigationPreload' in self.registration) {
      await self.registration.navigationPreload.enable();
    }
  })());

  // Tell the active service worker to take control of the page immediately.
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  // We only want to call event.respondWith() if this is a navigation request
  // for an HTML page.
  if (event.request.mode === 'navigate') {
    event.respondWith((async () => {
      try {
        // First, try to use the navigation preload response if it's supported.
        const preloadResponse = await event.preloadResponse;
        if (preloadResponse) {
          return preloadResponse;
        }

        const networkResponse = await fetch(event.request);
        return networkResponse;
      } catch (error) {
        // catch is only triggered if an exception is thrown, which is likely
        // due to a network error.
        // If fetch() returns a valid HTTP response with a response code in
        // the 4xx or 5xx range, the catch() will NOT be called.
        console.log('Fetch failed; returning offline page instead.', error);

        const cache = await caches.open('BibleWiki Offline');
        const cachedResponse = await cache.match(offlineUrl);
        return cachedResponse;
      }
    })());
  }

  // If our if() condition is false, then this fetch handler won't intercept the
  // request. If there are any other fetch handlers registered, they will get a
  // chance to call event.respondWith(). If no fetch handlers call
  // event.respondWith(), the request will be handled by the browser as if there
  // were no service worker involvement.
});
