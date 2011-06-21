<?php

namespace Model\Pomm\Entity\Vlib\Base;

use Pomm\Object\BaseObjectMap;
use Pomm\Exception\Exception;

abstract class VelibStationDataMap extends BaseObjectMap
{
    public function initialize()
    {
        $this->object_class =  'Model\Pomm\Entity\Vlib\VelibStationData';
        $this->object_name  =  'vlib.velib_station_data';

        $this->addField('station_id', 'Integer');
        $this->addField('created_at', 'Timestamp');
        $this->addField('available', 'Integer');
        $this->addField('free', 'Integer');
        $this->addField('total', 'Integer');
        $this->addField('ticket', 'Integer');

        $this->pk_fields = array('station_id', 'created_at');
    }
}