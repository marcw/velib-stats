<?php

require_once __DIR__.'/vendor/silex.phar';

use Symfony\Component\HttpFoundation\Response;

$app = new Silex\Application();
$app['autoloader']->registerNamespace('MarcW', array(__DIR__.'/vendor/marcw-buzz/lib', __DIR__.'/vendor/marcw-velib/lib'));

// Registering extensions
$app->register(new MarcW\BuzzExtension(), array('buzz.class_path' => __DIR__.'/vendor/buzz/lib'));
$app->register(new MarcW\VelibExtension(), array('velib.class_path' => __DIR__.'/vendor/velib/lib'));
$app->register(new Silex\Extension\HttpCacheExtension(), array('http_cache.cache_dir' => __DIR__.'/cache'));
$app->register(new Silex\Extension\TwigExtension(), array('twig.path' => __DIR__.'/views', 'twig.class_path' => __DIR__.'/vendor/twig/lib'));
$app->register(new Silex\Extension\UrlGeneratorExtension());
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
    return new Response('', 200, array('Cache-Control' => 's-maxage=86400'));
});

// Updates infos about station
$app->get('/update/{id}', function ($id) use ($app) {
    $values = $app['velib']->stationDetail($id);
    if (null === $values) {
        return new Response('Bad Request', 400);
    }

    // Update database
    $app['db']->insert('velib_station_data', array(
        'station_id'   => (int) $id,
        'created_at'      => date('Y-m-d H:i:s'),
        'available' => $values['available'],
        'free'      => $values['free'],
        'total'     => $values['total'],
        'ticket'    => $values['ticket'],
    ));

    return new Response('', 200, array('Cache-Control' => 's-maxage=600'));
});

// Station list
$app->get('/station/list', function() use ($app) {
    $stmt = $app['db']->executeQuery("SELECT v.* FROM `velib_station` AS v RIGHT JOIN velib_station_data AS d ON v.id=d.station_id GROUP BY v.id");
    $stations = $stmt->fetchAll();

    $body = $app['twig']->render('station_list.twig', array('stations' => $stations));

    return new Response($body, 200, array('Cache-Control' => 's-maxage=1800'));
})->bind('station_list');

// Displays infos about station
$app->get('/station/{id}', function($id) use ($app) {
    $body = $app['twig']->render('station.twig', array('id' => $id));

    return new Response($body, 200, array('Cache-Control' => 's-maxage=120', 'Surrogate-Control' => 'content="ESI/1.0"'));
})->bind('station_show');

// Textual information about station
$app->get('/station/{id}/info', function ($id) use ($app) {
    $info = $app['db']->fetchAssoc('SELECT * FROM velib_station WHERE id= ?', array((int) $id));
    $body = $app['twig']->render('station_info.twig', array('station' => $info));

    return new Response($body, 200, array('Cache-Control' => 's-maxage=86400'));
})->bind('station_info');

// What do we know about this station NOW
$app->get('/station/{id}/now', function ($id) use ($app) {
    $now = $app['db']->fetchAssoc('SELECT * FROM velib_station_data WHERE station_id = ? ORDER BY created_at DESC LIMIT 1', array((int) $id));
    $body = $app['twig']->render('station_data_now.twig', array('now' => $now));

    return new Response($body, 200, array('Cache-Control' => 's-maxage=600'));
})->bind('station_data_now');

// What do we know about this station from last 24h
$app->get('/station/{id}/24h', function ($id) use ($app) {
    $stmt = $app['db']->executeQuery('SELECT * FROM velib_station_data WHERE station_id = ? AND TO_DAYS(NOW()) - TO_DAYS(created_at) < 1 ORDER BY created_at ASC', array((int) $id));
    $data = $stmt->fetchAll();

    $body = $app['twig']->render('station_data_24h.twig', array('data' => $data));

    return new Response($body, 200, array('Cache-Control' => 's-maxage=3600'));
})->bind('station_data_24h');

// homepage
$app->get('/', function() use ($app) {
    $body = $app['twig']->render('index.twig');

    return new Response($body, 200, array('Surrogate-Control' => 'content="ESI/1.0"', 'Cache-Control' => 's-maxage=3600'));
})->bind('homepage');

return $app;
