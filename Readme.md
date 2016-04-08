# Staticus

Сервис обрабатывает запросы и отдаёт X-Accel-Redirect для Nginx, который отдаёт файл на закачку.
В зависимости от запрошенного маршрута вызывается соответствующий прокси-слой,
который обращается к провайдерам для генерации контента, кеширует результат и в следующий раз отдаёт тот же файл.

Nginx должен быть настроен таким образом, чтобы в свою очередь управлять внутренним кешированием,
обеспечивая максимальную скорость повторной отдачи.

## Contents

<!---
see: https://github.com/aslushnikov/table-of-contents-preprocessor
md-toc-filter ./Readme.md > Readme2.md
-->

- [Staticus](#staticus)
    - [Contents](#contents)
    - [Disclaimer](#disclaimer)
    - [Принцип работы](#-)
    - [Query structure](#query-structure)
    - [All Types](#all-types)
        - [Параметры:](#)
            - [alt: string, alternative resource name](#alt-string-alternative-resource-name)
            - [var: string, resource variant name](#var-string-resource-variant-name)
            - [v: integer, version id](#v-integer-version-id)
            - [destroy: bool, remove without backup](#destroy-bool-remove-without-backup)
            - [author: string, author](#author-string-author)
    - [JPG Type](#jpg-type)
        - [Особые параметры:](#-)
            - [size=WxH, string, image dimension](#sizewxh-string-image-dimension)
            - [variant=fractal фрактальный вариант изображения](#variantfractal---)
            - [variant=auto[:id] автоматически найденный вариант](#variantautoid---)
            - [filters[]=filtername, string, postprocessing filters](#filtersfiltername-string-postprocessing-filters)
    - [MP3 Type](#mp3-type)
        - [GET /*.mp3](#get-mp3)
            - [Первый запрос (без кеша)](#---)
            - [Второй запрос (кеширован)](#--)
        - [POST /*.mp3](#post-mp3)
            - [Первый запрос (или требование регенерации)](#----)
            - [Второй запрос](#-)
        - [DELETE /*.mp3](#delete-mp3)

## Disclaimer

- *Проект в стадии разработки*. Заявленная ниже функциональность пока ещё является техническим заданием
  и не реализована в полном объёме. Если вы обнаружили расхождение в спецификации и реализации — задавайте вопросы.
- Примеры показаны с использованием утилиты [jkbrzt/httpie](https://github.com/jkbrzt/httpie)
- Если не переданы данные авторизации, генерация и удаление новых файлов не будет выполнена.

## Принцип работы

См. [fuse.conf](etc/nginx/conf.d/fuse.conf)

Основной host в Nginx проксирует запрос на вспомогательный хост и кеширует успешный результат:

```
proxy_pass http://127.0.0.1:8081;
```

Вспомогательный host в Nginx проксирует запрос на backend.

```
location / {
    ...
    include fastcgi_fpm.conf;
}
```

Backend обрабатывает запрос, генерирует контент и отдаёт заголовок X-Accel-Redirect,
который сообщает Nginx, где брать конечный файл для выдачи.

Nginx обрабатывает полученный маршрут согласно внутреннему location и отдаёт результат.

```
location ~* ^/data/(img|voice)/(.+)\.(jpg|jpeg|gif|png|mp3)$ {
    internal;
    ...
}
```

На клиенте всё выглядит так, как будто просто получен статический файл.

## Query structure

scheme:[//[user:password@]host[:port]][/path-to-home]/resource.type[?parameters]

- **user:password**: поддерживается HTTP BASIC авторизация для модифицирующих запросов между проектами.
- **path-to-home**: проект может быть размещён во вложенном маршруте, это стоит учитывать при внешнем использовании,
  правильно формируя URL во View.
- **resource**: основное короткое имя ресурса. По одному и тому же адресу всегда должен возвращаться один и тот же ресурс,
  если его принудительно не заменить.
- **type**: тип ресурса, гарантирующий возвращаемое расширение файла и mime-type для успешных запросов.
- **parameters**: поддерживаются типовые параметры, но у разных типов ресурсов могут быть и собственные.
  Параметры влияют на возвращаемые данные. Могут передаваться в теле POST.

## All Types

| Method | HTTP Statuses | Comment |
|--------|---------------|---------|
| GET | 404, 200 | Возвращает данные ресурса (которые могут кешироваться Nginx) |
| POST | 201, 304 | Однократно создаёт ресурс по указанному маршруту, если не запрошено принудительное пересоздание |
| DELETE | 204 | Удаляет ресурс актуальной или запрошенной версии или сообщает, что ресурс отсутствует |

PUT не поддерживается.

### Параметры:

#### alt: string, alternative resource name

*Этот может параметр по-разному обрабатываться для разных типов.*

Если основное короткое имя не в полной мере описывает ресурс,
например, не хватает длины или полное имя содержит Unicode-символы,
можно передать альтернативное имя, которое будет дополнять уникальный маршрут ресурса.

Ресурсы car.jpg, car.jpg?alt=вагон и car.jpg?alt=машина считаются *разными ресурсами*.

#### var: string, resource variant name

По-умолчанию используется вариант default (зарезервированное имя, которое не обязательно передавать).
Для некоторых ресурсов может понадобиться хранение или генерация разных уникальных вариантов.
Например, пользователь может загрузить собственный вариант ресурса или ресурс может быть сразу генерирован
в нескольких вариантах.

#### v: integer, version id

Каждый вариант ресурса содержит собственные версии.
По-умолчанию используется нулевая версия, которая отражает последнее актуальное состояние ресурса.
Передавать v=0 не обязательно.
Если стандартный или особый вариант ресурса изменяется (пересоздаётся или удаляется), то изменяемый вариант
автоматически сохраняется в виде версии, которой присваивается автоинкрементный идентификатор.
Удалённая нулевая версия не удаляет ресурс целиком.
Если при изменении ресурса отправить указатель конкретной версии, её можно будет окончательно удалить или заменить.
Если удалить версию в середине списка (например, v=2), появится "дырка": v=1: 200, v=2: 404, v=3: 200.
Если удалить версию в конце списка, при очередно изменении появится другая версия с этим же номером:
1. v=1: 200, v=2: 404 < удалена
2. v=1: 200, v=2: 200 < добавлена заново после изменения нулевой версии

Чтобы удалить ресурс полностью, нужно добавить параметр **destroy**.

#### destroy: bool, remove without backup

- Если при удалении ресурса *версии по-умолчанию* в *варианте по-умолчанию* передать параметр destroy — ресурс будет
удалён во всех вариантах и версиях.
- Если при удалении *версии по-умолчанию* для *конкретного варианта* передать параметр destroy — будут удалены
  все версии этого варианта.
- Если при удалении ресурса указана определенная версия (для любого варианта) и передан параметр destroy,
  будет удалена только указанная версия этого варианта, т.е. параметр destroy не окажет никакого влияния на поведение.

#### author: string, author

Строка с информацией об авторе изменения в произвольном строковом формате. Требуется только для журналирования.

## Path structure

- **/type/variant/version/[size/][other-type-specified/]uuid.type**
- /jpg/def/0/0/22af64.jpg
- /jpg/user1534/3/0/22af64.jpg
- /jpg/fractal/0/30x40/22af64.jpg
- /mp3/def/1/22af64.mp3
- /mp3/ivona/0/22af64.mp3

## JPG Type

### Особые параметры:

#### size=WxH, string, image dimension

Для jpg поддерживается автоматическая обрезка изображений при выдаче.
Чтобы изображения обрезались, в конфигурационном файле должны быть зарегистрированы все разрешенные размеры.
Изображение с неразрешенным размером будет обрезано к ближайшему найденному зарегистрированному *большему* размеру.

#### variant=fractal фрактальный вариант изображения

Если отправить POST-запрос на этот вариант, будет генерирована картинка с фракталом. Удобно использовать в качестве
заглушек по-умолчанию.

#### variant=auto[:id] автоматически найденный вариант

Варианты будут искаться через зарегистрированного провайдера изображений (поддерживается один провайдер).
Если не указан идентификатор, вернётся нулевой вариант (первый из найденных).
Если не существует варианта изображения по-умолчанию, нулевой вариант из автоматически генерированных будет скопирован
как вариант по-умолчанию.

#### filters[]=filtername, string, postprocessing filters

TODO: filters support

## MP3 Type

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
- Если не найден, обращается к зарегистрированному провайдеру озвучки (поддерживается один провайдер).
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