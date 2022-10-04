<?php

namespace Aternos\Hawk;

abstract class McCoordinates
{
    public int|float $x;
    public int|float $z;

    /**
     * @param int|float $x
     * @param int|float $z
     */
    public function __construct(int|float $x, int|float $z)
    {
        $this->x = $x;
        $this->z = $z;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->x . ", " . $this->z;
    }

    /**
     * @param McCoordinates $coordinates
     * @return bool
     */
    public function equals(McCoordinates $coordinates): bool
    {
        return floatval($this->x) === floatval($coordinates->x) && floatval($this->z) === floatval($coordinates->z);
    }
}