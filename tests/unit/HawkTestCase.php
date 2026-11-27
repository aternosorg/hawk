<?php

declare(strict_types=1);

namespace Aternos\Hawk\Tests\Unit;

use Aternos\Hawk\AbstractFile;
use Aternos\Hawk\BlockChunk;
use Aternos\Hawk\BlockRegion;
use Aternos\Hawk\File;
use Aternos\Hawk\McCoordinates2D;
use Aternos\Hawk\McCoordinates3D;
use Aternos\Hawk\McCoordinatesFloat;
use Aternos\Nbt\Tag\CompoundTag;
use DirectoryIterator;
use Exception;
use PHPUnit\Framework\TestCase;

class HawkTestCase extends TestCase
{
    protected function closeFiles(array $blockFiles, array $entitiesFiles): void
    {
        foreach ($blockFiles as $blockFile) {
            $blockFile->close();
        }
        foreach ($entitiesFiles as $entitiesFile) {
            $entitiesFile->close();
        }
    }

    public const VERSIONS_WITHOUT_ENTITIES_FILES = [
        "1.12",
        "1.12.1",
        "1.12.2",
        "1.13",
        "1.13.1",
        "1.13.2",
        "1.14",
        "1.14.1",
        "1.14.2",
        "1.14.3",
        "1.14.4",
        "1.15",
        "1.15.1",
        "1.15.2",
        "1.16",
        "1.16.1",
        "1.16.2",
        "1.16.3",
        "1.16.4",
        "1.16.5",
    ];

    public function getRegionCoords(): McCoordinates2D
    {
        return new McCoordinates2D(0, 0);
    }

    public function getNegativeRegionCoords(): McCoordinates2D
    {
        return new McCoordinates2D(-1, -1);
    }

    public function getChunkCoords(): McCoordinates2D
    {
        return new McCoordinates2D(0, 0);
    }

    public function getNegativeChunkCoords(): McCoordinates2D
    {
        return new McCoordinates2D(-1, -1);
    }

    public function getBlockCoords(): McCoordinates3D
    {
        return new McCoordinates3D(1, 1, 1);
    }

    public function getNegativeBlockCoords(): McCoordinates3D
    {
        return new McCoordinates3D(-1, 0, -1);
    }

    public function provide3DCoordinates(): array
    {
        return [
            new McCoordinates3D(1, 1, 1,),
            new McCoordinates3D(-1, 1, -1,),
        ];
    }

    public function getEntityCoords(): McCoordinatesFloat
    {
        return new McCoordinatesFloat(1.5, 64, 1.5);
    }

    public function getBlockEntityCoords(): McCoordinatesFloat
    {
        return new McCoordinatesFloat(1, 1, 1);
    }

    public function getNegativeEntityCoords(): McCoordinatesFloat
    {
        return new McCoordinatesFloat(-1.5, 64, -1.5);
    }

    /**
     * Data sets of the version providers are named after the version directory, e.g. "1.15.2" or "latest".
     * Blocks are supported since 1.13, the first version with block state palettes.
     *
     * @return bool
     */
    protected function areBlocksSupportedInDataSet(): bool
    {
        $version = (string)$this->dataName();
        return $version === "latest" || version_compare($version, "1.13", ">=");
    }

    public function getBlockRegion(AbstractFile $blockfile): BlockRegion
    {
        return new BlockRegion($blockfile);
    }

    public function getBlockChunk(AbstractFile $blockfile): BlockChunk
    {
        $chunk = $this->getBlockRegion($blockfile)->getChunkFromBlock($this->getBlockCoords());
        if ($chunk instanceof BlockChunk) {
            return $chunk;
        }
        throw new Exception("Not a BlockChunk");
    }

    public function getBlockChunkTag(AbstractFile $blockfile): CompoundTag
    {
        return $this->getBlockChunk($blockfile)->getTag();
    }

    /**
     * @return array
     * @throws Exception
     */
    public static function provideSingleBlockFile(): array
    {
        $blockFiles = [];
        $versions = new DirectoryIterator(__DIR__ . "/../../examples/resources/versions");
        foreach ($versions as $version) {
            if (!$version->isDot()) {
                $dirName = $version->getFilename();
                $regionFile = new File(__DIR__ . "/../../examples/resources/versions/" . $dirName . "/region/r.0.0.mca");
                $entityFile = null;
                if (version_compare($dirName, "1.17", ">=") || $dirName === "latest") {
                    $entityFile = new File(__DIR__ . "/../../examples/resources/versions/" . $dirName . "/entities/r.0.0.mca");
                }
                $blockFiles[$dirName] = [
                    [$regionFile],
                    $entityFile !== null ? [$entityFile] : []
                ];
            }
        }
        return $blockFiles;
    }

