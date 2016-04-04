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

## Пример

*Примечание: примеры показаны с использованием утилиты [jkbrzt/httpie](https://github.com/jkbrzt/httpie)*

### Первый запрос

- Вызывается бекенд, который обращается к провайдеру озвучки.
- Полученный результат бекенд кеширует в своей папке.
- TODO: и прописывает связь запроса и файла, чтоб позволить поиск и фильтрацию файлов.

```
➜  fuse git:(master) ✗ http -h http://fuse.dev:8080/Google.mp3
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

### Второй запрос

Nginx отдаёт файл из собственного кеша, уже не обращаясь на proxy_pass.

```
➜  fuse git:(master) ✗ http -h http://fuse.dev:8080/Google.mp3
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