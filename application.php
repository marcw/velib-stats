<?php

require_once __DIR__.'/vendor/silex.phar';

use Symfony\Component\HttpFoundation\Response;

$app = new Silex\Application();
$app['autoloader']->registerNamespace('MarcW', array(__DIR__.'/vendor/marcw-buzz/lib', __DIR__.'/vendor/marcw-velib/lib'));

// Registering extensions
$app->register(new MarcW\BuzzExtension(), array('buzz.class_path' => __DIR__.'/vendor/buzz/lib'));
$app->register(new MarcW\VelibExtension(), array('velib.class_path' => __DIR__.'/vendor/velib/lib'));
$app->register(new Silex\Extension\HttpCacheExtension(), array('http_cache.cache_dir' => __DIR__.'/cache'));
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

// Updating database with list of stations
$app->get('/update/list', function() use ($app) {
    $stations = $app['velib']->stationList();

    foreach ($stations as $k => $station) {
        $result = $app['db']->fetchColumn('SELECT id FROM velib_station WHERE id = ?', array($k));
        if ($result) {
            $station['updated_at'] = date('Y-m-d H:i:s');
            $app['db']->update('velib_station', $station, array('id' => $k));
        } else {
            $station['created_at'] = date('Y-m-d H:i:s');
            $station['updated_at'] = date('Y-m-d H:i:s');
            $station['id'] = $k;
            $app['db']->insert('velib_station', $station);
        }
    }

    // Stores the response in cache for 1 day.
    return new Response('', 200, array('Cache-Control', 's-max-age=86400'));
}

// Updates infos about station
$app->get('/update/{id}', function ($id) use ($app) {
    $values = $app['velib']->stationDetail($id);
    if (null === $values) {
        return new Response('Bad Request', 400);
    }

    // Update database
    $app['db']->insert('velib_station_data', array(
        'station'   => (int) $id,
        'created_at'      => date('Y-m-d H:i:s'),
        'available' => $values['available'],
        'free'      => $values['free'],
        'total'     => $values['total'],
        'ticket'    => $values['ticket'],
    ));

    return new Response('', 200, array('Cache-Control', 's-max-age=600'));
});

// Displays infos about station
$app->get('/station/{id}', function($id) {
});

// Textual information about station
$app->get('/station/{id}/info', function ($id) use ($app) {

});

// What do we know about this station NOW
$app->get('/station/{id}/now', function ($id) use ($app) {
    $data = $app['db']->fetchColumn('SELECT * FROM velib_station_data WHERE station_id = ? ORDER BY created_at DESC', array((int) $id));

    $body = $app['twig']->render('station_data_now', array('data' => $data));
});

return $app;
