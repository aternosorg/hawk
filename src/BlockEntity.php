<?php

namespace Aternos\Hawk;

use Aternos\Nbt\Tag\CompoundTag;
use Aternos\Nbt\Tag\ListTag;
use Aternos\Nbt\Tag\Tag;
use Exception;

class BlockEntity
{
    protected McCoordinates3D $coordinates;

    protected string $name;

    protected CompoundTag $tag;

    /**
     * @throws Exception
     */
    public function __construct(Tag $tag)
    {
        if (!$tag instanceof CompoundTag) {
            throw new Exception("Wrong tag type");
        }
        $this->coordinates = new McCoordinates3D($tag->getInt("x")->getValue(),$tag->getInt("y")->getValue(),$tag->getInt("z")->getValue());
        $this->tag = $tag;
        $this->name = $this->tag["id"]->getValue();
    }

    /**
     * @return McCoordinates3D
     */
    public function getCoordinates(): McCoordinates3D
    {
        return $this->coordinates;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return CompoundTag|ListTag
     */
    public function createTag(): CompoundTag|ListTag
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