    /**
     * @return array
     * @throws Exception
     */
    public static function provideNegativeSingleBlockFile(): array
    {
        $blockFiles = [];
        $versions = new DirectoryIterator(__DIR__ . "/../../examples/resources/versions");
        foreach ($versions as $version) {
            if (!$version->isDot()) {
                $dirName = $version->getFilename();
                $regionFile = new File(__DIR__ . "/../../examples/resources/versions/" . $dirName . "/region/r.-1.-1.mca");
                $entityFile = null;
                if (version_compare($dirName, "1.17", ">=") || $dirName === "latest") {
                    $entityFile = new File(__DIR__ . "/../../examples/resources/versions/" . $dirName . "/entities/r.-1.-1.mca");
                }
                $blockFiles[$dirName] = [
                    [$regionFile],
                    $entityFile !== null ? [$entityFile] : []
                ];
            }
        }
        return $blockFiles;
    }

    public static function provideMultipleBlockFiles(): array
    {
        $blockFiles = [];
        $versions = new DirectoryIterator(__DIR__ . "/../../examples/resources/versions");
        foreach ($versions as $version) {
            if (!$version->isDot()) {
                $dirName = $version->getFilename();
                $blockFiles[$dirName] = [];
                $regionFile = new File(__DIR__ . "/../../examples/resources/versions/" . $dirName . "/region/r.0.0.mca");
                $regionFileTwo = new File(__DIR__ . "/../../examples/resources/versions/" . $dirName . "/region/r.-1.-1.mca");
                $blockFiles[$dirName][] = [$regionFile, $regionFileTwo];
                $entityFile = null;
                $entityFileTwo = null;
                if (version_compare($dirName, "1.17", ">=") || $dirName === "latest") {
                    $entityFile = new File(__DIR__ . "/../../examples/resources/versions/" . $dirName . "/entities/r.0.0.mca");
                    $entityFileTwo = new File(__DIR__ . "/../../examples/resources/versions/" . $dirName . "/entities/r.-1.-1.mca");
                    $blockFiles[$dirName][] = [$entityFile, $entityFileTwo];
                } else {
                    $blockFiles[$dirName][] = [];
                }
            }
        }
        return $blockFiles;
    }

    public static function provideMultipleEqualBlockFiles(): array
    {
        $blockFiles = [];
        $versions = new DirectoryIterator(__DIR__ . "/../../examples/resources/versions");
        foreach ($versions as $version) {
            if (!$version->isDot()) {
                $dirName = $version->getFilename();
                $blockFiles[$dirName] = [];
                $regionFile = new File(__DIR__ . "/../../examples/resources/versions/" . $dirName . "/region/r.0.0.mca");
                $regionFileTwo = new File(__DIR__ . "/../../examples/resources/versions/" . $dirName . "/region/r.0.0.mca");
                $blockFiles[$dirName][] = [$regionFile, $regionFileTwo];
                $entityFile = null;
                $entityFileTwo = null;
                if (version_compare($dirName, "1.17", ">=") || $dirName === "latest") {
                    $entityFile = new File(__DIR__ . "/../../examples/resources/versions/" . $dirName . "/entities/r.0.0.mca");
                    $entityFileTwo = new File(__DIR__ . "/../../examples/resources/versions/" . $dirName . "/entities/r.0.0.mca");
                    $blockFiles[$dirName][] = [$entityFile, $entityFileTwo];
                } else {
                    $blockFiles[$dirName][] = [];
                }
            }
        }
        return $blockFiles;
    }

    public function provideBlockRegions(): array
    {
        $regions = [];
        $blockFiles = $this->provideSingleBlockFile();

        foreach ($blockFiles as $blockFile) {
            $regions[] = new BlockRegion($blockFile[0][0]);
        }
        return $regions;
    }

    public function provideBlockChunks(): array
    {
        $chunks = [];
        $blockRegions = $this->provideBlockRegions();
        foreach ($blockRegions as $blockRegion) {
            $chunks[] = $blockRegion->getChunkFromBlock($this->getBlockCoords());
        }
        return $chunks;
    }

    public function provideChunkTags(): array
    {
        $tags = [];
        $blockChunks = $this->provideBlockChunks();
        foreach ($blockChunks as $blockChunk) {
            $tags[] = $blockChunk->getTag();
        }
        return $tags;
    }

}