# Staticus

Сервис обрабатывает запросы и отдаёт X-Accel-Redirect для Nginx, который отдаёт файл на закачку.
В зависимости от запрошенного маршрута вызывается соответствующий прокси-слой,
который обращается к провайдерам для генерации контента, кеширует результат и в следующий раз отдаёт тот же файл.

Nginx должен быть настроен таким образом, чтобы в свою очередь управлять внутренним кешированием,
обеспечивая максимальную скорость повторной отдачи.

## Dependencies

- [league/flysystem](http://flysystem.thephpleague.com/) - partially used, full integration planned
- [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv) - used only in config files, can be removed
- [zendframework/zend-expressive](https://github.com/zendframework/zend-expressive) - In theory, can be replaced
  to another middleware-based framework, because PSR-7 interfaces used almost everywhere.
- zendframework/zend-expressive-helpers - default for ZFE
- zendframework/zend-stdlib - default for ZFE
- zendframework/zend-expressive-fastroute – can be
  replaced [to another router](https://github.com/zendframework/zend-expressive-router)
- roave/security-advisories - default for ZFE
- [aura/di](https://github.com/auraphp/Aura.Di) – can be replaced (maybe), see ```config/container.php```
- [zendframework/zend-permissions-acl](https://github.com/zendframework/zend-permissions-acl)
- [zendframework/zend-session](https://github.com/zendframework/zend-session) – only for AuthSessionMiddleware
- [mtymek/expressive-config-manager](https://github.com/mtymek/expressive-config-manager) - can be removed,
  only for ```config/config.php```
- [newage/AudioManager](https://github.com/newage/AudioManager) - MPEG-type generator

## Contents

<!---
see: https://github.com/aslushnikov/table-of-contents-preprocessor
md-toc-filter ./Readme.md > Readme2.md
-->
- [Staticus](#staticus)
    - [Dependencies](#dependencies)
    - [Contents](#contents)
    - [Disclaimer](#disclaimer)
    - [The basics](#the-basics)
    - [Query structure](#query-structure)
    - [Supported HTTP Methods](#supported-http-methods)
        - [Parameters](#parameters)
            - [var: string, resource variant name](#var-string-resource-variant-name)
            - [alt: string, alternative resource name](#alt-string-alternative-resource-name)
            - [v: integer, version id](#v-integer-version-id)
            - [DELETE destroy: bool, remove without backup](#delete-destroy-bool-remove-without-backup)
            - [POST author: string, author](#post-author-string-author)
            - [POST uri=http Upload image by remote URI](#post-urihttp-upload-image-by-remote-uri)
    - [Path structure](#path-structure)
    - [JPG Type](#jpg-type)
        - [Special parameters](#special-parameters)
            - [size=WxH, string, image dimension](#sizewxh-string-image-dimension)
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
            - [File Remote Downloading](#file-remote-downloading)
        - [DELETE /*.mp3](#delete-mp3)
            - [Safety deletion](#safety-deletion)
            - [Destroying](#destroying)
    - [Installation and tests](#installation-and-tests)
    - [Advanced usage](#advanced-usage)
        - [JPG searching with the special route /search/](#jpg-searching-with-the-special-route-search)
            - [Search example](#search-example)
        - [HTTP-based authentication](#http-based-authentication)
        - [Session-based authentication](#session-based-authentication)
        - [Namespaces](#namespaces)

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

scheme:[//[user:password@]host[:port]][/path-to-home][/namespace/sub/namespace]/resource.type[?parameters]

- **user:password**: HTTP-based authentication for the administrator role.
- **path-to-home**: проект может быть размещён во вложенном маршруте, это стоит учитывать при внешнем использовании,
  правильно формируя URL во View.
- **namespace**: logically grouped resources with separate ACL rules.
  Every session-authorised user has own namespace ```/user/{id}```.
- **resource**: основное короткое имя ресурса. По одному и тому же адресу всегда должен возвращаться один и тот же ресурс,
  если его принудительно не заменить.
- **type**: тип ресурса, гарантирующий возвращаемое расширение файла и mime-type для успешных запросов.
- **parameters**: поддерживаются типовые параметры, но у разных типов ресурсов могут быть и собственные.
  Параметры влияют на возвращаемые данные. Могут передаваться в теле POST.

## Supported HTTP Methods

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

#### DELETE destroy: bool, remove without backup

- Если при удалении ресурса *версии по-умолчанию* в *варианте по-умолчанию* передать параметр destroy — ресурс будет
удалён во всех вариантах и версиях.
- Если при удалении *версии по-умолчанию* для *конкретного варианта* передать параметр destroy — будут удалены
  все версии этого варианта.
- Если при удалении ресурса указана определенная версия (для любого варианта) и передан параметр destroy,
  будет удалена только указанная версия этого варианта, т.е. параметр destroy не окажет никакого влияния на поведение.

#### POST author: string, author

TODO: not implemented yet
Line with information about the author of the changes in an arbitrary string. Required only for logging.

#### POST uri=http Upload image by remote URI

Image, specified in the url parameter, will be uploaded to the server.

## Path structure

- **[/namespace]/type/variant/version/[size/][other-type-specified/]uuid.type**
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

Если отправить POST-запрос на изображение без загружаемого файла, будет генерирована картинка с фракталом. Удобно использовать в качестве
заглушек по-умолчанию.

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
  recreate=true var=uploaded file@/Users/kivagant/vagrant/staticus/test.mp3
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

#### File Remote Downloading

```
$ http --verify no --auth Developer:12345 -f POST https://www.englishdom.dev/staticus/waxwing.mp3 var=remote uri='http://some.domain/new.mp3'
HTTP/1.1 201 Created
Cache-Control: public
Cache-Control: public
Connection: keep-alive
Content-Length: 186
Content-Type: application/json
Date: Mon, 11 Apr 2016 01:22:01 GMT
Server: nginx/1.9.7
X-Powered-By: PHP/5.6.15

{
    "resource": {
        "name": "waxwing",
        "nameAlternative": "",
        "recreate": false,
        "type": "mp3",
        "uuid": "2d5080a8ea20ec175c318d65d1429e94",
        "variant": "remote",
        "version": 0
    },
    "uri": "waxwing.mp3?var=remote"
}
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

## Advanced usage

### JPG searching with the special route /search/

GET|POST /search/{resource_route}

The file list found by a search adapter will be returned.

1. Select a URL from the list.
2. Send a POST request to any resource route with the same type and add the parameter uri=*chosen-uri*.

- You can attach another search adapters and actions for different resource types.
- You can configure ACL config for searching with Actions::ACTION_SEARCH command.

#### Search example

```
$ http --verify no --auth Developer:12345 -f GET https://www.englishdom.dev/staticus/search/welcome.jpg alt='school'
HTTP/1.1 200 OK
Cache-Control: public
Cache-Control: public
Connection: keep-alive
Content-Encoding: gzip
Content-Type: application/json
Date: Mon, 11 Apr 2016 01:25:52 GMT
Server: nginx/1.9.7
Transfer-Encoding: chunked
Vary: Accept-Encoding
X-Powered-By: PHP/5.6.15

{
    "found": {
        "count": 10,
        "items": [
            {
                "height": 675,
                "size": 453573,
                "thumbnailheight": 112,
                "thumbnailurl": "https://somehots.somedomain/someurl",
                "thumbnailwidth": 146,
                "title": "FREE Back to School Party",
                "url": "http://somehots.somedomain/wp-content/uploads/2013/02/welcome-back-to-school.jpg",
                "width": 880
            },
            {...},
        ],
        "start": 0,
        "total": "449000000"
    }
}
```

### HTTP-based authentication

This is a primary authentication.
Used only for the administrator role. Look into ```AuthBasicMiddleware``` that activated in ```routes.global``` config.
This middleware will setup ADMIN role for current User object regardless session-based login status.
Look into ```acl.global``` config for ADMIN roles.

### Session-based authentication

The ```AuthSessionMiddleware``` allows you to use sessions from your project that includes 'Staticus' inside.
You can transparently embed this project to yours just with Nginx rules.

For example, if your basic project have domain ```https://my.domain.dev```, then you can put 'Staticus' to subpath:
```https://my.domain.dev/static/``` and then all your files will accessible inside this route.
This subpath called ```path-to-home``` in Query structure in this document.
See ```etc/nginx/conf.d/staticus.conf``` with Nginx rules template for this case.

In this situation, 'Staticus' will have clear access to cookies from basic domain. And to users sessions too.

So, if your project used [Zend_Auth storage](http://framework.zend.com/manual/current/en/modules/zend.authentication.intro.html),
the ```AuthSessionMiddleware``` will load it from ```Redis``` sessions
and will look for this path: ```$_SESSION['Zend_Auth']->storage->user_id```.

If you want to change session handler from Redis to something else, just replace
```SessionManagerFactory``` to another one in the dependency section in this config: ```auth.global.php```.

All that ```AuthSessionMiddleware``` need for the ACL rules and default user namespaces support – it is user_id.
So you can replace the middleware to yours and realise this logic:

```
$this->user->login($storage->user_id, [Roles::USER]);
$this->user->setNamespace(UserInterface::NAMESPACES . DIRECTORY_SEPARATOR . $storage->user_id);
```

### Namespaces

You can group your resources in namespaces and setup different Access Control List rules for them.
Setup the allowed namespaces list in the ```staticus.global``` config. You can use wildcard syntax here.

The ```AclMiddleware``` will help to implement rules from the ```acl.global``` config.

So, you can setup rules for different roles for any resource types for global namespace and special namespaces.

By default:
- any guests have READ access to any resources in any namespaces.
- any authorised user has own namespace (started from ```/user/{id}```) and have ANY access to JPG-resources inside.
- an administrator has ANY access to all resources.

You can add or change this behaviour with ACL configuration or with adding another middleware.