<?php

namespace Aternos\Hawk\Tests\Integration;

use Aternos\Hawk\BlockEntity;
use Aternos\Hawk\Hawk;
use Exception;
use PHPUnit\Framework\Attributes\DataProvider;

class BlockEntityTest extends IntegrationTestCase
{
    /**
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    #[DataProvider('provideSingleBlockFile')]
    public function testGetAllBlockEntitiesFromChunk(array $blockFiles, array $entitiesFiles): void
    {
        $hawk = new Hawk(blockFiles: $blockFiles, entitiesFiles: $entitiesFiles);
        $entities = $hawk->getAllBlockEntitiesFromChunk($this->getBlockCoords());
        foreach ($entities as $entity) {
            $this->assertInstanceOf(BlockEntity::class, $entity);
        }
        $this->closeFiles($blockFiles, $entitiesFiles);
    }

    /**
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    #[DataProvider('provideNegativeSingleBlockFile')]
    public function testGetAllBlockEntitiesFromNegativeChunk(array $blockFiles, array $entitiesFiles): void
    {
        $hawk = new Hawk(blockFiles: $blockFiles, entitiesFiles: $entitiesFiles);
        $entities = $hawk->getAllBlockEntitiesFromChunk($this->getNegativeBlockCoords());
        foreach ($entities as $entity) {
            $this->assertInstanceOf(BlockEntity::class, $entity);
        }
        $this->closeFiles($blockFiles, $entitiesFiles);
    }

    /**
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    #[DataProvider('provideSingleBlockFile')]
    public function testGetBlockEntities(array $blockFiles, array $entitiesFiles): void
    {
        $hawk = new Hawk(blockFiles: $blockFiles, entitiesFiles: $entitiesFiles);
        $entities = $hawk->getBlockEntities("minecraft:furnace", $this->getBlockEntityCoords());
        foreach ($entities as $entity) {
            $this->assertInstanceOf(BlockEntity::class, $entity);
            $this->assertEquals("minecraft:furnace", $entity->getName());
            $this->assertTrue($entity->getCoordinates()->equals($this->getBlockEntityCoords()));
        }
        $this->closeFiles($blockFiles, $entitiesFiles);
    }

    /**
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    #[DataProvider('provideNegativeSingleBlockFile')]
    public function testGetNegativeBlockEntities(array $blockFiles, array $entitiesFiles): void
    {
        $hawk = new Hawk(blockFiles: $blockFiles, entitiesFiles: $entitiesFiles);
        $entities = $hawk->getBlockEntities("minecraft:furnace", $this->getNegativeBlockEntityCoords());
        foreach ($entities as $entity) {
            $this->assertInstanceOf(BlockEntity::class, $entity);
            $this->assertEquals("minecraft:furnace", $entity->getName());
            $this->assertTrue($entity->getCoordinates()->equals($this->getNegativeBlockEntityCoords()));
        }
        $this->closeFiles($blockFiles, $entitiesFiles);
    }

    /**
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    #[DataProvider('provideSingleBlockFile')]
    public function testDeleteBlockEntity(array $blockFiles, array $entitiesFiles): void
    {
        $hawk = new Hawk(blockFiles: $blockFiles, entitiesFiles: $entitiesFiles);
        $entities = $hawk->getBlockEntities("minecraft:furnace", $this->getBlockEntityCoords());
        $this->assertEquals("minecraft:furnace", $entities[0]->getName());
        $hawk->deleteBlockEntity($entities[0]);
        $hawk->save();

        $hawk = new Hawk(blockFiles: $blockFiles, entitiesFiles: $entitiesFiles);
        $entities = $hawk->getBlockEntities("minecraft:furnace", $this->getBlockEntityCoords());
        $this->assertEmpty($entities);
        $this->closeFiles($blockFiles, $entitiesFiles);
    }

    /**
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    #[DataProvider('provideNegativeSingleBlockFile')]
    public function testDeleteNegativeBlockEntity(array $blockFiles, array $entitiesFiles): void
    {
        $hawk = new Hawk(blockFiles: $blockFiles, entitiesFiles: $entitiesFiles);
        $entities = $hawk->getBlockEntities("minecraft:furnace", $this->getNegativeBlockEntityCoords());
        $this->assertEquals("minecraft:furnace", $entities[0]->getName());
        $hawk->deleteBlockEntity($entities[0]);
        $hawk->save();

        $hawk = new Hawk(blockFiles: $blockFiles, entitiesFiles: $entitiesFiles);
        $entities = $hawk->getBlockEntities("minecraft:furnace", $this->getNegativeBlockEntityCoords());
        $this->assertEmpty($entities);
        $this->closeFiles($blockFiles, $entitiesFiles);
    }
}
