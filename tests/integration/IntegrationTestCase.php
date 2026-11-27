<?php

namespace Aternos\Hawk\Tests\Integration;

use Aternos\Hawk\BlockRegion;
use Aternos\Hawk\File;
use Aternos\Hawk\McCoordinates3D;
use Aternos\Hawk\McCoordinatesFloat;
use Aternos\Hawk\Tests\Unit\HawkTestCase;
use Aternos\Nbt\IO\Writer\StringWriter;
use Aternos\Nbt\NbtFormat;
use DirectoryIterator;
use Exception;

/**
 * Integration tests are split into several classes, so they can run in parallel
 */
abstract class IntegrationTestCase extends HawkTestCase
{
    public function getBlockCoords(): McCoordinates3D
    {
        return new McCoordinates3D(1, 1, 1);
    }

    public function getNegativeBlockCoords(): McCoordinates3D
    {
        return new McCoordinates3D(-1, 1, -1);
    }

    public function getEntityCoords(): McCoordinatesFloat
    {
        return new McCoordinatesFloat(1.4, 64, 1.4);
    }

    public function getBlockEntityCoords(): McCoordinatesFloat
    {
        return new McCoordinatesFloat(1, 1, 1);
    }

    public function getNegativeEntityCoords(): McCoordinatesFloat
    {
        return new McCoordinatesFloat(-0.4, 64, -0.4);
    }

    public function getNegativeBlockEntityCoords(): McCoordinatesFloat
    {
        return new McCoordinatesFloat(-1, 1, -1);
    }

    public function getExactEntityCoords(): McCoordinatesFloat
    {
        return new McCoordinatesFloat(1.5, 64, 1.5);
    }

    public function getExactNegativeEntityCoords(): McCoordinatesFloat
    {
        return new McCoordinatesFloat(-0.5, 64, -0.5);
    }

    /**
     * Directory that this test class copies the example worlds into.
     * Every test class needs a directory of its own, so test classes running in parallel processes
     * do not change each other's files.
     *
     * @return string e.g. "tests/files/blocks/versions"
     */
    abstract protected static function getTestFilesDirectory(): string;

    protected static function copyTestFiles(?string $src = null, ?string $dst = null): void
    {
        if ($src === null) {
            $src = __DIR__ . "/../../examples/resources/versions";
        }
        if ($dst === null) {
            $dst = static::getTestFilesDirectory();
        }

        $dir = opendir($src);
        if (!file_exists($dst)) {
            @mkdir($dst, recursive: true);
        }
        while (($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    static::copyTestFiles($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    /**
     * @return array
     * @throws Exception
     */
    public static function provideSingleBlockFile(): array
    {
        static::copyTestFiles();
        $blockFiles = [];
        $versions = new DirectoryIterator(static::getTestFilesDirectory());
        foreach ($versions as $version) {
            if (!$version->isDot()) {
                $dirName = $version->getFilename();
                if (version_compare($dirName, "1.17", ">=") || $dirName === "latest") {
                    $blockFiles[$dirName] = [
                        [
                            new File(static::getTestFilesDirectory() . "/" . $dirName . "/region/r.0.0.mca")
                        ],
                        [
                            new File(static::getTestFilesDirectory() . "/" . $dirName . "/entities/r.0.0.mca")
                        ],
                    ];
                } else {
                    $blockFiles[$dirName] = [
                        [
                            new File(static::getTestFilesDirectory() . "/" . $dirName . "/region/r.0.0.mca")
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
    public static function provideNegativeSingleBlockFile(): array
    {
        static::copyTestFiles();
        $blockFiles = [];
        $versions = new DirectoryIterator(static::getTestFilesDirectory());
        foreach ($versions as $version) {
            if (!$version->isDot()) {
                $dirName = $version->getFilename();
                if (version_compare($dirName, "1.17", ">=") || $dirName === "latest") {
                    $blockFiles[$dirName] = [
                        [
                            new File(static::getTestFilesDirectory() . "/" . $dirName . "/region/r.-1.-1.mca")
                        ],
                        [
                            new File(static::getTestFilesDirectory() . "/" . $dirName . "/entities/r.-1.-1.mca")
                        ],
                    ];
                } else {
                    $blockFiles[$dirName] = [
                        [
                            new File(static::getTestFilesDirectory() . "/" . $dirName . "/region/r.-1.-1.mca")
                        ],
                        []
                    ];
                }
            }
        }
        return $blockFiles;
    }

    /**
     * Reads the raw section tags of the chunk at the block coordinates, as hex encoded NBT per key and section Y.
     * Uses the binary writer instead of toSNBT(), which older versions of aternos/nbt (for PHP 8.0) do not have.
     *
     * @param File $blockFile
     * @return array<int, array<string, string>>
     * @throws Exception
     */
    protected function readRawSections(File $blockFile): array
    {
        $chunkTag = (new BlockRegion($blockFile))->getChunkFromBlock($this->getBlockCoords())->getTag();
        // Before 1.18, the chunk data is wrapped in a "Level" tag and the list is called "Sections"
        $levelTag = $chunkTag->getCompound("Level") ?? $chunkTag;
        $sectionsTag = $levelTag->getList("Sections") ?? $levelTag->getList("sections");

        $sections = [];
        foreach ($sectionsTag as $sectionTag) {
            $entries = [];
            foreach ($sectionTag as $key => $value) {
                $writer = (new StringWriter())->setFormat(NbtFormat::JAVA_EDITION);
                // write() only accepts a CompoundTag as root, writeContent() works for every tag
                $value->writeContent($writer);
                $entries[$key] = bin2hex($writer->getStringData());
            }
            ksort($entries);
            $y = $sectionTag->getByte("Y")->getValue();
            $this->assertArrayNotHasKey($y, $sections, "Section " . $y . " exists twice");
            $sections[$y] = $entries;
        }
        ksort($sections);
        return $sections;
    }
}
