<?php

require "bootstrap.php";

$scan = new Pomm\Tools\ScanSchemaTool(array(
    'schema' => 'vlib',
    'connection' => $app['pomm']->getDatabase(),
    'prefix_dir' => __DIR__,

    ));
$scan->execute();
