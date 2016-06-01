<?php
use Aura\Di\ContainerBuilder;

// Load configuration
$config = require __DIR__ . '/config.php';

// Build container
$builder = new ContainerBuilder();
$container = $builder->newInstance($builder::AUTO_RESOLVE);

// Inject config
$container->set('config', $config);
$container->set(Staticus\Config\ConfigInterface::class, $container->lazyNew(Staticus\Config\Config::class, [$config]));
$container->types[Staticus\Config\ConfigInterface::class] = $container->lazyGet(Staticus\Config\ConfigInterface::class);

// Inject factories
foreach ($config['dependencies']['factories'] as $name => $object) {
    $container->set($object, $container->lazyNew($object));
    $container->set($name, $container->lazyGetCall($object, '__invoke', $container));
}

// Inject invokables
foreach ($config['dependencies']['invokables'] as $name => $object) {
    $container->set($name, $container->lazyNew($object));
}

// Inject type hintings
foreach ($config['dependencies']['types'] as $name => $object) {
    $container->types[$name] = $container->lazyGet($object);
}

return $container;
