<?php

namespace Aternos\Hawk\Tests\Unit;

use Aternos\Hawk\Chunk;
use Aternos\Hawk\File;
use Aternos\Hawk\Region;
use Exception;

class RegionTest extends HawkTestCase
{
    public function testGetBlockRegionCoordinatesFromBlockCoordinates()
    {
        $this->assertEquals($this->getRegionCoords(),Region::getRegionCoordinatesFromBlockCoordinates($this->getBlockCoords()));
        $this->assertEquals($this->getNegativeRegionCoords(),Region::getRegionCoordinatesFromBlockCoordinates($this->getNegativeBlockCoords()));
    }

    public function testGetBlockRegionCoordinatesFromChunkCoordinates()
    {
        $this->assertEquals($this->getChunkCoords(),Region::getRegionCoordinatesFromChunkCoordinates($this->getChunkCoords()));
        $this->assertEquals($this->getNegativeChunkCoords(),Region::getRegionCoordinatesFromChunkCoordinates($this->getNegativeChunkCoords()));
    }

    /**
     * @dataProvider provideSingleBlockFile
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     */
    public function testGetBlockRegionCoordinatesFromFile(array $blockFiles, array $entitiesFiles): void
    {
        $this->assertEquals($this->getRegionCoords(), Region::getRegionCoordinatesFromFile($blockFiles[0]));
    }

    /**
     * @dataProvider provideNegativeSingleBlockFile
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     */
    public function testGetNegativeBlockRegionCoordinatesFromFile(array $blockFiles, array $entitiesFiles): void
    {
        $this->assertEquals($this->getNegativeRegionCoords(), Region::getRegionCoordinatesFromFile($blockFiles[0]));
    }

    public function testGetRegionFileNameFromBlock(): void
    {
        $this->assertEquals("r.0.0.mca", Region::getRegionFileNameFromBlock($this->getBlockCoords()));
        $this->assertEquals("r.-1.-1.mca", Region::getRegionFileNameFromBlock($this->getNegativeBlockCoords()));
    }

    /**
     * @dataProvider provideSingleBlockFile
     * @param File[] $blockFiles
     * @param array $entitiesFiles
     * @return void
     */
    public function testConstructor(array $blockFiles, array $entitiesFiles): void
    {
        $mock = $this->getMockForAbstractClass(Region::class,$blockFiles);
        $this->assertEquals($blockFiles[0]->getFileName(),$mock->getFileName());
        $this->assertEquals($blockFiles[0],$mock->getFile());
        $this->assertEquals(Region::getRegionCoordinatesFromFile($blockFiles[0]),$mock->getCoordinates());
    }

    /**
     * @dataProvider provideSingleBlockFile
     * @param File[] $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    public function testGetChunkFromBlock(array $blockFiles, array $entitiesFiles): void
    {
        $mock = $this->getMockForAbstractClass(Region::class,$blockFiles);
        $chunk = $mock->getChunkFromBlock($this->getBlockCoords());
        $this->assertInstanceOf(Chunk::class, $chunk);
    }

    /**
     * @dataProvider provideSingleBlockFile
     * @param File[] $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    public function testGetChunkFromChunk(array $blockFiles, array $entitiesFiles): void
    {
        $mock = $this->getMockForAbstractClass(Region::class,$blockFiles);
        $chunk = $mock->getChunkFromChunk($this->getChunkCoords());
        $this->assertInstanceOf(Chunk::class, $chunk);
    }

    /**
     * @dataProvider provideSingleBlockFile
     * @param File[] $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    public function testGetChunk(array $blockFiles, array $entitiesFiles): void
    {
        $mock = $this->getMockBuilder(Region::class)
            ->setConstructorArgs($blockFiles)
            ->onlyMethods(['readChunk'])
            ->getMockForAbstractClass();
        $mock->expects($this->once())
            ->method('readChunk');
        $chunk = $mock->getChunk($this->getChunkCoords());
        $this->assertInstanceOf(Chunk::class, $chunk);
    }


}