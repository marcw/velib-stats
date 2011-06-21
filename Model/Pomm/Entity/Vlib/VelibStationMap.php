<?php

namespace Model\Pomm\Entity\Vlib;

use Model\Pomm\Entity\Vlib\Base\VelibStationMap as BaseVelibStationMap;
use Pomm\Exception\Exception;
use Pomm\Query\Where;

class VelibStationMap extends BaseVelibStationMap
{
    public function updateStations(Array $stations)
    {
        $ids = array();
        $known_stations = array();
        $unsorted_list = $this->findWhere(Where::createWhereIn('id', array_keys($stations)));

        foreach ($unsorted_list as $unsorted)
        {
            $known_stations[$unsorted->getId()] = $unsorted;
        }

        foreach ($stations as $k => $station)
        {
            if (array_key_exists($k, $known_stations))
            {
                $known_station = $known_stations[$k];
            }
            else
            {
                $known_station = $this->createObject();
            }

            $known_station->convert($station);
            $known_station->setId($k);
            $this->saveOne($known_station);
        }
    }

    public function findAllWithData()
    {
        $sql = sprintf("SELECT %s FROM vlib.velib_station s RIGHT JOIN vlib.velib_station_data d ON s.id = d.station_id ORDER BY id ASC", join(', ', $this->getSelectFields('s')));

        return $this->query($sql);
    }
}
