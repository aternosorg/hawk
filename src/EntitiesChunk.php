<?php

namespace Aternos\Hawk;

use Aternos\Nbt\IO\Writer\ZLibCompressedStringWriter;
use Aternos\Nbt\NbtFormat;
use Aternos\Nbt\Tag\CompoundTag;
use Aternos\Nbt\Tag\ListTag;
use Aternos\Nbt\Tag\Tag;
use Exception;

class EntitiesChunk extends Chunk
{
    public function __construct(int $location, int $offset, int $compressedDataLength, int $compressionScheme, Tag $tag, McCoordinates2D $coordinates, int $version)
    {
        parent::__construct($location, $offset, $compressedDataLength, $compressionScheme, $tag, $coordinates, $version);

    }

    /**
     * @return void
     * @throws Exception
     */
    public function replaceTags(): void
    {
        $writer = (new ZLibCompressedStringWriter())->setFormat(NbtFormat::JAVA_EDITION);
        $this->tag->set($this->entitiesTagName, $this->createEntitiesTag());
        $this->tag->write($writer);
        $this->compressedData = $writer->getStringData();
        $this->compressedDataLength = strlen($this->compressedData);
    }

    /**
     * @return ListTag
     * @throws Exception
     */
    public function createEntitiesTag(): ListTag
    {
        $entities = new ListTag();
        $entities->setContentTag(CompoundTag::TYPE);
        foreach ($this->entities as $entity) {
            $entities[] = $entity->createTag();
        }
        return $entities;
    }
}