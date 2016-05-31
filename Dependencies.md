# Dependencies

- [kivagant/staticus-core](https://github.com/KIVagant/staticus-core) - the core part of this project
- [kivagant/staticus-search-manager](https://github.com/KIVagant/staticus-search-manager) - Google API adapter
- [kivagant/staticus-fractal-manager](https://github.com/KIVagant/staticus-fractal-manager) - simple fractal images generator
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