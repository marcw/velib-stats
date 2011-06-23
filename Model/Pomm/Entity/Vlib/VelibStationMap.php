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
        $sql = sprintf("SELECT %s FROM vlib.velib_station s WHERE s.data IS NOT NULL ORDER BY id ASC", join(', ', $this->getSelectFields('s')));

        return $this->query($sql);
    }

    public function findNearest($id, $limit = 5)
    {
        $sql = sprintf("SELECT %s, (coord(a) <-> coord(b)) AS distance FROM vlib.velib_station a, vlib.velib_station b WHERE b.data IS NOT NULL AND a.id = ? AND a.id != b.id ORDER BY distance ASC LIMIT %d", join(', ', $this->getSelectFields('b')), $limit);

        return $this->query($sql, array($id));
    }
}
