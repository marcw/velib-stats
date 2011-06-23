<?php

namespace Model\Pomm\Database;

use Pomm\Connection\Database;
use Pomm\Converter\PgEntity;

class VlibDb extends Database
{
    protected function initialize()
    {
        parent::initialize();

        $this->registerConverter('VelibStationData', new PgEntity($this, 'Model\Pomm\Entity\Vlib\VelibStationData'), array('velib_station_data'));
    }
}
