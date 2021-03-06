# Staticus

Application:
[![Build Status](https://scrutinizer-ci.com/g/KIVagant/staticus/badges/build.png)](https://scrutinizer-ci.com/g/KIVagant/staticus/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/KIVagant/staticus/badges/quality-score.png)](https://scrutinizer-ci.com/g/KIVagant/staticus/)
[![Code Coverage](https://scrutinizer-ci.com/g/KIVagant/staticus/badges/coverage.png)](https://scrutinizer-ci.com/g/KIVagant/staticus/)
Core:
[![Build Status](https://scrutinizer-ci.com/g/KIVagant/staticus-core/badges/build.png)](https://scrutinizer-ci.com/g/KIVagant/staticus-core/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/KIVagant/staticus-core/badges/quality-score.png)](https://scrutinizer-ci.com/g/KIVagant/staticus-core/)
[![Code Coverage](https://scrutinizer-ci.com/g/KIVagant/staticus-core/badges/coverage.png)](https://scrutinizer-ci.com/g/KIVagant/staticus-core/)

In short: this [PSR-7](http://www.php-fig.org/psr/psr-7/) based service is an "invisible" layer, which dynamically looking for requested static files and tells to Nginx
where they placed. "Pipeline post-processing", content generators and ACL support give to you a powerfull instrument
for a files management on your web-service.

Quick example:

```
- POST https://www.your.project.dev/staticus/waxwing.mp3
> File will be generated (if you have access) and placed to path like ~/mp3/def/0/22af64.mp3

- GET https://www.your.project.dev/staticus/waxwing.mp3
> The php-backend layer will be called once for the file path search, then file will be sended throught Nginx

- GET https://www.your.project.dev/staticus/waxwing.mp3
> File will be returned from Nginx cache
```

The service handles HTTP requests and gives
[X-Accel-Redirect](https://www.nginx.com/resources/wiki/start/topics/examples/x-accel/) for Nginx,
which will force the file downloading.

Depending on the requested route the corresponding proxy layer is loaded, which refers to the providers to generate
content, then caches the result and next time gives the file from cache.

Nginx internal cache should be configured for providing a maximum speed of repeated requests. Read the example below.

With Staticus you will can:

- call HTTP CRUD operations for different static files on your project without hard integration work;
- generate any file resources by request: images, sounds, documents etc;
- resize and crop images 'on the fly' when it requested from your frontend;
- search for the new images in Google;
- write your own operation layers (middlewares) and make this instrument more powerful!

## Dependencies

Read [information about dependencies](Dependencies.md) used in the project.

## Contents

<!---
see: https://github.com/aslushnikov/table-of-contents-preprocessor
md-toc-filter ./Readme.md > Readme2.md
-->
- [Staticus](#staticus)
    - [Dependencies](#dependencies)
    - [Contents](#contents)
    - [Disclaimer](#disclaimer)
    - [Installation and tests](#installation-and-tests)
    - [The Nginx configuration](#the-nginx-configuration)
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
    - [Advanced usage](#advanced-usage)
        - [List all resource files](#list-all-resource-files)
        - [JPG searching with the special route /search/](#jpg-searching-with-the-special-route-search)
            - [Search example](#search-example)
        - [HTTP-based authentication](#http-based-authentication)
        - [Session-based authentication](#session-based-authentication)
        - [Namespaces](#namespaces)
    - [Contributors](#contributors)
    - [License](#license)

## Disclaimer

- **Some parts of this readme still not translated**. If you can help with that – you are welcome with a PR.
- Examples are shown by the utility [jkbrzt/httpie](https://github.com/jkbrzt/httpie).
  Hint: if you see some unexpected html in your console, use [your-command | html2text](https://pypi.python.org/pypi/html2text)
- All data operations such as reading, generation or deleting, controlled with the ACL config.

## Installation and tests

Notice: composer will run ```post-create-project-cmd````.
All distributive configuration files will be copied without replacing from their templates.
If you not trust to composer scripts, just add ```--no-scripts``` argument.

1. This project works like ready-to-use application. So, you don't need to **require** it. Instead run:

```
$ composer create-project "kivagant/staticus"
$ cd staticus
```

2. Open **.env** file for editing and setup the variables inside. First of all, **do not forget to setup the DATA_DIR**!

3. **Important note:** The next step will try to create and delete test files (in [AcceptanceTest](test/StaticusTest/Actions/Fractal/AcceptanceTest.php).
So, read the [License](#license), run and pray :)

```
$ composer run-script test
> phpunit
PHPUnit 4.8.24 by Sebastian Bergmann and contributors.
...
OK (85 tests, 377 assertions)
```

Then you can run project without Nginx and works with it almost like in examples below. The only difference is that
you can't see any files in GET requests because the only X-Accel-Redirect header will be sent.

```
$ composer run-script serve
```

But if you want to see a real dark magic, read the next part of this documentation.

## The Nginx configuration

Look for simple Nginx config example: [staticus.conf](etc/nginx/conf.d/staticus.conf)

The main host in Nginx proxies a request to the "auxiliary host" (at himself in reality) and caches a successful result:

```
proxy_pass http://127.0.0.1:8081;
```

Auxiliary Nginx-host proxies a request to the backend (Staticus php-project).

```
location / {
    ...
    include fastcgi_fpm.conf;
}
```

Backend processes the request, looks for file or generates the new content and sends the
[X-Accel-Redirect](https://www.nginx.com/resources/wiki/start/topics/examples/x-accel/),
which tells Nginx, where to take the final file for downloading.

Nginx processes the route according to the internal location configuration and sends the result to the client.

```
location ~* ^/data/(img|voice)/(.+)\.(jpg|jpeg|gif|png|mp3)$ {
    internal;
    ...
}
```

For the client, all looks like his just received a static file.

## Query structure

scheme:[//[user:password@]host[:port]][/path-to-home][/namespace/sub/namespace]/resource.type[?parameters]

- **user:password**: HTTP-based authentication for the administrator role.
- **path-to-home**: project can be located in the sub-route, this is should be taken into account,
  creating the right URLs in the client Views.
- **namespace**: logically grouped resources with separate ACL rules.
  Every session-authorised user has own namespace ```/user/{id}```.
- **resource**: basic short name of the resource. With the same address the same resource always will be returned.
- **type**: the type of resource that guarantees the return file extension and mime-type.
- **parameters**: some parameters is supported by default, but different types of resources can have their own parameters.
  Parameters affect the returned data. Can be sent in query or in the POST body.

## Supported HTTP Methods

| Method | HTTP Statuses | Comment |
|--------|---------------|---------|
| GET | 404, 200 | Returns the resource data (which can be cached with Nginx) |
| POST | 201, 304 | Once creates a resource on the specified route, unless the forcing re-creation will be requested |
| DELETE | 204 | It removes the requested version of the resource or reports that the resource is not exists. |

PUT is not supported.

### Parameters

#### var: string, resource variant name

By default the 'def' variant is used.

For some resources may need to store or generate different unique variants.
For example, a user can upload his own version of a resource or a resource can be generated in several variants.

#### alt: string, alternative resource name

*This parameter can be handled differently for different resource types.*

*When you create a resource*, you can pass an alternative name.

Sometimes the main resource name is not enough to generate the correct data, or to ensure uniqueness.
For example, if the common short name is not fully describes the resource, or lacks length of a GET-request,
or full name contains Unicode-characters etc.

Depending on the resource type, an alternate name can be further processed or ignored.
For example, the alternative name will be used for voicing *instead of the common name*.
And for the image searching – *along with the main name*.

Resources ```car.jpg```, ```car.jpg?alt=вагон``` and ```car.jpg?alt=машина``` considered as **different resources**.
Alternative name will be used in the formation **uuid** resource together with the basic name.

#### body: string, additional information for resource creation

Sometimes is not enough to use URI variables for resource creation.
For example, when you need to create some audio-file with uri: ```/my-audio.mp3``` with a big text (for voicing) inside,
you can send the body argument through HTTP POST body. The resource will have short url (without alt=...) and,
in the same time, correct long data inside.

#### v: integer, version id

Each version of a resource contains its own version.
By **default the 0 version** used (and v=0 is not required), which reflects the *latest state* of the current resource.

If a standard or a special version of the resource is changed (recreated or deleted), the current zero-version
is automatically saved as a new auto-increment version and the new zero-version will be created instead.

When the zero version is deleted, it's just moved to a new auto-increment version and the other versions
will be not deleted.

When changing the resource you can send a pointer to the specific version and it can be completely removed or replaced.
If the version in the middle of the list will be deleted (v=2 for example), a "hole" will be appear:
v=1: 200, v=2: 404 Not found, v=3: 200.

If you delete a version at the end of the list, the other version will be available at the next change to the same number:

1. v=1: 200, v=2: 404 < removed
2. v=1: 200, v=2: 200 < added again after changing the zero version

For completely resource deleting, you need to send a **destroy** parameter.

#### DELETE destroy: bool, remove without backup

- Если при удалении ресурса *версии по-умолчанию* в *варианте по-умолчанию* передать параметр destroy — ресурс будет
удалён во всех вариантах и версиях.
- Если при удалении *версии по-умолчанию* для *конкретного варианта* передать параметр destroy — будут удалены
  все версии этого варианта.
- Если при удалении ресурса указана определенная версия (для любого варианта) и передан параметр destroy,
  будет удалена только указанная версия этого варианта, т.е. параметр destroy не окажет никакого влияния на поведение.

#### POST author: string, author

TODO: not implemented yet
The line with information about the author of the changes in an arbitrary string. Required only for logging.

#### POST uri=http Upload image by remote URI

Image, specified in the url parameter, will be uploaded to the server.

## Path structure

Different resources types can have different path structure. The Resource object have a path map specification inside.
You can look into specification with method [ResourceDOInterface::getDirectoryTokens()](https://github.com/KIVagant/staticus-core/blob/master/src/Resources/ResourceDOInterface.php#L129)

- **[/namespace]/type/shard_variant/variant/version/[size/][other-type-specified/]shard_uuid/uuid.type**
- /jpg/def/def/0/0/22a/22af64.jpg
- /jpg/use/user/3/0/22a/22af64.jpg
- /jpg/fra/fractal/0/22a/30x40/22af64.jpg
- /jpg/som/some_module/0/22a/100x110/22af64.jpg
- /mp3/def/def/0/22a/22af64.mp3
- /mp3/def/def/1/22a/22af64.mp3

Notice: ```shard_variant```` and ```shard_uuid``` should help to avoid filesystem crash or critical response time.
In the examples below their can be skipped.

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
$ http --verify no -h GET https://www.your.project.dev/staticus/waxwing.mp3
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
$ http --verify no -h GET https://www.your.project.dev/staticus/waxwing.mp3
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

$ http --verify no --auth Developer:12345 -f POST https://www.your.project.dev/staticus/waxwing.mp3
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
$ http --verify no --auth Developer:12345 -f POST https://www.your.project.dev/staticus/WaxWing.mp3
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
$ http --verify no --auth Developer:12345 -f POST https://www.your.project.dev/staticus/waxwing.mp3 recreate=1
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
$ http --verify no --auth Developer:12345 -f POST https://www.your.project.dev/staticus/waxwing.mp3 recreate=1
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
$ http --verify no --auth Developer:12345 -f POST https://www.your.project.dev/staticus/waxwing.mp3 \
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
$ http --verify no --auth Developer:12345 -f POST https://www.your.project.dev/staticus/waxwing.mp3 var=remote uri='http://some.domain/new.mp3'
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

$ http --verify no --auth Developer:12345 DELETE https://www.your.project.dev/staticus/waxwing.mp3
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

$ http --verify no GET https://www.your.project.dev/staticus/waxwing.mp3\?nocache\=bzbzbz # skip nginx cache
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
$ http --verify no --auth Developer:12345 DELETE https://www.your.project.dev/staticus/waxwing.mp3\?destroy\=1
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

## Advanced usage

### List all resource files

- **GET|POST** /list/*{resource_route}*
- **ACL Action**: list

```
$ http --body --verify no --auth Developer:12345 GET https://www.your.project.dev/staticus/list/welcome.jpg
{
    "options": [
        {
            "dimension": "0",
            "size": 5322,
            "timestamp": 1464692308,
            "variant": "def",
            "version": "0"
        },
        {
            "dimension": "100x100",
            "size": 2165,
            "timestamp": 1464692314,
            "variant": "def",
            "version": "0"
        },
        {
            "dimension": "0",
            "size": 17055,
            "timestamp": 1464692306,
            "variant": "def",
            "version": "1"
        }
    ],
    "resource": {
        "dimension": "0",
        "height": 0,
        "name": "welcome",
        "nameAlternative": "",
        "namespace": "",
        "new": false,
        "recreate": false,
        "type": "jpg",
        "uuid": "40be4e59b9a2a2b5dffb918c0e86b3d7",
        "variant": "def",
        "version": 0,
        "width": 0
    }
}
```

### JPG searching with the special route /search/

Setup the GOOGLE_SEARCH_API_KEY and the GOOGLE_SEARCH_API_CX in your ```.env``` config.

- **GET|POST** /search/*{resource_route}?cursor=integer*
- **ACL Action**: search

The file list found by a search adapter will be returned.

1. Select a URL from the list.
2. Send a POST request to any resource route with the same type and add the parameter uri=*chosen-uri*.

- You can attach another search adapters and actions for different resource types (and change search behaviour).
- You can configure ACL config for searching with Actions::ACTION_SEARCH command.
- Only users with the 'adimn' role can use cursor attribute.
- By default, 'name' and 'alt' will be used together for more correct searching.
- By default, the POST 'body' argument will be used instead of 'name' and 'alt' if passed (can be used as searching string).

#### Search example

```
$ http --body --verify no --auth Developer:12345 -f GET https://www.your.project.dev/staticus/search/welcome.jpg\?alt\='school'\&cursor\=11
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
        "start": 10,
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

## Contributors

- Andrew Yanakov <ayanakov at englishdom.com>
- Eugene Glotov <kivagant at gmail.com>

## License

Made in the [EnglishDom online school](https://www.englishdom.com/en/).

Copyright 2016 Eugene Glotov <kivagant at gmail.com>

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
