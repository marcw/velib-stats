<?php

namespace Model\Pomm\Entity\Vlib;

use Model\Pomm\Entity\Vlib\Base\VelibStationDataMap as BaseVelibStationDataMap;
use Pomm\Exception\Exception;
use Pomm\Query\Where;

class VelibStationDataMap extends BaseVelibStationDataMap
{
    protected function findOrderByCreatedAt($alias, Where $where = null, $limit = 0, $asc = true)
    {
        $suffix = sprintf("ORDER BY %s.created_at %s %s",
            $alias,
            $asc ? 'ASC' : 'DESC',
            $limit ? sprintf("LIMIT %d", $limit) : ''
        );

        $where_string = is_null($where) ? '' : sprintf("WHERE %s", $where);

        $sql = sprintf("SELECT %s FROM %s data %s %s",
            join(', ', $this->getSelectFields($alias)),
            $this->getTableName(),
            $where_string,
            $suffix
        );

        return $this->query($sql, $where->getValues());
    }

    public function getLast($station_id, $n = 1)
    {
        $where = Where::create("data.station_id = ?", array($station_id));

        return $this->findOrderByCreatedAt('data', $where, $n, false);
    }

    public function getOlderUntil($station_id, $days)
    {
        $where = Where::create("data.station_id = ?", array($station_id))
            ->andWhere("(now()::timestamp - data.created_at) < ?", array(sprintf("'%dd'", $days)));

        return $this->findOrderByCreatedAt('data', $where);
    }
}
