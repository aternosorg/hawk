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