<?php

declare(strict_types=1);

namespace Aternos\Hawk\Tests\Unit;

use Aternos\Hawk\BlockEntity;
use Aternos\Hawk\BlockRegion;
use Aternos\Hawk\DataBlock;
use Aternos\Hawk\EntitiesRegion;
use Aternos\Hawk\Entity;
use Aternos\Hawk\Exceptions\VersionNotSupportedException;
use Aternos\Hawk\Hawk;
use Aternos\Hawk\Property;
use Exception;
use PHPUnit\Framework\Attributes\DataProvider;

class HawkTest extends HawkTestCase
{
    public function testConstructorThrowsExceptionWithEmptyArrays(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Neither block files nor entities files found.");
        new Hawk([], []);
    }

    public function testConstructorThrowsExceptionWithWrongFileTypes(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Wrong file type for block files.");
        new Hawk([new Property("lit", "true")], []);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Wrong file type for entities files.");
        new Hawk([], new Property("lit", "true"));
    }

    /**
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    #[DataProvider('provideSingleBlockFile')]
    public function testConstructorCallsLoadBlockRegions(array $blockFiles, array $entitiesFiles): void
    {
        $mock = $this->getMockBuilder(Hawk::class)
            ->setConstructorArgs([$blockFiles, $entitiesFiles])
            ->onlyMethods(['loadBlockRegions', 'loadEntitiesRegions'])
            ->getMock();
        $mock
            ->expects($this->once())
            ->method('loadBlockRegions');
        $mock
            ->expects($this->once())
            ->method('loadEntitiesRegions');
        $mock->__construct($blockFiles, $entitiesFiles);
        $this->closeFiles($blockFiles, $entitiesFiles);
    }

    /**
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    #[DataProvider('provideSingleBlockFile')]
    public function testLoadBlockRegionsEmptyBlockRegions(array $blockFiles, array $entitiesFiles): void
    {
        $hawk = new Hawk($blockFiles, $entitiesFiles);
        $region = new BlockRegion($blockFiles[0]);
        $regions = $hawk->getBlockRegions();
        $this->assertEquals($region, $regions[0]);
        $this->assertCount(1, $regions);
        $this->closeFiles($blockFiles, $entitiesFiles);
    }

    /**
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    #[DataProvider('provideMultipleBlockFiles')]
    public function testLoadBlockRegionsMultipleFiles(array $blockFiles, array $entitiesFiles): void
    {
        $hawk = new Hawk($blockFiles, $entitiesFiles);
        foreach ($blockFiles as $file) {
            $regions[] = new BlockRegion($file);
        }
        $hawkRegions = $hawk->getBlockRegions();
        $this->assertEquals($regions, $hawkRegions);
        $this->assertCount(2, $hawkRegions);
        $this->closeFiles($blockFiles, $entitiesFiles);
    }

    /**
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    #[DataProvider('provideMultipleEqualBlockFiles')]
    public function testLoadBlockRegionsMultipleEqualFiles(array $blockFiles, array $entitiesFiles): void
    {
        $hawk = new Hawk($blockFiles, $entitiesFiles);
        foreach ($blockFiles as $file) {
            $regions[] = new BlockRegion($file);
        }
        $hawkRegions = $hawk->getBlockRegions();
        $this->assertNotEquals($regions, $hawkRegions);
        $this->assertCount(1, $hawkRegions);
        $this->closeFiles($blockFiles, $entitiesFiles);
    }

    /**
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    #[DataProvider('provideSingleBlockFile')]
    public function testLoadEntitiesRegionsEmptyEntitiesRegions(array $blockFiles, array $entitiesFiles): void
    {
        $hawk = new Hawk($blockFiles, $entitiesFiles);
        if (empty($entitiesFiles)) {
            $this->assertTrue($hawk->hasNoEntitiesRegions);
            return;
        }
        $region = new EntitiesRegion($entitiesFiles[0]);
        $regions = $hawk->getEntitiesRegions();
        $this->assertEquals($region, $regions[0]);
        $this->assertCount(1, $regions);
        $this->closeFiles($blockFiles, $entitiesFiles);
    }

    /**
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    #[DataProvider('provideMultipleBlockFiles')]
    public function testLoadEntitiesRegionsMultipleFiles(array $blockFiles, array $entitiesFiles): void
    {
        $hawk = new Hawk($blockFiles, $entitiesFiles);
        if (empty($entitiesFiles)) {
            $this->assertTrue($hawk->hasNoEntitiesRegions);
            return;
        }
        foreach ($entitiesFiles as $file) {
            $regions[] = new EntitiesRegion($file);
        }
        $hawkRegions = $hawk->getEntitiesRegions();
        $this->assertEquals($regions, $hawkRegions);
        $this->assertCount(2, $hawkRegions);
        $this->closeFiles($blockFiles, $entitiesFiles);
    }


    /**
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    #[DataProvider('provideMultipleEqualBlockFiles')]
    public function testLoadEntitiesRegionsMultipleEqualFiles(array $blockFiles, array $entitiesFiles): void
    {
        $hawk = new Hawk($blockFiles, $entitiesFiles);
        if (empty($entitiesFiles)) {
            $this->assertTrue($hawk->hasNoEntitiesRegions);
            return;
        }
        foreach ($entitiesFiles as $file) {
            $regions[] = new EntitiesRegion($file);
        }
        $hawkRegions = $hawk->getEntitiesRegions();
        $this->assertNotEquals($regions, $hawkRegions);
        $this->assertCount(1, $hawkRegions);
        $this->closeFiles($blockFiles, $entitiesFiles);
    }

    /**
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    #[DataProvider('provideMultipleBlockFiles')]
    public function testGetBlockRegionFromBlock(array $blockFiles, array $entitiesFiles): void
    {
        $hawk = new Hawk($blockFiles, $entitiesFiles);
        $region = $hawk->getBlockRegionFromBlock($this->getBlockCoords());
        $this->assertInstanceOf(BlockRegion::class, $region);
        $this->assertEquals($this->getRegionCoords(), $region->getCoordinates());
        $region = $hawk->getBlockRegionFromBlock($this->getNegativeBlockCoords());
        $this->assertInstanceOf(BlockRegion::class, $region);
        $this->assertEquals($this->getNegativeRegionCoords(), $region->getCoordinates());
        $this->closeFiles($blockFiles, $entitiesFiles);
    }

    /**
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    #[DataProvider('provideMultipleBlockFiles')]
    public function testGetEntitiesRegionFromBlock(array $blockFiles, array $entitiesFiles): void
    {
        $hawk = new Hawk($blockFiles, $entitiesFiles);
        if (in_array($this->dataName(), self::VERSIONS_WITHOUT_ENTITIES_FILES)) {
            $this->assertEmpty($entitiesFiles);
            $this->assertTrue($hawk->hasNoEntitiesRegions);
            return;
        }
        $region = $hawk->getEntitiesRegionFromBlock($this->getBlockCoords());
        $this->assertInstanceOf(EntitiesRegion::class, $region);
        $this->assertEquals($this->getRegionCoords(), $region->getCoordinates());
        $region = $hawk->getEntitiesRegionFromBlock($this->getNegativeBlockCoords());
        $this->assertInstanceOf(EntitiesRegion::class, $region);
        $this->assertEquals($this->getNegativeRegionCoords(), $region->getCoordinates());
        $this->closeFiles($blockFiles, $entitiesFiles);

    }

    /**
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    #[DataProvider('provideMultipleBlockFiles')]
    public function testGetBlockRegionFromChunk(array $blockFiles, array $entitiesFiles): void
    {
        $hawk = new Hawk($blockFiles, $entitiesFiles);
        foreach ([$this->getChunkCoords(), $this->getLastChunkInRegionCoords()] as $chunkCoordinates) {
            $region = $hawk->getBlockRegionFromChunk($chunkCoordinates);
            $this->assertInstanceOf(BlockRegion::class, $region);
            $this->assertEquals($this->getRegionCoords(), $region->getCoordinates());
        }
        foreach ([$this->getNegativeChunkCoords(), $this->getFirstChunkInNegativeRegionCoords()] as $chunkCoordinates) {
            $region = $hawk->getBlockRegionFromChunk($chunkCoordinates);
            $this->assertInstanceOf(BlockRegion::class, $region);
            $this->assertEquals($this->getNegativeRegionCoords(), $region->getCoordinates());
        }
        $this->closeFiles($blockFiles, $entitiesFiles);
    }

    /**
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    #[DataProvider('provideMultipleBlockFiles')]
    public function testGetEntitiesRegionFromChunk(array $blockFiles, array $entitiesFiles): void
    {
        $hawk = new Hawk($blockFiles, $entitiesFiles);
        if (in_array($this->dataName(), self::VERSIONS_WITHOUT_ENTITIES_FILES)) {
            $this->assertEmpty($entitiesFiles);
            $this->assertTrue($hawk->hasNoEntitiesRegions);
            return;
        }
        foreach ([$this->getChunkCoords(), $this->getLastChunkInRegionCoords()] as $chunkCoordinates) {
            $region = $hawk->getEntitiesRegionFromChunk($chunkCoordinates);
            $this->assertInstanceOf(EntitiesRegion::class, $region);
            $this->assertEquals($this->getRegionCoords(), $region->getCoordinates());
        }
        foreach ([$this->getNegativeChunkCoords(), $this->getFirstChunkInNegativeRegionCoords()] as $chunkCoordinates) {
            $region = $hawk->getEntitiesRegionFromChunk($chunkCoordinates);
            $this->assertInstanceOf(EntitiesRegion::class, $region);
            $this->assertEquals($this->getNegativeRegionCoords(), $region->getCoordinates());
        }
        $this->closeFiles($blockFiles, $entitiesFiles);
    }

    /**
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    #[DataProvider('provideMultipleBlockFiles')]
    public function testGetBlock(array $blockFiles, array $entitiesFiles): void
    {
        $hawk = new Hawk($blockFiles, $entitiesFiles);
        if (!$this->areBlocksSupportedInDataSet()) {
            $this->expectException(VersionNotSupportedException::class);
        }
        $block = $hawk->getBlock($this->getBlockCoords());
        $this->assertInstanceOf(DataBlock::class, $block);
        $this->assertEquals("minecraft:furnace", $block->getPaletteBlock()->getName());
        $this->closeFiles($blockFiles, $entitiesFiles);
    }

    /**
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    #[DataProvider('provideMultipleBlockFiles')]
    public function testReplaceBlock(array $blockFiles, array $entitiesFiles): void
    {
        $hawk = new Hawk($blockFiles, $entitiesFiles);
        if (!$this->areBlocksSupportedInDataSet()) {
            $this->expectException(VersionNotSupportedException::class);
        }
        $block = $hawk->getBlock($this->getBlockCoords());
        $this->assertEquals("minecraft:furnace", $block->getPaletteBlock()->getName());
        $hawk->replaceBlock($this->getBlockCoords(), "minecraft:wool");
        $this->assertEquals("minecraft:wool", $hawk->getBlock($this->getBlockCoords())->getPaletteBlock()->getName());
        $this->closeFiles($blockFiles, $entitiesFiles);
    }

    /**
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    #[DataProvider('provideMultipleBlockFiles')]
    public function testGetAllEntitiesFromChunk(array $blockFiles, array $entitiesFiles): void
    {
        $hawk = new Hawk($blockFiles, $entitiesFiles);
        $entities = $hawk->getAllEntitiesFromChunk($this->getBlockCoords());
        $this->assertInstanceOf(Entity::class, $entities[0]);
        $this->closeFiles($blockFiles, $entitiesFiles);

    }

    /**
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    #[DataProvider('provideMultipleBlockFiles')]
    public function testGetEntities(array $blockFiles, array $entitiesFiles): void
    {
        $hawk = new Hawk($blockFiles, $entitiesFiles);
        $entities = $hawk->getEntities("minecraft:chicken", $this->getEntityCoords());
        $this->assertInstanceOf(Entity::class, $entities[0]);
        $this->assertEquals("minecraft:chicken", $entities[0]->getName());
        $this->closeFiles($blockFiles, $entitiesFiles);
    }

    /**
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    #[DataProvider('provideMultipleBlockFiles')]
    public function testDeleteEntity(array $blockFiles, array $entitiesFiles): void
    {
        $hawk = new Hawk($blockFiles, $entitiesFiles);
        $entities = $hawk->getEntities("minecraft:chicken", $this->getEntityCoords());
        $hawk->deleteEntity($entities[0]);

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
    #[DataProvider('provideMultipleBlockFiles')]
    public function testGetAllBlockEntitiesFromChunk(array $blockFiles, array $entitiesFiles): void
    {
        $hawk = new Hawk($blockFiles, $entitiesFiles);
        $entities = $hawk->getAllBlockEntitiesFromChunk($this->getBlockCoords());
        $this->assertInstanceOf(BlockEntity::class, $entities[0]);
        $this->closeFiles($blockFiles, $entitiesFiles);

    }

    /**
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    #[DataProvider('provideMultipleBlockFiles')]
    public function testGetBlockEntities(array $blockFiles, array $entitiesFiles): void
    {
        $hawk = new Hawk($blockFiles, $entitiesFiles);
        $entities = $hawk->getBlockEntities("minecraft:furnace", $this->getBlockEntityCoords());
        $this->assertInstanceOf(BlockEntity::class, $entities[0]);
        $this->assertEquals("minecraft:furnace", $entities[0]->getName());
        $this->closeFiles($blockFiles, $entitiesFiles);
    }

    /**
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    #[DataProvider('provideMultipleBlockFiles')]
    public function testDeleteBlockEntity(array $blockFiles, array $entitiesFiles): void
    {
        $hawk = new Hawk($blockFiles, $entitiesFiles);
        $entities = $hawk->getBlockEntities("minecraft:furnace", $this->getBlockEntityCoords());
        $hawk->deleteBlockEntity($entities[0]);

        $entities = $hawk->getBlockEntities("minecraft:furnace", $this->getBlockEntityCoords());
        $this->assertEmpty($entities);
        $this->closeFiles($blockFiles, $entitiesFiles);
    }
}