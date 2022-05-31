<?php

namespace Aternos\Hawk;

use Aternos\Nbt\Tag\CompoundTag;
use Exception;

class BlockRegion extends Region
{
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
        $chunkClass = VersionHelper::getChunkClassFromVersion($this->version);
        if(VersionHelper::hasLevelTag($this->version)){
            $tag = $tag->getCompound("Level");
        }
        $chunk = new $chunkClass($location, $offset, $compressedDataLength, $compressionScheme, $tag, $coordinates, $version);
        $this->chunks[] = $chunk;
        return $chunk;
    }

    /**
     * @param McCoordinates3D $coordinates
     * @return DataBlock
     * @throws Exception
     */
    public function getBlock(McCoordinates3D $coordinates): DataBlock
    {
        $chunk = $this->getChunkFromBlock($coordinates);
        if ($chunk instanceof BlockChunk) {
            return $chunk->getBlock($coordinates);
        }
        throw new Exception("Wrong chunk type");
    }

    /**
     * @param string $name
     * @param McCoordinatesFloat $coordinates
     * @return Entity[]
     * @throws Exception
     */
    public function getEntities(string $name,McCoordinatesFloat $coordinates): array
    {
        $chunk = $this->getChunkFromBlock(McCoordinatesFloat::get3DCoordinates($coordinates));

        if(!VersionHelper::hasEntitiesTag($this->version)){
            throw new Exception("Entities not stored in block region");
        }
        if (!($chunk instanceof BlockChunk)) {
            throw new Exception("Wrong chunk type");
        }
        return $chunk->getEntities($name, $coordinates);
    }

    /**
     * @param McCoordinates3D $blockCoordinates
     * @return array
     * @throws Exception
     */
    public function getAllEntitiesFromBlockChunk(McCoordinates3D $blockCoordinates): array
    {
        $chunk = $this->getChunkFromBlock($blockCoordinates);
        if (!($chunk instanceof BlockChunk)) {
            throw new Exception("Wrong chunk type");
        }
        if(!VersionHelper::hasEntitiesTag($this->version)){
            throw new Exception("Entities not stored in block region");
        }

        $entities = [];
        foreach ($chunk->getAllEntities() as $entity) {
            $entities[] = $entity;
        }
        return $entities;
    }

    /**
     * Reads chunk and replaces block at $coordinates with $blockName
     *
     * @codeCoverageIgnore
     * @param McCoordinates3D $coordinates
     * @param string $blockName
     * @return void
     * @throws Exception
     */
    public function replaceBlock(McCoordinates3D $coordinates, string $blockName = "minecraft:stone"): void
    {
        $chunk = $this->getChunkFromBlock($coordinates);
        if ($chunk instanceof BlockChunk) {
            $chunk->replaceBlock($coordinates, $blockName);
            return;
        }
        throw new Exception("Wrong chunk type");

    }

    /**
     * @param Entity $entity
     * @return void
     * @throws Exception
     */
    public function deleteEntity(Entity $entity):void
    {
        $chunk = $this->getChunkFromBlock(McCoordinatesFloat::get3DCoordinates($entity->getCoordinates()));
        if ($chunk instanceof BlockChunk) {
            $chunk->deleteEntity($entity);
            return;
        }
        throw new Exception("Wrong chunk type");
    }
}