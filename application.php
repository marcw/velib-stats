<?php

require "bootstrap.php";

use Symfony\Component\HttpFoundation\Response;

$app['db'] = $app['pomm']
    ->getDatabase()
    ->createConnection();

// Updating database with list of stations
$app->get('/update/list', function() use ($app) {
    $stations = $app['velib']->stationList();
    $app['db']->getMapFor('Model\Pomm\Entity\Vlib\VelibStation')
        ->updateStations($stations);

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
    $data_map = $app['db']->getMapFor('Model\Pomm\Entity\Vlib\VelibStationData');
    $station_map = $app['db']->getMapFor('Model\Pomm\Entity\Vlib\VelibStation');

    try
    {
        $app['db']->begin();
        $velib_station_data = $data_map->createObject();
        $values['station_id'] = $id;
        $velib_station_data->hydrate($values);
        $data_map->saveOne($velib_station_data);
        $velib_station = $station_map->findByPk(array('id' => $id));
        $velib_station->setData($velib_station_data);
        $station_map->updateOne($velib_station, array('data'));
        $app['db']->commit();
    }
    catch (Exception $e)
    {
        $app['db']->rollback();

        throw $e;
    }

    return new Response('', 200, array('Cache-Control' => 's-maxage=600'));
});

// Station list
$app->get('/station/list', function() use ($app) {
    $stations = $app['db']->getMapFor('Model\Pomm\Entity\Vlib\VelibStation')
        ->findAllWithData();

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
    $map = $app['db']->getMapFor('Model\Pomm\Entity\Vlib\VelibStation');
    $info = $map
        ->findByPk(array('id' => $id));
    $nearest = $map
        ->findNearest($id);

    $body = $app['twig']->render('station_info.twig', array('station' => $info, 'nearest' => $nearest));

    return new Response($body, 200, array('Cache-Control' => 's-maxage=86400'));
})->bind('station_info');

// What do we know about this station NOW
$app->get('/station/{id}/now', function ($id) use ($app) {
    $now = $app['db']->getMapFor('Model\Pomm\Entity\Vlib\VelibStationData')
        ->getLast($id);
    $body = $app['twig']->render('station_data_now.twig', array('now' => $now[0]));

    return new Response($body, 200, array('Cache-Control' => 's-maxage=600'));
})->bind('station_data_now');

// What do we know about this station from last 24h
$app->get('/station/{id}/24h', function ($id) use ($app) {
    $data = $app['db']->getMapFor('Model\Pomm\Entity\Vlib\VelibStationData')
        ->getOlderUntil($id, 1);

    $body = $app['twig']->render('station_data_24h.twig', array('data' => $data));

    return new Response($body, 200, array('Cache-Control' => 's-maxage=3600'));
})->bind('station_data_24h');

// homepage
$app->get('/', function() use ($app) {
    $body = $app['twig']->render('index.twig');

    return new Response($body, 200, array('Surrogate-Control' => 'content="ESI/1.0"', 'Cache-Control' => 's-maxage=3600'));
})->bind('homepage');

return $app;
