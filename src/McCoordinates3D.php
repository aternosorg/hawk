<?php

namespace Aternos\Hawk;

class McCoordinates3D extends McCoordinates2D
{
    public int $y;

    /**
     * @param int $x
     * @param int $y
     * @param int $z
     */
    public function __construct(int $x, int $y, int $z)
    {
        parent::__construct($x, $z);
        $this->y = $y;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->x . ", " . $this->y . ", " . $this->z;
    }

    /**
     * @param McCoordinates $coordinates
     * @return bool
     */
    public function equals(McCoordinates $coordinates): bool
    {
        if ($coordinates instanceof McCoordinates3D) {
            return parent::equals($coordinates) && $this->y === $coordinates->y;
        }
        return false;
    }
}