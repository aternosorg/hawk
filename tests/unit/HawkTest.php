<?php

namespace Aternos\Hawk\Tests\Unit;

use Aternos\Hawk\BlockRegion;
use Aternos\Hawk\DataBlock;
use Aternos\Hawk\EntitiesRegion;
use Aternos\Hawk\Entity;
use Aternos\Hawk\Hawk;
use Aternos\Hawk\Property;
use Exception;

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
     * @dataProvider provideSingleBlockFile
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
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
    }

    /**
     * @dataProvider provideSingleBlockFile
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    public function testLoadBlockRegionsEmptyBlockRegions(array $blockFiles, array $entitiesFiles): void
    {
        $hawk = new Hawk($blockFiles, $entitiesFiles);
        $region = new BlockRegion($blockFiles[0]);
        $regions = $hawk->getBlockRegions();
        $this->assertEquals($region, $regions[0]);
        $this->assertCount(1, $regions);
    }

    /**
     * @dataProvider provideMultipleBlockFiles
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    public function testLoadBlockRegionsMultipleFiles(array $blockFiles, array $entitiesFiles): void
    {
        $hawk = new Hawk($blockFiles, $entitiesFiles);
        foreach ($blockFiles as $file) {
            $regions[] = new BlockRegion($file);
        }
        $hawkRegions = $hawk->getBlockRegions();
        $this->assertEquals($regions, $hawkRegions);
        $this->assertCount(2, $hawkRegions);
    }

    /**
     * @dataProvider provideMultipleEqualBlockFiles
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    public function testLoadBlockRegionsMultipleEqualFiles(array $blockFiles, array $entitiesFiles): void
    {
        $hawk = new Hawk($blockFiles, $entitiesFiles);
        foreach ($blockFiles as $file) {
            $regions[] = new BlockRegion($file);
        }
        $hawkRegions = $hawk->getBlockRegions();
        $this->assertNotEquals($regions, $hawkRegions);
        $this->assertCount(1, $hawkRegions);
    }

    /**
     * @dataProvider provideSingleBlockFile
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
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
    }

    /**
     * @dataProvider provideMultipleBlockFiles
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
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
    }


    /**
     * @dataProvider provideMultipleEqualBlockFiles
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
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
    }

    /**
     * @dataProvider provideMultipleBlockFiles
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    public function testGetBlockRegionFromBlock(array $blockFiles, array $entitiesFiles): void
    {
        $hawk = new Hawk($blockFiles, $entitiesFiles);
        $region = $hawk->getBlockRegionFromBlock($this->getBlockCoords());
        $this->assertInstanceOf(BlockRegion::class, $region);
        $this->assertEquals($this->getRegionCoords(), $region->getCoordinates());
        $region = $hawk->getBlockRegionFromBlock($this->getNegativeBlockCoords());
        $this->assertInstanceOf(BlockRegion::class, $region);
        $this->assertEquals($this->getNegativeRegionCoords(), $region->getCoordinates());
    }

    /**
     * @dataProvider provideMultipleBlockFiles
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
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
    }

    /**
     * @dataProvider provideMultipleBlockFiles
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    public function testGetBlockRegionFromChunk(array $blockFiles, array $entitiesFiles): void
    {
        $hawk = new Hawk($blockFiles, $entitiesFiles);
        $region = $hawk->getBlockRegionFromChunk($this->getRegionCoords());
        $this->assertInstanceOf(BlockRegion::class, $region);
        $this->assertEquals($this->getRegionCoords(), $region->getCoordinates());
        $region = $hawk->getBlockRegionFromChunk($this->getNegativeRegionCoords());
        $this->assertInstanceOf(BlockRegion::class, $region);
        $this->assertEquals($this->getNegativeRegionCoords(), $region->getCoordinates());
    }

    /**
     * @dataProvider provideMultipleBlockFiles
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    public function testGetEntitiesRegionFromChunk(array $blockFiles, array $entitiesFiles): void
    {
        $hawk = new Hawk($blockFiles, $entitiesFiles);
        if (in_array($this->dataName(), self::VERSIONS_WITHOUT_ENTITIES_FILES)) {
            $this->assertEmpty($entitiesFiles);
            $this->assertTrue($hawk->hasNoEntitiesRegions);
            return;
        }
        $region = $hawk->getEntitiesRegionFromChunk($this->getRegionCoords());
        $this->assertInstanceOf(EntitiesRegion::class, $region);
        $this->assertEquals($this->getRegionCoords(), $region->getCoordinates());
        $region = $hawk->getEntitiesRegionFromChunk($this->getNegativeRegionCoords());
        $this->assertInstanceOf(EntitiesRegion::class, $region);
        $this->assertEquals($this->getNegativeRegionCoords(), $region->getCoordinates());
    }

    /**
     * @dataProvider provideMultipleBlockFiles
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    public function testGetBlock(array $blockFiles, array $entitiesFiles): void
    {
        $hawk = new Hawk($blockFiles, $entitiesFiles);
        $block = $hawk->getBlock($this->getBlockCoords());
        $this->assertInstanceOf(DataBlock::class, $block);
        $this->assertEquals("minecraft:furnace", $block->getPaletteBlock()->getName());
    }

    /**
     * @dataProvider provideMultipleBlockFiles
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    public function testReplaceBlock(array $blockFiles, array $entitiesFiles): void
    {
        $hawk = new Hawk($blockFiles, $entitiesFiles);
        $block = $hawk->getBlock($this->getBlockCoords());
        $hawk->replaceBlock($this->getBlockCoords(), "minecraft:wool");
        $this->assertInstanceOf(DataBlock::class, $block);
        $this->assertEquals("minecraft:wool", $hawk->getBlock($this->getBlockCoords())->getPaletteBlock()->getName());
    }

    /**
     * @dataProvider provideMultipleBlockFiles
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    public function testGetAllEntitiesFromChunk(array $blockFiles, array $entitiesFiles): void
    {
        $hawk = new Hawk($blockFiles, $entitiesFiles);
        $entities = $hawk->getAllEntitiesFromChunk($this->getBlockCoords());
        $this->assertInstanceOf(Entity::class, $entities[0]);

    }

    /**
     * @dataProvider provideMultipleBlockFiles
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    public function testGetEntities(array $blockFiles, array $entitiesFiles): void
    {
        $hawk = new Hawk($blockFiles, $entitiesFiles);
        $entities = $hawk->getEntities("minecraft:chicken", $this->getEntityCoords());
        $this->assertInstanceOf(Entity::class, $entities[0]);
        $this->assertEquals("minecraft:chicken", $entities[0]->getName());
    }

    /**
     * @dataProvider provideMultipleBlockFiles
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    public function testDeleteEntity(array $blockFiles, array $entitiesFiles): void
    {
        $hawk = new Hawk($blockFiles, $entitiesFiles);
        $entities = $hawk->getEntities("minecraft:chicken", $this->getEntityCoords());
        $count = count($entities);
        $hawk->deleteEntity($entities[0]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Entity not found.");
        $entities = $hawk->getEntities("minecraft:chicken", $this->getEntityCoords());
    }
}