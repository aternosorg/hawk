<?php

namespace Aternos\Hawk;

class McCoordinates2D extends McCoordinates
{
    /**
     * @param int $x
     * @param int $z
     */
    public function __construct(int $x, int $z)
    {
        parent::__construct($x, $z);
    }
}