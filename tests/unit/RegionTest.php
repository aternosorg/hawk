<?php

declare(strict_types=1);

namespace Aternos\Hawk\Tests\Unit;

use Aternos\Hawk\BlockRegion;
use Aternos\Hawk\Chunk;
use Aternos\Hawk\Exceptions\FileException;
use Aternos\Hawk\Exceptions\InvalidCompressionSchemeException;
use Aternos\Hawk\File;
use Aternos\Hawk\McCoordinates2D;
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
        $this->assertEquals($this->getRegionCoords(), Region::getRegionCoordinatesFromChunkCoordinates($this->getChunkCoords()));
        $this->assertEquals($this->getRegionCoords(), Region::getRegionCoordinatesFromChunkCoordinates($this->getLastChunkInRegionCoords()));
        $this->assertEquals($this->getNegativeRegionCoords(), Region::getRegionCoordinatesFromChunkCoordinates($this->getNegativeChunkCoords()));
        $this->assertEquals($this->getNegativeRegionCoords(), Region::getRegionCoordinatesFromChunkCoordinates($this->getFirstChunkInNegativeRegionCoords()));
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

    /**
     * @throws Exception
     */
    public function testReadChunkWithUnknownCompressionSchemeNamesChunkAndFile(): void
    {
        $chunkSectorOffset = 2;
        $chunkSectorCount = 1;
        $chunkLocation = pack("N", ($chunkSectorOffset << 8) | $chunkSectorCount);
        $header = str_pad($chunkLocation, 8192, "\0");
        $unknownCompressionScheme = 99;
        $chunkData = pack("N", 1) . pack("C", $unknownCompressionScheme);

        $file = new File();
        $file->setFileName("r.0.0.mca");
        $file->setContent($header . $chunkData);
        $region = new BlockRegion($file);

        try {
            $region->getChunkFromChunk(new McCoordinates2D(0, 0));
            $this->fail("Expected " . InvalidCompressionSchemeException::class);
        } catch (InvalidCompressionSchemeException $exception) {
            $this->assertSame($unknownCompressionScheme, $exception->getCompressionScheme());
            $this->assertSame("r.0.0.mca", $exception->getRegionFileName());
            $this->assertEquals(new McCoordinates2D(0, 0), $exception->getChunkCoordinates());
            $this->assertSame("Unknown compression scheme 99 for chunk 0, 0 in region file \"r.0.0.mca\".", $exception->getMessage());
        } finally {
            $file->close();
        }
    }

    public function testOpeningMissingFileNamesThePath(): void
    {
        $path = __DIR__ . "/does-not-exist/r.0.0.mca";
        try {
            @new File($path);
            $this->fail("Expected " . FileException::class);
        } catch (FileException $exception) {
            $this->assertSame($path, $exception->getPath());
            $this->assertSame("Could not open file \"" . $path . "\".", $exception->getMessage());
        }
    }
}
