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
    - [The basics](#the-basics)
    - [Query structure](#query-structure)
    - [All Types](#all-types)
        - [Parameters](#parameters)
            - [var: string, resource variant name](#var-string-resource-variant-name)
            - [alt: string, alternative resource name](#alt-string-alternative-resource-name)
            - [v: integer, version id](#v-integer-version-id)
            - [destroy: bool, remove without backup](#destroy-bool-remove-without-backup)
            - [author: string, author](#author-string-author)
    - [Path structure](#path-structure)
    - [JPG Type](#jpg-type)
        - [Special parameters](#special-parameters)
            - [size=WxH, string, image dimension](#sizewxh-string-image-dimension)
            - [variant=fractal fractal variant of the image](#variantfractal-fractal-variant-of-the-image)
            - [variant=auto[:id] automatically found variant](#variantautoid-automatically-found-variant)
            - [filters[]=filtername, string, postprocessing filters](#filtersfiltername-string-postprocessing-filters)
    - [MP3 Type](#mp3-type)
        - [GET /*.mp3](#get-mp3)
            - [First request (without cache)](#first-request-without-cache)
            - [Second request (Nginx cache)](#second-request-nginx-cache)
        - [POST /*.mp3](#post-mp3)
            - [Creation](#creation)
            - [Secondary creation](#secondary-creation)
            - [Regeneration 1: Re-created file is identical to the existing](#regeneration-1-re-created-file-is-identical-to-the-existing)
            - [Regeneration 2: The created file is a different](#regeneration-2-the-created-file-is-a-different)
            - [File Uploading](#file-uploading)
        - [DELETE /*.mp3](#delete-mp3)
            - [Safety deletion](#safety-deletion)
            - [Destroying](#destroying)
    - [Installation and tests](#installation-and-tests)

## Disclaimer

- *Проект в стадии разработки*. Заявленная ниже функциональность пока ещё является техническим заданием
  и не реализована в полном объёме. Если вы обнаружили расхождение в спецификации и реализации — задавайте вопросы.
- Примеры показаны с использованием утилиты [jkbrzt/httpie](https://github.com/jkbrzt/httpie)
- Если не переданы данные авторизации, генерация и удаление новых файлов не будет выполнена.

## The basics

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

### Parameters

#### var: string, resource variant name

По-умолчанию используется вариант default (зарезервированное имя, которое не обязательно передавать).
Для некоторых ресурсов может понадобиться хранение или генерация разных уникальных вариантов.
Например, пользователь может загрузить собственный вариант ресурса или ресурс может быть сразу генерирован
в нескольких вариантах.

#### alt: string, alternative resource name

*Этот может параметр по-разному обрабатываться для разных типов.*

Иногда основного имени ресурса недостаточно для правильной генерации данных.
Например, если основное короткое имя не в полной мере описывает ресурс или не хватает длины GET-запроса,
или полное имя содержит Unicode-символы.
*при создании ресурса* можно передать альтернативное имя.
В зависимости от типа ресурса, альтернативное имя может быть обработано или проигнорировано.
Например, оно будет использовано для озвучки *вместо основного имени*.
А для поиска изображений — *вместе с основным именем*.

Ресурсы car.jpg, car.jpg?alt=вагон и car.jpg?alt=машина считаются *одинаковыми ресурсами*.
Это значит, что при создании ресурса с указанием альтернативного текста также рекомендуется указывать имя варианта.
Например, ```POST car.jpg?var=vagon&alt=вагон``` создаст вариант изображений для вагона.
Чтобы получить созданное изображение для "alt=вагон", достаточно указать имя его варианта: ```GET car.jpg?var=vagon```


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

### Special parameters

#### size=WxH, string, image dimension

Для jpg поддерживается автоматическая обрезка изображений при выдаче.
Чтобы изображения обрезались, в конфигурационном файле должны быть зарегистрированы все разрешенные размеры.
Изображение с неразрешенным размером будет обрезано к ближайшему найденному зарегистрированному *большему* размеру.

#### variant=fractal fractal variant of the image

Если отправить POST-запрос на этот вариант, будет генерирована картинка с фракталом. Удобно использовать в качестве
заглушек по-умолчанию.

#### variant=auto[:id] automatically found variant

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
- Иначе вернёт 404 Not found

#### First request (without cache)
```
$ http --verify no -h GET https://www.englishdom.dev/staticus/waxwing.mp3
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

#### Second request (Nginx cache)

Nginx отдаёт файл из собственного кеша, уже не обращаясь на proxy_pass.

```
$ http --verify no -h GET https://www.englishdom.dev/staticus/waxwing.mp3
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

#### Creation

```
$ find /var/www/cache/mp3 -type f -name *.mp3
(nothing here)

$ http --verify no --auth Developer:12345 -f POST https://www.englishdom.dev/staticus/waxwing.mp3
HTTP/1.1 201 Created
Cache-Control: public
Cache-Control: public
Connection: keep-alive
Content-Length: 0
Content-Type: application/json
Date: Mon, 04 Apr 2016 20:30:37 GMT
Server: nginx/1.9.7
X-Powered-By: PHP/5.6.15

{
    "resource": {
        "name": "waxwing",
        "nameAlternative": "",
        "recreate": false,
        "type": "mp3",
        "uuid": "2d5080a8ea20ec175c318d65d1429e94",
        "variant": "def",
        "version": 0
    },
    "uri": "waxwing.mp3"
}

$ find /var/www/cache/mp3 -type f -name *.mp3
/var/www/cache/mp3/def/0/2d5080a8ea20ec175c318d65d1429e94.mp3
```

#### Secondary creation

```
$ http --verify no --auth Developer:12345 -f POST https://www.englishdom.dev/staticus/WaxWing.mp3
HTTP/1.1 304 Not Modified
Cache-Control: public
Cache-Control: public
Connection: keep-alive
Content-Length: 0
Date: Mon, 04 Apr 2016 20:36:16 GMT
Server: nginx/1.9.7
X-Powered-By: PHP/5.6.15

find /var/www/cache/mp3 -type f -name *.mp3
/var/www/cache/mp3/def/0/2d5080a8ea20ec175c318d65d1429e94.mp3
```

#### Regeneration 1: Re-created file is identical to the existing

```
$ http --verify no --auth Developer:12345 -f POST https://www.englishdom.dev/staticus/waxwing.mp3 recreate=1
HTTP/1.1 304 Not Modified
Cache-Control: public
Cache-Control: public
Connection: keep-alive
Content-Length: 0
Date: Sat, 09 Apr 2016 10:08:50 GMT
Server: nginx/1.9.7
X-Powered-By: PHP/5.6.15

$ find /var/www/cache/mp3 -type f -name *.mp3
/var/www/cache/mp3/def/0/2d5080a8ea20ec175c318d65d1429e94.mp3
```

#### Regeneration 2: The created file is a different

```
$ http --verify no --auth Developer:12345 -f POST https://www.englishdom.dev/staticus/waxwing.mp3 recreate=1
HTTP/1.1 201 Created
Cache-Control: public
Cache-Control: public
Connection: keep-alive
Content-Length: 0
Content-Type: application/json
Date: Sat, 09 Apr 2016 10:41:39 GMT
Server: nginx/1.9.7
X-Powered-By: PHP/5.6.15

{
    "resource": {
        "name": "waxwing",
        "nameAlternative": "",
        "recreate": true,
        "type": "mp3",
        "uuid": "2d5080a8ea20ec175c318d65d1429e94",
        "variant": "def",
        "version": 0
    },
    "uri": "waxwing.mp3"
}

$ find /var/www/cache/mp3 -type f -name *.mp3
/var/www/cache/mp3/def/0/2d5080a8ea20ec175c318d65d1429e94.mp3
/var/www/cache/mp3/def/1/2d5080a8ea20ec175c318d65d1429e94.mp3 # automatically backuped version
```

#### File Uploading

- You can use any parameter name for the uploaded file, but only first file from multiple files list will be uploaded.
- Uploading will be ignored, if the version already exist. So, use 'recreate' param to force uploading.

```
$ http --verify no --auth Developer:12345 -f POST https://www.englishdom.dev/staticus/waxwing.mp3 \
  recreate=true var=test file@/Users/kivagant/vagrant/staticus/test.mp3
HTTP/1.1 201 Created
Cache-Control: public
Cache-Control: public
Connection: keep-alive
Content-Length: 0
Content-Type: application/json
Date: Sun, 10 Apr 2016 14:40:17 GMT
Server: nginx/1.9.7
X-Powered-By: PHP/5.6.15

{
    "resource": {
        "name": "waxwing",
        "nameAlternative": "",
        "recreate": true,
        "type": "mp3",
        "uuid": "2d5080a8ea20ec175c318d65d1429e94",
        "variant": "test",
        "version": 0
    },
    "uri": "waxwing.mp3?var=test"
}

$ find /var/www/cache/mp3 -type f -name *.mp3
/var/www/cache/mp3/def/0/2d5080a8ea20ec175c318d65d1429e94.mp3
/var/www/cache/mp3/def/1/2d5080a8ea20ec175c318d65d1429e94.mp3
/var/www/cache/mp3/test/0/2d5080a8ea20ec175c318d65d1429e94.mp3
```

### DELETE /*.mp3

- Бекенд проверяет существование файла.
- Если найден, создаёт резервную копию при условии, что предыдущая не-нулевая версия не идентична удаляемой.
- Удаляет текущий оригинальный файл.
- Возвращает 204 No content.

#### Safety deletion

If Version 1 not equal to 0, then version 0 will backup to new version 2.

```
$ find /var/www/cache/mp3 -type f -name *.mp3
/var/www/cache/mp3/def/0/2d5080a8ea20ec175c318d65d1429e94.mp3
/var/www/cache/mp3/def/1/2d5080a8ea20ec175c318d65d1429e94.mp3

$ http --verify no --auth Developer:12345 DELETE https://www.englishdom.dev/staticus/waxwing.mp3
HTTP/1.1 204 No Content
Cache-Control: public
Cache-Control: public
Connection: keep-alive
Content-Length: 0
Content-Type: audio/mpeg
Date: Mon, 04 Apr 2016 20:40:05 GMT
Server: nginx/1.9.7
X-Powered-By: PHP/5.6.15

$ find /var/www/cache/mp3 -type f -name *.mp3
/var/www/cache/mp3/def/2/2d5080a8ea20ec175c318d65d1429e94.mp3 # automatically backuped version
/var/www/cache/mp3/def/1/2d5080a8ea20ec175c318d65d1429e94.mp3

$ http --verify no GET https://www.englishdom.dev/staticus/waxwing.mp3\?nocache\=bzbzbz # skip nginx cache
HTTP/1.1 404 Not Found
Connection: keep-alive
Content-Length: 0
Content-Type: audio/mpeg
Date: Sat, 09 Apr 2016 10:48:19 GMT
Server: nginx/1.9.7
X-Powered-By: PHP/5.6.15
```

#### Destroying

```
$ http --verify no --auth Developer:12345 DELETE https://www.englishdom.dev/staticus/waxwing.mp3\?destroy\=1
HTTP/1.1 204 No Content
Cache-Control: public
Cache-Control: public
Connection: keep-alive
Content-Length: 0
Content-Type: audio/mpeg
Date: Sat, 09 Apr 2016 11:38:30 GMT
Server: nginx/1.9.7
X-Powered-By: PHP/5.6.15

$ find /var/www/cache/mp3 -type f -name *.mp3
(nothing here)
```

## Installation and tests

1. Copy **.env.develop** to **.env** and check the variables inside.
2. Copy **phpunit.xml.dist** to **phpunit.xml**.

```
$ composer run-script serve
$ composer run-script test
> phpunit
PHPUnit 4.8.24 by Sebastian Bergmann and contributors.

.........

Time: 180 ms, Memory: 6.75Mb

OK (9 tests, 67 assertions)
```