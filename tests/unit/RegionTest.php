<?php

declare(strict_types=1);

namespace Aternos\Hawk\Tests\Unit;

use Aternos\Hawk\Chunk;
use Aternos\Hawk\File;
use Aternos\Hawk\Region;
use Exception;
use PHPUnit\Framework\Attributes\DataProvider;

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
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     */
    #[DataProvider('provideSingleBlockFile')]
    public function testGetBlockRegionCoordinatesFromFile(array $blockFiles, array $entitiesFiles): void
    {
        $this->assertEquals($this->getRegionCoords(), Region::getRegionCoordinatesFromFile($blockFiles[0]));
        $this->closeFiles($blockFiles, $entitiesFiles);
    }

    /**
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     */
    #[DataProvider('provideNegativeSingleBlockFile')]
    public function testGetNegativeBlockRegionCoordinatesFromFile(array $blockFiles, array $entitiesFiles): void
    {
        $this->assertEquals($this->getNegativeRegionCoords(), Region::getRegionCoordinatesFromFile($blockFiles[0]));
        $this->closeFiles($blockFiles, $entitiesFiles);
    }

    public function testGetRegionFileNameFromBlock(): void
    {
        $this->assertEquals("r.0.0.mca", Region::getRegionFileNameFromBlock($this->getBlockCoords()));
        $this->assertEquals("r.-1.-1.mca", Region::getRegionFileNameFromBlock($this->getNegativeBlockCoords()));
    }

    /**
     * @param File[] $blockFiles
     * @param array $entitiesFiles
     * @return void
     */
    #[DataProvider('provideSingleBlockFile')]
    public function testConstructor(array $blockFiles, array $entitiesFiles): void
    {
        $region = $this->getStubBuilder(Region::class)
            ->setConstructorArgs($blockFiles)
            ->onlyMethods(['addNewChunk'])
            ->getStub();
        $this->assertEquals($blockFiles[0]->getFileName(),$region->getFileName());
        $this->assertEquals($blockFiles[0],$region->getFile());
        $this->assertEquals(Region::getRegionCoordinatesFromFile($blockFiles[0]),$region->getCoordinates());
        $this->closeFiles($blockFiles, $entitiesFiles);
    }

    /**
     * @param File[] $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    #[DataProvider('provideSingleBlockFile')]
    public function testGetChunkFromBlock(array $blockFiles, array $entitiesFiles): void
    {
        $region = $this->getStubBuilder(Region::class)
            ->setConstructorArgs($blockFiles)
            ->onlyMethods(['addNewChunk'])
            ->getStub();
        $chunk = $region->getChunkFromBlock($this->getBlockCoords());
        $this->assertInstanceOf(Chunk::class, $chunk);
        $this->closeFiles($blockFiles, $entitiesFiles);
    }

    /**
     * @param File[] $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    #[DataProvider('provideSingleBlockFile')]
    public function testGetChunkFromChunk(array $blockFiles, array $entitiesFiles): void
    {
        $region = $this->getStubBuilder(Region::class)
            ->setConstructorArgs($blockFiles)
            ->onlyMethods(['addNewChunk'])
            ->getStub();
        $chunk = $region->getChunkFromChunk($this->getChunkCoords());
        $this->assertInstanceOf(Chunk::class, $chunk);
        $this->closeFiles($blockFiles, $entitiesFiles);
    }

    /**
     * @param File[] $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    #[DataProvider('provideSingleBlockFile')]
    public function testGetChunk(array $blockFiles, array $entitiesFiles): void
    {
        $mock = $this->getMockBuilder(Region::class)
            ->setConstructorArgs($blockFiles)
            ->onlyMethods(['readChunk', 'addNewChunk'])
            ->getMock();
        $mock->expects($this->once())
            ->method('readChunk');
        $chunk = $mock->getChunk($this->getChunkCoords());
        $this->assertInstanceOf(Chunk::class, $chunk);
        $this->closeFiles($blockFiles, $entitiesFiles);
    }
}