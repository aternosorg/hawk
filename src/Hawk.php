<?php

namespace Aternos\Hawk;

use Exception;

class Hawk
{
    public bool $hasNoEntitiesRegions;

    /**
     * @var BlockRegion[]
     */
    protected array $blockRegions = [];

    /**
     * @var EntitiesRegion[]
     */
    protected array $entitiesRegions = [];

    /**
     * @param AbstractFile[] $blockFiles
     * @param AbstractFile[] $entitiesFiles
     * @throws Exception
     */
    public function __construct(array $blockFiles = [], array $entitiesFiles = [])
    {
        if (empty($blockFiles) && empty($entitiesFiles)) {
            throw new Exception("Neither block files nor entities files found.");
        }

        foreach ($blockFiles as $file) {
            if (!($file instanceof AbstractFile)) {
                throw new Exception("Wrong file type for block files.");
            }
        }

        foreach ($entitiesFiles as $file) {
            if (!($file instanceof AbstractFile)) {
                throw new Exception("Wrong file type for entities files.");
            }
        }

        $this->loadBlockRegions($blockFiles);
        $this->loadEntitiesRegions($entitiesFiles);
        $this->hasNoEntitiesRegions = empty($this->entitiesRegions);
    }

    /**
     * Checks for duplicated regions
     */
    public function loadBlockRegions(array $files): void
    {
        foreach ($files as $file) {
            $newBlockRegion = new BlockRegion($file);
            if (empty($this->blockRegions)) {
                $this->blockRegions[] = $newBlockRegion;
            } else {
                foreach ($this->blockRegions as $region) {
                    if (!$region->getCoordinates()->equals($newBlockRegion->getCoordinates())) {
                        $this->blockRegions[] = new BlockRegion($file);
                    }
                }
            }
        }
    }

    /**
     * Checks for duplicated regions
     */
    public function loadEntitiesRegions(array $files): void
    {
        foreach ($files as $file) {
            $newEntitiesRegion = new EntitiesRegion($file);
            if (empty($this->entitiesRegions)) {
                $this->entitiesRegions[] = $newEntitiesRegion;
            } else {
                foreach ($this->entitiesRegions as $region) {
                    if (!$region->getCoordinates()->equals($newEntitiesRegion->getCoordinates())) {
                        $this->entitiesRegions[] = new EntitiesRegion($file);
                    }
                }
            }
        }
    }

    /**
     * @codeCoverageIgnore
     * @return BlockRegion[] All regions
     */
    public function getBlockRegions(): array
    {
        return $this->blockRegions;
    }

    /**
     * @return EntitiesRegion[]
     */
    public function getEntitiesRegions(): array
    {
        return $this->entitiesRegions;
    }

    /**
     * @param McCoordinates3D $coordinates
     * @return BlockRegion
     * @throws Exception
     */
    public function getBlockRegionFromBlock(McCoordinates3D $coordinates): BlockRegion
    {
        $region = $this->getRegion($this->blockRegions, Region::getRegionCoordinatesFromBlockCoordinates($coordinates));
        if (!($region instanceof BlockRegion)){
            throw new Exception("Not a block region.");
        }
        return $region;
    }

    /**
     * @param McCoordinates3D $coordinates
     * @return EntitiesRegion
     * @throws Exception
     */
    public function getEntitiesRegionFromBlock(McCoordinates3D $coordinates): EntitiesRegion
    {
        $region = $this->getRegion($this->entitiesRegions, Region::getRegionCoordinatesFromBlockCoordinates($coordinates));
        if (!($region instanceof EntitiesRegion)){
            throw new Exception("Not a entities region.");
        }
        return $region;
    }

    /**
     * @param McCoordinates3D $coordinates
     * @return EntitiesRegion
     * @throws Exception
     */
    public function getBlockRegionFromChunk(McCoordinates2D $coordinates): Region
    {
        return $this->getRegion($this->blockRegions, Region::getRegionCoordinatesFromChunkCoordinates($coordinates));
    }

    /**
     * @param McCoordinates3D $coordinates
     * @return EntitiesRegion
     * @throws Exception
     */
    public function getEntitiesRegionFromChunk(McCoordinates2D $coordinates): Region
    {
        return $this->getRegion($this->entitiesRegions, Region::getRegionCoordinatesFromChunkCoordinates($coordinates));
    }

    /**
     * @param array $regions
     * @param McCoordinates $coordinates
     * @return Region
     * @throws Exception
     */
    protected function getRegion(array $regions, McCoordinates $coordinates): Region
    {
        foreach ($regions as $region) {
            if ($region->getCoordinates()->equals($coordinates)) {
                return $region;
            }
        }
        $message = (!empty($regions)) ? get_class($regions[0]) . " not found." : "Empty array.";
        throw new Exception($message);
    }

