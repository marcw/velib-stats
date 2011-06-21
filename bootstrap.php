<?php

require_once __DIR__.'/vendor/silex.phar';

$app = new Silex\Application();

// AUTOLOADING
$app['autoloader']->registerNamespace('MarcW', array(__DIR__.'/vendor/marcw-buzz/lib', __DIR__.'/vendor/marcw-velib/lib'));
$app['autoloader']->registerNamespace('GHub', array(__DIR__.'/vendor'));
$app['autoloader']->registerNamespace('Model', __DIR__);

// EXTENSIONS
$app->register(new MarcW\BuzzExtension(), array('buzz.class_path' => __DIR__.'/vendor/buzz/lib'));
$app->register(new MarcW\VelibExtension(), array('velib.class_path' => __DIR__.'/vendor/velib/lib'));
$app->register(new Silex\Extension\HttpCacheExtension(), array('http_cache.cache_dir' => __DIR__.'/cache'));
$app->register(new Silex\Extension\TwigExtension(), array('twig.path' => __DIR__.'/views', 'twig.class_path' => __DIR__.'/vendor/twig/lib'));
$app->register(new Silex\Extension\UrlGeneratorExtension());
$app->register(
    new GHub\PommExtension\PommExtension(),
    array(
        'pomm.class_path' => __DIR__.'/vendor/pomm',
        'pomm.connections' => array(
            'default' => array('dsn' => 'pgsql://greg/greg')))
        );
