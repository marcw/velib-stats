<?php

namespace Model\Pomm\Entity\Vlib\Base;

use Pomm\Object\BaseObjectMap;
use Pomm\Exception\Exception;

abstract class VelibStationMap extends BaseObjectMap
{
    public function initialize()
    {
        $this->object_class =  'Model\Pomm\Entity\Vlib\VelibStation';
        $this->object_name  =  'vlib.velib_station';

        $this->addField('id', 'Integer');
        $this->addField('bonus', 'Boolean');
        $this->addField('fulladdress', 'String');
        $this->addField('address', 'String');
        $this->addField('coord', 'Point');
        $this->addField('name', 'String');
        $this->addField('open', 'Boolean');
        $this->addField('created_at', 'Timestamp');
        $this->addField('updated_at', 'Timestamp');
        $this->addField('data', 'VelibStationData');

        $this->pk_fields = array('id');
    }
}