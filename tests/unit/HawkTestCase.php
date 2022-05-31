<?php

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
    public const VERSIONS_WITHOUT_ENTITIES_FILES = [
        "1.16",
        "1.16.1",
        "1.16.2",
        "1.16.3",
        "1.16.4",
        "1.16.5",
    ];

    public function getRegionCoords(): McCoordinates2D
    {
        return new McCoordinates2D(0,0);
    }

    public function getNegativeRegionCoords(): McCoordinates2D
    {
        return new McCoordinates2D(-1,-1);
    }

    public function getChunkCoords(): McCoordinates2D
    {
        return new McCoordinates2D(0,0);
    }

    public function getNegativeChunkCoords(): McCoordinates2D
    {
        return new McCoordinates2D(-1,-1);
    }

    public function getBlockCoords(): McCoordinates3D
    {
        return new McCoordinates3D(1, 1, 1);
    }

    public function getNegativeBlockCoords(): McCoordinates3D
    {
        return new McCoordinates3D(-1,0,-1);
    }

    public function provide3DCoordinates(): array
    {
        return [
            new McCoordinates3D(1,1,1,),
            new McCoordinates3D(-1,1,-1,),
        ];
    }

    public function getEntityCoords(): McCoordinatesFloat
    {
        return new McCoordinatesFloat(1.5, 64, 1.5);
    }

    public function getNegativeEntityCoords(): McCoordinatesFloat
    {
        return new McCoordinatesFloat(-1.5, 64, -1.5);
    }

    public function getBlockRegion(AbstractFile $blockfile): BlockRegion
    {
        return new BlockRegion($blockfile);
    }

    public function getBlockChunk(AbstractFile $blockfile): BlockChunk
    {
        $chunk = $this->getBlockRegion($blockfile)->getChunkFromBlock($this->getBlockCoords());
        if($chunk instanceof BlockChunk){
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
    public function provideSingleBlockFile(): array
    {
        $blockFiles = [];
        $versions = new DirectoryIterator(__DIR__ . "/../../examples/resources/versions");
        foreach ($versions as $version) {
            if (!$version->isDot()) {
                $dirName = $version->getFilename();
                $versionName = explode("(", $dirName)[0];
                $major = explode(".", $versionName)[1];
                if ($major > 16) {
                    $blockFiles[$versionName] = [
                        [
                            new File(__DIR__ . "/../../examples/resources/versions/" . $dirName . "/region/r.0.0.mca")
                        ],
                        [
                            new File(__DIR__ . "/../../examples/resources/versions/" . $dirName . "/entities/r.0.0.mca")
                        ],
                    ];
                } else {
                    $blockFiles[$versionName] = [
                        [
                            new File(__DIR__ . "/../../examples/resources/versions/" . $dirName . "/region/r.0.0.mca")
                        ],
                        []
                    ];
                }
            }
        }
        return $blockFiles;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function provideNegativeSingleBlockFile(): array
    {
        $blockFiles = [];
        $versions = new DirectoryIterator(__DIR__ . "/../../examples/resources/versions");
        foreach ($versions as $version) {
            if (!$version->isDot()) {
                $dirName = $version->getFilename();
                $versionName = explode("(", $dirName)[0];
                $major = explode(".", $versionName)[1];
                if ($major > 16) {
                    $blockFiles[$versionName] = [
                        [
                            new File(__DIR__ . "/../../examples/resources/versions/" . $dirName . "/region/r.-1.-1.mca")
                        ],
                        [
                            new File(__DIR__ . "/../../examples/resources/versions/" . $dirName . "/entities/r.-1.-1.mca")
                        ],
                    ];
                } else {
                    $blockFiles[$versionName] = [
                        [
                            new File(__DIR__ . "/../../examples/resources/versions/" . $dirName . "/region/r.-1.-1.mca")
                        ],
                        []
                    ];
                }
            }
        }
        return $blockFiles;
    }

    public function provideMultipleBlockFiles(): array
    {
        $blockFiles = [];
        $versions = new DirectoryIterator(__DIR__ . "/../../examples/resources/versions");
        foreach ($versions as $version) {
            if (!$version->isDot()) {
                $dirName = $version->getFilename();
                $versionName = explode("(", $dirName)[0];
                $major = explode(".", $versionName)[1];
                if ($major > 16) {
                    $blockFiles[$versionName] = [
                        [
                            new File(__DIR__ . "/../../examples/resources/versions/" . $dirName . "/region/r.0.0.mca"),
                            new File(__DIR__ . "/../../examples/resources/versions/" . $dirName . "/region/r.-1.-1.mca"),
                        ],
                        [
                            new File(__DIR__ . "/../../examples/resources/versions/" . $dirName . "/entities/r.0.0.mca"),
                            new File(__DIR__ . "/../../examples/resources/versions/" . $dirName . "/entities/r.-1.-1.mca"),
                        ],
                    ];
                }else{
                    $blockFiles[$versionName] = [
                        [
                            new File(__DIR__ . "/../../examples/resources/versions/" . $dirName . "/region/r.0.0.mca"),
                            new File(__DIR__ . "/../../examples/resources/versions/" . $dirName . "/region/r.-1.-1.mca"),
                        ],
                        []
                    ];
                }
            }
        }
        return $blockFiles;
    }

    public function provideMultipleEqualBlockFiles(): array
    {
        $blockFiles = [];
        $versions = new DirectoryIterator(__DIR__ . "/../../examples/resources/versions");
        foreach ($versions as $version) {
            if (!$version->isDot()) {
                $dirName = $version->getFilename();
                $versionName = explode("(", $dirName)[0];
                $blockFiles[$versionName] = [
                    [
                        new File(__DIR__ . "/../../examples/resources/versions/" . $dirName . "/region/r.0.0.mca"),
                        new File(__DIR__ . "/../../examples/resources/versions/" . $dirName . "/region/r.0.0.mca"),
                    ],
                    []
                ];
                $major = explode(".", $versionName)[1];
                if ($major > 16) {
                    $blockFiles[$versionName] = [
                        [
                            new File(__DIR__ . "/../../examples/resources/versions/" . $dirName . "/region/r.0.0.mca"),
                            new File(__DIR__ . "/../../examples/resources/versions/" . $dirName . "/region/r.0.0.mca")
                        ],
                        [
                            new File(__DIR__ . "/../../examples/resources/versions/" . $dirName . "/entities/r.0.0.mca"),
                            new File(__DIR__ . "/../../examples/resources/versions/" . $dirName . "/entities/r.0.0.mca")
                        ],
                    ];
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