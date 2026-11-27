<?php

declare(strict_types=1);

namespace Aternos\Hawk;

use Aternos\Hawk\Exceptions\FileException;
use Aternos\Hawk\Exceptions\InvalidCompressionSchemeException;
use Aternos\Hawk\Exceptions\InvalidRegionFilesException;
use Aternos\Hawk\Exceptions\RegionNotFoundException;
use Aternos\Hawk\Exceptions\SectionNotFoundException;
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
     * @throws InvalidRegionFilesException
     */
    public function __construct(array $blockFiles = [], array $entitiesFiles = [])
    {
        if (empty($blockFiles) && empty($entitiesFiles)) {
            throw new InvalidRegionFilesException("Neither block files nor entities files found.");
        }

        foreach ($blockFiles as $file) {
            if (!($file instanceof AbstractFile)) {
                throw new InvalidRegionFilesException("Block file is a " . get_debug_type($file) . ", expected " . AbstractFile::class . ".");
            }
        }

        foreach ($entitiesFiles as $file) {
            if (!($file instanceof AbstractFile)) {
                throw new InvalidRegionFilesException("Entities file is a " . get_debug_type($file) . ", expected " . AbstractFile::class . ".");
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
        if (!($region instanceof BlockRegion)) {
            throw new RegionNotFoundException("No block region file found for region " . $region->getCoordinates() . ".");
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
        if (!($region instanceof EntitiesRegion)) {
            throw new RegionNotFoundException("No entities region file found for region " . $region->getCoordinates() . ".");
        }
        return $region;
    }

    /**
     * @param McCoordinates2D $coordinates
     * @return BlockRegion
     * @throws Exception
     */
    public function getBlockRegionFromChunk(McCoordinates2D $coordinates): BlockRegion
    {
        $region = $this->getRegion($this->blockRegions, Region::getRegionCoordinatesFromChunkCoordinates($coordinates));
        if (!($region instanceof BlockRegion)) {
            throw new RegionNotFoundException("No block region file found for region " . $region->getCoordinates() . ".");
        }
        return $region;
    }

    /**
     * @param McCoordinates2D $coordinates
     * @return EntitiesRegion
     * @throws Exception
     */
    public function getEntitiesRegionFromChunk(McCoordinates2D $coordinates): EntitiesRegion
    {
        $region = $this->getRegion($this->entitiesRegions, Region::getRegionCoordinatesFromChunkCoordinates($coordinates));
        if (!($region instanceof EntitiesRegion)) {
            throw new RegionNotFoundException("No entities region file found for region " . $region->getCoordinates() . ".");
        }
        return $region;
    }

    /**
     * @param array $regions
     * @param McCoordinates $coordinates
     * @return Region
     * @throws RegionNotFoundException
     */
    protected function getRegion(array $regions, McCoordinates $coordinates): Region
    {
        foreach ($regions as $region) {
            if ($region->getCoordinates()->equals($coordinates)) {
                return $region;
            }
        }
        throw new RegionNotFoundException("No region file loaded for region " . $coordinates . ".");
    }

    /**
     * @param McCoordinates3D $coordinates
     * @return DataBlock Block at $coordinates
     * @throws SectionNotFoundException
     * @throws Exception
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
     * @throws InvalidCompressionSchemeException
     * @throws Exception
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
    public function getBlockEntities(string $name, McCoordinatesFloat $coordinates): array
    {
        $region = $this->getBlockRegionFromBlock(McCoordinatesFloat::get3DCoordinates($coordinates));
        return $region->getBlockEntities($name, $coordinates);
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
        return $region->getEntities($name, $coordinates);
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
        return $region->getEntities($name, $coordinates);
    }

    /**
     * @param McCoordinates3D $blockCoordinates
     * @return array
     * @throws Exception
     */
    public function getAllEntitiesFromChunk(McCoordinates3D $blockCoordinates): array
    {
        if ($this->hasNoEntitiesRegions) {
            return $this->getBlockRegionFromBlock($blockCoordinates)->getAllEntitiesFromBlockChunk($blockCoordinates);
        } else {
            return $this->getEntitiesRegionFromBlock($blockCoordinates)->getAllEntitiesFromEntitiesChunk($blockCoordinates);
        }
    }

    public function getAllBlockEntitiesFromChunk(McCoordinates3D $blockCoordinates): array
    {
        return $this->getBlockRegionFromBlock($blockCoordinates)->getAllBlockEntitiesFromBlockChunk($blockCoordinates);
    }

    /**
     * @param Entity $entity
     * @return void
     * @throws Exception
     */
    public function deleteEntity(Entity $entity): void
    {
        if ($this->hasNoEntitiesRegions) {
            $region = $this->getBlockRegionFromBlock(McCoordinatesFloat::get3DCoordinates($entity->getCoordinates()));
        } else {
            $region = $this->getEntitiesRegionFromBlock(McCoordinatesFloat::get3DCoordinates($entity->getCoordinates()));
        }
        $region->deleteEntity($entity);
    }

    /**
     * @param BlockEntity $entity
     * @return void
     * @throws Exception
     */
    public function deleteBlockEntity(BlockEntity $entity): void
    {
        $region = $this->getBlockRegionFromBlock($entity->getCoordinates());
        $region->deleteBlockEntity($entity);
    }

    /**
     * Saves every region to its file
     *
     * @return void
     * @throws FileException
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