self.addEventListener('push', function(event) {
    console.log('Received a push message', event);

    var title = '◯◯サイトからのお知らせ';
    var body = '記事が更新されました。';

    event.waitUntil(
        self.registration.showNotification(title, {
            body: body
        })
    );
});

self.addEventListener('notificationclick', function(event) {
  console.log('On notification click: ', event);
  event.notification.close();
  event.waitUntil(
      clients.openWindow('http://google.com')
  )
});

// var urlsCache = [
//   '/'
// ];
//
// self.oninstall = function(e) {
//   e.waitUntil(
//     caches.open('version')
//       .then(function(cache) {
//         console.log('Opend Cache');
//         return cache.addAll(urlsCache);
//       })
//   );
// };
//
// self.onfetch = function(e) {
//   e.respondWith(
//     caches.match(e.request)
//       .then(function(res) {
//         if (res) {
//           return res;
//         }
//
//         var fetchReq = event.request.clone();
//
//         return fetch(fetchReq)
//           .then(function(res) {
//             if (!res || res.status !== 200 || res.type !== 'basic') {
//               return res;
//             }
//
//             var resToCache = res.clone();
//             caches.open('version')
//               .then(function(cache) {
//                 cache.put(e.request, resToCache);
//               });
//
//             return res;
//           });
//       })
//   );
// };
