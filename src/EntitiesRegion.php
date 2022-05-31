<?php

namespace Aternos\Hawk;

use Aternos\Nbt\Tag\CompoundTag;
use Exception;

class EntitiesRegion extends Region
{
    /**
     * @param AbstractFile $file
     */
    public function __construct(AbstractFile $file)
    {
        parent::__construct($file);
    }

    /**
     * @param int $location
     * @param int $offset
     * @param int $compressedDataLength
     * @param int $compressionScheme
     * @param CompoundTag $tag
     * @param McCoordinates2D $coordinates
     * @param int $version
     * @return Chunk
     * @throws Exception
     */
    public function addNewChunk(int $location, int $offset, int $compressedDataLength, int $compressionScheme, CompoundTag $tag, McCoordinates2D $coordinates, int $version): Chunk
    {
        $chunk = new EntitiesChunk($location, $offset, $compressedDataLength, $compressionScheme, $tag, $coordinates, $version);
        $this->chunks[] = $chunk;
        return $chunk;
    }

    /**
     * @param string $name
     * @param McCoordinatesFloat $coordinates
     * @return Entity[]
     * @throws Exception
     */
    public function getEntities(string $name, McCoordinatesFloat $coordinates): array
    {
        $chunk = $this->getChunkFromBlock(McCoordinatesFloat::get3DCoordinates($coordinates));
        if (!($chunk instanceof EntitiesChunk)) {
            throw new Exception("Wrong chunk type");
        }
        return $chunk->getEntities($name, $coordinates);
    }

    /**
     * @param McCoordinates3D $blockCoordinates
     * @return array
     * @throws Exception
     */
    public function getAllEntitiesFromEntitiesChunk(McCoordinates3D $blockCoordinates): array
    {
        $chunk = $this->getChunkFromBlock($blockCoordinates);
        if (!($chunk instanceof EntitiesChunk)) {
            throw new Exception("Wrong chunk type");
        }

        $entities = [];
        foreach ($chunk->getAllEntities() as $entity) {
            $entities[] = $entity;
        }
        return $entities;
    }

    /**
     * @param Entity $entity
     * @return void
     * @throws Exception
     */
    public function deleteEntity(Entity $entity): void
    {
        $chunk = $this->getChunkFromBlock(McCoordinatesFloat::get3DCoordinates($entity->getCoordinates()));
        if (!($chunk instanceof EntitiesChunk)) {
            throw new Exception("Wrong chunk type");
        }
        $chunk->deleteEntity($entity);
    }
}