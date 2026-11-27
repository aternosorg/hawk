<?php

declare(strict_types=1);

namespace Aternos\Hawk;

use Aternos\Hawk\Exceptions\UnexpectedTagTypeException;
use Aternos\Nbt\Tag\CompoundTag;
use Aternos\Nbt\Tag\ListTag;
use Aternos\Nbt\Tag\Tag;

class BlockEntity
{
    protected McCoordinates3D $coordinates;

    protected string $name;

    protected CompoundTag $tag;

    /**
     * @throws UnexpectedTagTypeException
     */
    public function __construct(Tag $tag)
    {
        if (!$tag instanceof CompoundTag) {
            throw new UnexpectedTagTypeException("Block entity tag is a " . get_class($tag) . ", expected a compound tag.");
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