    /**
     * @param McCoordinates3D $coordinates
     * @return DataBlock Block at $coordinates
     * @throws Exception "Section not found.";
     */
    public function getBlock(McCoordinates3D $coordinates): DataBlock
    {
        return $this->getBlockRegionFromBlock($coordinates)->getBlock($coordinates);
    }

    /**
     * Replaces a block at $coordinates with $blockName
     *
     * @codeCoverageIgnore
     * @param McCoordinates3D $coordinates
     * @param string $blockName
     * @return void
     * @throws Exception "Wrong compression scheme"
     */
    public function replaceBlock(McCoordinates3D $coordinates, string $blockName = "minecraft:stone"): void
    {
        $this->getBlockRegionFromBlock($coordinates)->replaceBlock($coordinates, $blockName);
    }

    /**
     * @param string $name
     * @param McCoordinatesFloat $coordinates
     * @return Entity[]
     * @throws Exception
     */
    public function getEntities(string $name, McCoordinatesFloat $coordinates): array
    {
        if ($this->hasNoEntitiesRegions) {
            return $this->getEntitiesFromBlockRegion($name, $coordinates);
        } else {
            return $this->getEntitiesFromEntitiesRegion($name, $coordinates);
        }

    }

    /**
     * @param string $name
     * @param McCoordinatesFloat $coordinates
     * @return array
     * @throws Exception
     */
    protected function getEntitiesFromEntitiesRegion(string $name, McCoordinatesFloat $coordinates): array
    {
        $region = $this->getEntitiesRegionFromBlock(McCoordinatesFloat::get3DCoordinates($coordinates));
        if ($region instanceof EntitiesRegion) {
            return $region->getEntities($name, $coordinates);
        }
        throw new Exception("Wrong region type");
    }

    /**
     * @param string $name
     * @param McCoordinatesFloat $coordinates
     * @return array
     * @throws Exception
     */
    protected function getEntitiesFromBlockRegion(string $name, McCoordinatesFloat $coordinates): array
    {
        $region = $this->getBlockRegionFromBlock(McCoordinatesFloat::get3DCoordinates($coordinates));
        if ($region instanceof BlockRegion) {
            return $region->getEntities($name, $coordinates);
        }
        throw new Exception("Wrong region type");
    }

    /**
     * @param McCoordinates3D $blockCoordinates
     * @return array
     * @throws Exception
     */
    public function getAllEntitiesFromChunk(McCoordinates3D $blockCoordinates): array
    {
        if ($this->hasNoEntitiesRegions) {
            return $this->getAllEntitiesFromBlockChunk($blockCoordinates);
        } else {
            return $this->getAllEntitiesFromEntitiesChunk($blockCoordinates);
        }
    }

    /**
     * @param McCoordinates3D $blockCoordinates
     * @return array
     * @throws Exception
     */
    protected function getAllEntitiesFromEntitiesChunk(McCoordinates3D $blockCoordinates): array
    {
        $entities = [];
        foreach ($this->getEntitiesRegionFromBlock($blockCoordinates)->getAllEntitiesFromEntitiesChunk($blockCoordinates) as $entity) {
            $entities[] = $entity;
        }
        return $entities;
    }

    /**
     * @param McCoordinates3D $blockCoordinates
     * @return array
     * @throws Exception
     */
    protected function getAllEntitiesFromBlockChunk(McCoordinates3D $blockCoordinates): array
    {
        $entities = [];
        foreach ($this->getBlockRegionFromBlock($blockCoordinates)->getAllEntitiesFromBlockChunk($blockCoordinates) as $entity) {
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
        if ($this->hasNoEntitiesRegions) {
            $this->deleteEntityFromBlockChunk($entity);
        } else {
            $this->deleteEntityFromEntityChunk($entity);
        }
    }

    /**
     * @param Entity $entity
     * @return void
     * @throws Exception
     */
    protected function deleteEntityFromEntityChunk(Entity $entity): void
    {
        $region = $this->getEntitiesRegionFromBlock(McCoordinatesFloat::get3DCoordinates($entity->getCoordinates()));
        if ($region instanceof EntitiesRegion) {
            $region->deleteEntity($entity);
            return;
        }
        throw new Exception("Wrong region type");
    }

    /**
     * @param Entity $entity
     * @return void
     * @throws Exception
     */
    protected function deleteEntityFromBlockChunk(Entity $entity): void
    {
        $region = $this->getBlockRegionFromBlock(McCoordinatesFloat::get3DCoordinates($entity->getCoordinates()));
        if ($region instanceof BlockRegion) {
            $region->deleteEntity($entity);
            return;
        }
        throw new Exception("Wrong region type");
    }

    /**
     * Saves every region to its file
     *
     * @return void
     * @throws Exception "Error while seeking/writing"
     */
    public function save(): void
    {
        foreach ($this->blockRegions as $region) {
            $region->save();
        }
        foreach ($this->entitiesRegions as $region) {
            $region->save();
        }
    }
}