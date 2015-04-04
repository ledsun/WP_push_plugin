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
