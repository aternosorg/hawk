<?php

namespace Aternos\Hawk;

use Aternos\Nbt\Tag\CompoundTag;
use Aternos\Nbt\Tag\ListTag;
use Aternos\Nbt\Tag\Tag;
use Exception;

abstract class Chunk
{
    protected int $location;

    protected int $offset;

    protected int $dataLength;

    protected int $compressedDataLength;

    protected int $compressionScheme;

    protected int $version;

    protected string $compressedData;

    protected string $entitiesTagName = "Entities";

    protected CompoundTag $tag;

    protected McCoordinates2D $coordinates;

    /**
     * @var Entity[]
     */
    protected array $entities = [];

    /**
     * Calculates chunk coordinates from $coordinates
     *
     * @param McCoordinates3D $coordinates
     * @return McCoordinates2D Chunk coordinates
     */
    public static function getChunkCoordinatesFromBlock(McCoordinates3D $coordinates): McCoordinates2D
    {
        return new McCoordinates2D(floor($coordinates->x / 16), floor($coordinates->z / 16));
    }

    /**
     * @param int $location
     * @param int $offset
     * @param int $compressedDataLength
     * @param int $compressionScheme
     * @param Tag $tag
     * @param McCoordinates2D $coordinates
     * @param int $version
     * @throws Exception
     */
    public function __construct(int $location, int $offset, int $compressedDataLength, int $compressionScheme, Tag $tag, McCoordinates2D $coordinates, int $version)
    {
        $this->location = $location;
        $this->offset = $offset;
        $this->compressedDataLength = $compressedDataLength;
        $this->compressionScheme = $compressionScheme;
        $this->coordinates = $coordinates;
        if (!$tag instanceof CompoundTag) throw new Exception("Wrong tag type");
        $this->tag = $tag;
        $this->version = $version;
        $this->loadEntities();
    }

    /**
     * @return void
     * @throws Exception
     */
    public function loadEntities(): void
    {
        $entities = $this->readEntitiesTag();
        if ($entities !== null) {
            foreach ($entities as $entity) {
                $this->entities[] = new Entity($entity);
            }
        }
    }

    /**
     * @codeCoverageIgnore
     * @return McCoordinates2D
     */
    public function getCoordinates(): McCoordinates2D
    {
        return $this->coordinates;
    }

    /**
     * @codeCoverageIgnore
     * @return int Offset in region file
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @codeCoverageIgnore
     * @param $offset
     * @return void
     */
    public function setOffset($offset): void
    {
        $this->offset = $offset;
    }

    /**
     * @return string
     */
    public function getCompressedDataLength(): string
    {
        return AbstractFile::uInt32BigEndianToString($this->compressedDataLength + 1);
    }

    /**
     * @return int
     */
    public function getCompressionScheme(): int
    {
        return $this->compressionScheme;
    }

    /**
     * @return string
     */
    public function getCompressedData(): string
    {
        return $this->compressedData;
    }

    /**
     * @codeCoverageIgnore
     * @param $length
     * @return void
     */
    public function setDataLength($length): void
    {
        $this->dataLength = $length;
    }

    /**
     * @codeCoverageIgnore
     * @return int
     */
    public function getLength(): int
    {
        return $this->dataLength;
    }

    /**
     * @codeCoverageIgnore
     * @return CompoundTag
     */
    public function getTag(): CompoundTag
    {
        return $this->tag;
    }

    /**
     * @return string
     */
    protected function getChunkDataRaw(): string
    {
        return $this->getCompressedDataLength() . chr($this->getCompressionScheme()) . $this->getCompressedData();
    }

    /**
     * Pads zeros to the right of ChunkDataRaw until a multiple of 4096 is reached
     *
     * @return string
     * @throws Exception
     */
    public function getChunkData(): string
    {
        $this->replaceTags();
        $data = $this->getChunkDataRaw();
        $dataLength = strlen($data);
        $padding = 4096 - ($dataLength % 4096);
        $data = str_pad($data, $dataLength + $padding, chr(0), STR_PAD_RIGHT);
        $this->setDataLength(strlen($data));
        return $data;
    }

    /**
     * @codeCoverageIgnore
     * @return string
     */
    public function getLocation(): string
    {
        $length = $this->getLength();
        /*
         * Offset: 3 Byte
         * Length: 1 Byte
         * Location: 4 Byte (Offset + Length)
         *
         *
         * Shift offset 8 bits to the left
         */
        $location = $this->getOffset() << 8;
        // Add length to Location
        return AbstractFile::uInt32BigEndianToString($location | ceil($length / 4096));
    }

    /**
     * @return ListTag|null
     */
    public function readEntitiesTag(): ?ListTag
    {
        return $this->tag->getList($this->entitiesTagName, CompoundTag::TYPE);
    }

    /**
     * This function returns all entities with a position offset of <= $delta and name of $name
     *
     * While loop is only for safety.
     * If there is no match in the first loop it increases the delta and loops up to 10 times.
     *
     * @param string $name
     * @param McCoordinatesFloat $coordinates
     * @return Entity[]
     */
    public function getEntities(string $name, McCoordinatesFloat $coordinates): array
    {
        $counter = 0;
        $delta = 0.000000000001;
        $entities = [];
        $found = false;
        $results = [];

        // Filters entities by name for the while loop
        foreach ($this->entities as $entity) {
            if ($entity->getName() === $name) {
                $entityDelta = $entity->getCoordinates()->getDelta($coordinates);
                if ($entityDelta <= $delta) {
                    $found = true;
                    $results[] = $entity;
                }
                $entities[] = [$entityDelta, $entity];
            }
        }
        if ($found === true) {
            return $results;
        }
        if (count($entities) === 0) {
            return [];
        }
        // Increases delta up to 13 times
        while ($counter < 13) {
            $results = [];
            foreach ($entities as $entity) {
                if ($entity[1]->getCoordinates()->getDelta($coordinates) <= $delta) {
                    $results[] = $entity[1];
                    $found = true;
                }
            }
            if ($found === true) {
                return $results;
            }
            $delta = $delta * 10;
            $counter++;
        }
        return [];
    }

    /**
     * @return Entity[]
     * @throws Exception
     */
    public function getAllEntities(): array
    {
        if (empty($this->entities)) {
            throw new Exception("No entities found.");
        }
        return $this->entities;
    }

    /**
     * @param Entity $input
     * @return void
     * @throws Exception
     */
    public function deleteEntity(Entity $input): void
    {
        foreach ($this->entities as $index => $entity) {
            if ($entity === $input) {
                unset($this->entities[$index]);
                return;
            }
        }
        throw new Exception("Entity not found.");
    }

    /**
     * @return void
     */
    abstract public function replaceTags(): void;
}