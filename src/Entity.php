<?php

namespace Aternos\Hawk;

use Aternos\Nbt\Tag\CompoundTag;

class Entity
{
    protected string $name;

    protected McCoordinatesFloat $coordinates;

    protected CompoundTag $tag;

    public function __construct(CompoundTag $tag)
    {
        $this->tag = $tag;
        $pos = $tag->getList("Pos");
        $this->name = $tag->getString("id")->getValue();
        $this->coordinates = new McCoordinatesFloat($pos[0]->getValue(), $pos[1]->getValue(), $pos[2]->getValue());
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return McCoordinatesFloat
     */
    public function getCoordinates(): McCoordinatesFloat
    {
        return $this->coordinates;
    }

    /**
     * @param McCoordinatesFloat $coordinates
     * @param float $delta
     * @return bool
     */
    public function equals(McCoordinatesFloat $coordinates, float $delta = 0.0000000001): bool
    {
        return $this->getDelta($coordinates) <= $delta;
    }

    /**
     * @param McCoordinatesFloat $coordinates
     * @return float
     */
    public function getDelta(McCoordinatesFloat $coordinates): float
    {
        $delta = 0;
        $delta += abs($this->coordinates->x - $coordinates->x);
        $delta += abs($this->coordinates->y - $coordinates->y);
        $delta += abs($this->coordinates->z - $coordinates->z);
        return $delta / 3;
    }

    /**
     * @return CompoundTag
     */
    public function createTag(): CompoundTag
    {
        return $this->tag;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->name . " at: " . $this->coordinates;
    }
}