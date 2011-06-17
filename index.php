<?php

require_once __DIR__.'/silex.phar';

$app = new Silex\Application();

$app['autoloader']->registerNamespace('MarcW', array(__DIR__.'/vendor/marcw-buzz/lib', __DIR__.'/vendor/marcw-velib/lib'));
$app['autoloader']->registerNamespace('Velib', __DIR__.'/vendor/velib/lib');

$app->register(new MarcW\BuzzExtension(), array(
    'buzz.class_path' => __DIR__.'/vendor/buzz/lib'
));
$app->register(new MarcW\VelibExtension());
$app->register(new Silex\Extension\DoctrineExtension(), array(
    'db.options' => array(
        'driver'   => 'pdo_mysql',
        'dbname'   => $_SERVER['DB_NAME'],
        'host'     => 'localhost',
        'user'     => $_SERVER['DB_USER'],
        'password' => $_SERVER['DB_PASSWORD'],
    ),
    'db.dbal.class_path'    => __DIR__.'/vendor/doctrine-dbal/lib',
    'db.common.class_path'  => __DIR__.'/vendor/doctrine-common/lib',
    )
);

// Updates infos about station
$app->get('/update/{id}', function ($id) use ($app) {
    $xml = simplexml_load_string($app['velib']->stationDetail($id));

    $available = (int) $xml->available;
    $free      = (int) $xml->free;
    $total     = (int) $xml->total;
    $ticket    = (int) $xml->ticket;

    if ($available === 0 && $free === 0 && $total === 0 && $ticket === 0) {
        return 'This station does not exist.';
    }

    // Update database
    $app['db']->insert('velib_station', array(
        'station'   => (int) $id,
        'date'      => date('Y-m-d H:i:s'),
        'available' => $available,
        'free'      => $free,
        'total'     => $total,
        'ticket'    => $ticket,
    ));

    return 'updated';
});

// Displays infos about station
$app->get('/station/{id}', function($id) {
});

$app->run();
