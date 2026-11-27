<?php

namespace Aternos\Hawk\Tests\Integration;

use Aternos\Hawk\Entity;
use Aternos\Hawk\Hawk;
use Exception;
use PHPUnit\Framework\Attributes\DataProvider;

class EntityTest extends IntegrationTestCase
{
    protected static function getTestFilesDirectory(): string
    {
        return __DIR__ . "/../files/entities/versions";
    }

    /**
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    #[DataProvider('provideSingleBlockFile')]
    public function testGetAllEntitiesFromChunk(array $blockFiles, array $entitiesFiles): void
    {
        $hawk = new Hawk(blockFiles: $blockFiles, entitiesFiles: $entitiesFiles);
        $entities = $hawk->getAllEntitiesFromChunk($this->getBlockCoords());
        foreach ($entities as $entity) {
            $this->assertInstanceOf(Entity::class, $entity);
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
    public function testGetAllEntitiesFromNegativeChunk(array $blockFiles, array $entitiesFiles): void
    {
        $hawk = new Hawk(blockFiles: $blockFiles, entitiesFiles: $entitiesFiles);
        $entities = $hawk->getAllEntitiesFromChunk($this->getNegativeBlockCoords());
        foreach ($entities as $entity) {
            $this->assertInstanceOf(Entity::class, $entity);
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
    public function testGetEntities(array $blockFiles, array $entitiesFiles): void
    {
        $hawk = new Hawk(blockFiles: $blockFiles, entitiesFiles: $entitiesFiles);
        $entities = $hawk->getEntities("minecraft:chicken", $this->getEntityCoords());
        foreach ($entities as $entity) {
            $this->assertInstanceOf(Entity::class, $entity);
            $this->assertEquals("minecraft:chicken", $entity->getName());
            $this->assertTrue($entity->getCoordinates()->equals($this->getExactEntityCoords()));
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
    public function testGetNegativeEntities(array $blockFiles, array $entitiesFiles): void
    {
        $hawk = new Hawk(blockFiles: $blockFiles, entitiesFiles: $entitiesFiles);
        $entities = $hawk->getEntities("minecraft:chicken", $this->getNegativeEntityCoords());
        foreach ($entities as $entity) {
            $this->assertInstanceOf(Entity::class, $entity);
            $this->assertEquals("minecraft:chicken", $entity->getName());
            $this->assertTrue($entity->getCoordinates()->equals($this->getExactNegativeEntityCoords(), 0.1));
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
    public function testDeleteEntity(array $blockFiles, array $entitiesFiles): void
    {
        $hawk = new Hawk(blockFiles: $blockFiles, entitiesFiles: $entitiesFiles);
        $entities = $hawk->getEntities("minecraft:chicken", $this->getEntityCoords());
        $this->assertEquals("minecraft:chicken", $entities[0]->getName());
        $hawk->deleteEntity($entities[0]);
        $hawk->save();

        $hawk = new Hawk(blockFiles: $blockFiles, entitiesFiles: $entitiesFiles);
        $entities = $hawk->getEntities("minecraft:chicken", $this->getEntityCoords());
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
    public function testDeleteNegativeEntity(array $blockFiles, array $entitiesFiles): void
    {
        $hawk = new Hawk(blockFiles: $blockFiles, entitiesFiles: $entitiesFiles);
        $entities = $hawk->getEntities("minecraft:chicken", $this->getNegativeEntityCoords());
        $this->assertEquals("minecraft:chicken", $entities[0]->getName());
        $hawk->deleteEntity($entities[0]);
        $hawk->save();

        $hawk = new Hawk(blockFiles: $blockFiles, entitiesFiles: $entitiesFiles);
        $entities = $hawk->getEntities("minecraft:chicken", $this->getNegativeEntityCoords());
        $this->assertEmpty($entities);
        $this->closeFiles($blockFiles, $entitiesFiles);
    }
}
