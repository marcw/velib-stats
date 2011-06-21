<?php

namespace Model\Pomm\Entity\Vlib;

use Pomm\Object\BaseObject;
use Pomm\Exception\Exception;
use Pomm\Type\Point;

class VelibStation extends BaseObject
{
    public function convert(Array $data)
    {
        $x = 0;
        $y = 0;
        $tmp = array();

        foreach($data as $key => $value)
        {
            if ($key == 'lat')
            {
                $x = $value;
                continue;
            }
            if ($key == 'lng')
            {
                $y = $value;
                continue;
            }

            $tmp[strtolower($key)] = $value;
        }

        $tmp['coord'] = new Point($x, $y);

        $this->hydrate($tmp);
    }
}
