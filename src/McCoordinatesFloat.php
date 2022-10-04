<?php

namespace Aternos\Hawk;

class McCoordinatesFloat extends McCoordinates
{
    public float $y;

    public static function get3DCoordinates(McCoordinatesFloat $coordinates): McCoordinates3D
    {
        return new McCoordinates3D(floor($coordinates->x), floor($coordinates->y), floor($coordinates->z));
    }

    public function __construct(float $x, float $y, float $z)
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
     * @param McCoordinatesFloat $coordinates
     * @return float
     */
    public function getDelta(McCoordinatesFloat $coordinates): float
    {
        $delta = 0;
        $delta += abs($this->x - $coordinates->x);
        $delta += abs($this->y - $coordinates->y);
        $delta += abs($this->z - $coordinates->z);
        return $delta / 3;
    }

    /**
     * @param McCoordinatesFloat $coordinates
     * @param float $delta
     * @return bool
     */
    public function equals(McCoordinates $coordinates, float $delta = 0.0000000001): bool
    {
        return $this->getDelta($coordinates) <= $delta;
    }

//    /**
//     * @param McCoordinates $coordinates
//     * @return bool
//     */
//    public function equals(McCoordinates $coordinates): bool
//    {
//        if ($coordinates instanceof McCoordinatesFloat) {
//            return parent::equals($coordinates) && $this->y === $coordinates->y;
//        }
//        return false;
//    }

}