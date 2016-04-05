# Staticus

Сервис обрабатывает запросы и отдаёт X-Accel-Redirect для Nginx, который отдаёт файл на закачку.
В зависимости от запрошенного маршрута вызывается соответствующий прокси-слой,
который обращается к провайдерам для генерации контента, кеширует результат и в следующий раз отдаёт тот же файл.

Nginx должен быть настроен таким образом, чтобы в свою очередь управлять внутренним кешированием,
обеспечивая максимальную скорость повторной отдачи.

## Принцип работы

См. [fuse.conf](etc/nginx/conf.d/fuse.conf)

Основной host в Nginx проксирует запрос на вспомогательный хост и кеширует успешный результат:

```
proxy_pass http://127.0.0.1:8081;
```

Вспомогательный host в Nginx проксирует запрос на backend.

```
location / {
    #...
    include fastcgi_fpm.conf;
}
```

Backend обрабатывает запрос, генерирует контент и отдаёт заголовок X-Accel-Redirect,
который сообщает Nginx, где брать конечный файл для выдачи.

Nginx обрабатывает полученный маршрут согласно внутреннему location и отдаёт результат.

```
location ~* ^/data/(img|voice)/(.+)\.(jpg|jpeg|gif|png|mp3)$ {
    internal;
    #...
}
```

На клиенте всё выглядит так, как будто просто получен статический файл.

## Примеры

*Примечание:*
- Примеры показаны с использованием утилиты [jkbrzt/httpie](https://github.com/jkbrzt/httpie)
- Если не переданы данные авторизации, генерация и удалениеновых файлов не будет выполнена.
- Для фракталов актуальны те же примеры, но маршрут вида: /fractal/*.png

### GET /*.mp3

- Бекенд проверяет существование файла
- Если найден — сообщит Nginx-у конечный URL, который будет закеширован

#### Первый запрос (без кеша)
```
$ http --auth Developer:12345 -h GET http://englishdom.dev/staticus/waxwing.mp3
HTTP/1.1 200 OK
Accept-Ranges: bytes
Cache-Control: public
Connection: keep-alive
Content-Length: 4904
Content-Type: audio/mpeg
Date: Mon, 04 Apr 2016 07:13:12 GMT
ETag: "5701963e-1328"
Last-Modified: Sun, 03 Apr 2016 22:16:30 GMT
Server: nginx/1.9.7
X-Proxy-Cache: MISS
```

#### Второй запрос (кеширован)

Nginx отдаёт файл из собственного кеша, уже не обращаясь на proxy_pass.

```
$ http --auth Developer:12345 -h GET http://englishdom.dev/staticus/waxwing.mp3
HTTP/1.1 200 OK
Accept-Ranges: bytes
Cache-Control: public
Connection: keep-alive
Content-Length: 4904
Content-Type: audio/mpeg
Date: Mon, 04 Apr 2016 07:13:21 GMT
ETag: "5701963e-1328"
Last-Modified: Sun, 03 Apr 2016 22:16:30 GMT
Server: nginx/1.9.7
X-Proxy-Cache: HIT
```

### POST /*.mp3

*Примечание:* параметр recreate всегда вызовет перегенерацию

- Бекенд проверяет существование файла.
- Если найден (и нет флага recreate), вернёт HTTP 304 Not Modified.
- Если не найден, обращается к провайдеру озвучки.
- Полученный результат бекенд кеширует в своей папке.
- TODO: и прописывает связь запроса и файла, чтоб позволить поиск и фильтрацию файлов.
- Вернёт HTTP 201 Created

#### Первый запрос (или требование регенерации)

```
$ http --auth Developer:12345 POST http://englishdom.dev/staticus/waxwing.mp3\?recreate\=1

HTTP/1.1 201 Created
Cache-Control: public
Cache-Control: public
Connection: keep-alive
Content-Length: 0
Content-Type: audio/mpeg
Date: Mon, 04 Apr 2016 20:30:37 GMT
Server: nginx/1.9.7
X-Powered-By: PHP/5.6.15
```

#### Второй запрос

```
$ http --auth Developer:12345 POST http://englishdom.dev/staticus/WaxWing.mp3

HTTP/1.1 304 Not Modified
Cache-Control: public
Cache-Control: public
Connection: keep-alive
Content-Length: 0
Date: Mon, 04 Apr 2016 20:36:16 GMT
Server: nginx/1.9.7
X-Powered-By: PHP/5.6.15
```

### DELETE /*.mp3

- Бекенд проверяет существование файла.
- Если найден, удаляет его.
- Возвращает 204 No content.

```
$ http --auth Developer:12345 DELETE http://englishdom.dev/staticus/waxwing.mp3

HTTP/1.1 204 No Content
Cache-Control: public
Cache-Control: public
Connection: keep-alive
Content-Length: 0
Content-Type: audio/mpeg
Date: Mon, 04 Apr 2016 20:40:05 GMT
Server: nginx/1.9.7
X-Powered-By: PHP/5.6.15

$ http --auth Developer:12345 GET http://englishdom.dev/staticus/waxwing.mp3

HTTP/1.1 404 Not Found
Connection: keep-alive
Content-Length: 0
Content-Type: audio/mpeg
Date: Mon, 04 Apr 2016 20:40:52 GMT
Server: nginx/1.9.7
X-Powered-By: PHP/5.6.15
```