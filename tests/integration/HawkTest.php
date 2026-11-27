<?php

declare(strict_types=1);

namespace Aternos\Hawk\Tests\Integration;

use Aternos\Hawk\BlockEntity;
use Aternos\Hawk\BlockRegion;
use Aternos\Hawk\Entity;
use Aternos\Hawk\Exceptions\VersionNotSupportedException;
use Aternos\Hawk\File;
use Aternos\Hawk\Hawk;
use Aternos\Hawk\McCoordinates3D;
use Aternos\Hawk\McCoordinatesFloat;
use Aternos\Hawk\Tests\Unit\HawkTestCase;
use Aternos\Nbt\IO\Writer\StringWriter;
use Aternos\Nbt\NbtFormat;
use DirectoryIterator;
use Exception;
use PHPUnit\Framework\Attributes\DataProvider;

class HawkTest extends HawkTestCase
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

    private static function copyTestFiles(?string $src = null, ?string $dst = null): void
    {
        if ($src === null) {
            $src = __DIR__ . "/../../examples/resources/versions";
        }
        if ($dst === null) {
            $dst = __DIR__ . "/../files/versions";
        }

        $dir = opendir($src);
        if (!file_exists($dst)) {
            @mkdir($dst, recursive: true);
        }
        while (($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    self::copyTestFiles($src . '/' . $file, $dst . '/' . $file);
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
        self::copyTestFiles();
        $blockFiles = [];
        $versions = new DirectoryIterator(__DIR__ . "/../files/versions");
        foreach ($versions as $version) {
            if (!$version->isDot()) {
                $dirName = $version->getFilename();
                if (version_compare($dirName, "1.17", ">=") || $dirName === "latest") {
                    $blockFiles[$dirName] = [
                        [
                            new File(__DIR__ . "/../files/versions/" . $dirName . "/region/r.0.0.mca")
                        ],
                        [
                            new File(__DIR__ . "/../files/versions/" . $dirName . "/entities/r.0.0.mca")
                        ],
                    ];
                } else {
                    $blockFiles[$dirName] = [
                        [
                            new File(__DIR__ . "/../files/versions/" . $dirName . "/region/r.0.0.mca")
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
        self::copyTestFiles();
        $blockFiles = [];
        $versions = new DirectoryIterator(__DIR__ . "/../files/versions");
        foreach ($versions as $version) {
            if (!$version->isDot()) {
                $dirName = $version->getFilename();
                if (version_compare($dirName, "1.17", ">=") || $dirName === "latest") {
                    $blockFiles[$dirName] = [
                        [
                            new File(__DIR__ . "/../files/versions/" . $dirName . "/region/r.-1.-1.mca")
                        ],
                        [
                            new File(__DIR__ . "/../files/versions/" . $dirName . "/entities/r.-1.-1.mca")
                        ],
                    ];
                } else {
                    $blockFiles[$dirName] = [
                        [
                            new File(__DIR__ . "/../files/versions/" . $dirName . "/region/r.-1.-1.mca")
                        ],
                        []
                    ];
                }
            }
        }
        return $blockFiles;
    }

    /**
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    #[DataProvider('provideSingleBlockFile')]
    public function testGetBlock(array $blockFiles, array $entitiesFiles): void
    {
        $hawk = new Hawk(blockFiles: $blockFiles, entitiesFiles: $entitiesFiles);
        if (!$this->areBlocksSupportedInDataSet()) {
            $this->expectException(VersionNotSupportedException::class);
        }
        $block = $hawk->getBlock($this->getBlockCoords());
        $this->assertEquals("minecraft:furnace", $block->getPaletteBlock()->getName());
        $this->closeFiles($blockFiles, $entitiesFiles);
    }

    /**
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    #[DataProvider('provideNegativeSingleBlockFile')]
    public function testGetBlockNegative(array $blockFiles, array $entitiesFiles): void
    {
        $hawk = new Hawk(blockFiles: $blockFiles, entitiesFiles: $entitiesFiles);
        if (!$this->areBlocksSupportedInDataSet()) {
            $this->expectException(VersionNotSupportedException::class);
        }
        $block = $hawk->getBlock($this->getNegativeBlockCoords());
        $this->assertEquals("minecraft:furnace", $block->getPaletteBlock()->getName());
        $this->closeFiles($blockFiles, $entitiesFiles);
    }

    /**
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    #[DataProvider('provideSingleBlockFile')]
    public function testReplaceBlock(array $blockFiles, array $entitiesFiles): void
    {
        $hawk = new Hawk(blockFiles: $blockFiles, entitiesFiles: $entitiesFiles);
        if (!$this->areBlocksSupportedInDataSet()) {
            $this->expectException(VersionNotSupportedException::class);
        }
        $block = $hawk->getBlock($this->getBlockCoords());
        $this->assertEquals("minecraft:furnace", $block->getPaletteBlock()->getName());
        $hawk->replaceBlock($this->getBlockCoords(), "minecraft:wool");
        $hawk->save();

        $hawk = new Hawk(blockFiles: $blockFiles, entitiesFiles: $entitiesFiles);
        $block = $hawk->getBlock($this->getBlockCoords());
        $this->assertEquals("minecraft:wool", $block->getPaletteBlock()->getName());
        $this->closeFiles($blockFiles, $entitiesFiles);
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

    /**
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    #[DataProvider('provideSingleBlockFile')]
    public function testSaveKeepsSectionsAndTheirLight(array $blockFiles, array $entitiesFiles): void
    {
        // Entries that change when a block is replaced
        $blockEntryKeys = ["BlockStates", "Palette", "block_states"];
        $withoutBlockEntries = fn(array $entries) => array_diff_key($entries, array_flip($blockEntryKeys));

        $sectionsBefore = $this->readRawSections($blockFiles[0]);

        $hawk = new Hawk(blockFiles: $blockFiles, entitiesFiles: $entitiesFiles);
        if ($this->areBlocksSupportedInDataSet()) {
            $hawk->replaceBlock($this->getBlockCoords(), "minecraft:stone");
        }
        $hawk->save();

        $sectionsAfter = $this->readRawSections($blockFiles[0]);

        $this->assertSame(array_keys($sectionsBefore), array_keys($sectionsAfter), "Sections of the chunk changed");
        foreach ($sectionsBefore as $y => $entriesBefore) {
            $this->assertSame(
                $withoutBlockEntries($entriesBefore),
                $withoutBlockEntries($sectionsAfter[$y]),
                "Entries of section " . $y . " changed"
            );
        }
        $this->closeFiles($blockFiles, $entitiesFiles);
    }

    /**
     * In the 1.17.1 test world, section 8 of chunk 0, 0 has no blocks, only "SkyLight"
     *
     * @return void
     * @throws Exception
     */
    public function testReplaceBlockInSectionWithoutBlocksKeepsItsLight(): void
    {
        $this->copyTestFiles();
        $blockFile = new File(__DIR__ . "/../files/versions/1.17.1/region/r.0.0.mca");
        $coordinates = new McCoordinates3D(1, 8 * 16 + 1, 1);

        $skyLightBefore = $this->readRawSections($blockFile)[8]["SkyLight"];

        $hawk = new Hawk(blockFiles: [$blockFile]);
        $hawk->replaceBlock($coordinates, "minecraft:stone");
        $hawk->save();

        $sectionAfter = $this->readRawSections($blockFile)[8];
        $this->assertSame($skyLightBefore, $sectionAfter["SkyLight"]);
        $this->assertArrayHasKey("BlockStates", $sectionAfter);
        $hawk = new Hawk(blockFiles: [$blockFile]);
        $this->assertEquals("minecraft:stone", $hawk->getBlock($coordinates)->getPaletteBlock()->getName());
        $blockFile->close();
    }

    /**
     * @param array $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    #[DataProvider('provideNegativeSingleBlockFile')]
    public function testReplaceBlockNegative(array $blockFiles, array $entitiesFiles): void
    {
        $hawk = new Hawk(blockFiles: $blockFiles, entitiesFiles: $entitiesFiles);
        if (!$this->areBlocksSupportedInDataSet()) {
            $this->expectException(VersionNotSupportedException::class);
        }
        $block = $hawk->getBlock($this->getNegativeBlockCoords());
        $this->assertEquals("minecraft:furnace", $block->getPaletteBlock()->getName());
        $hawk->replaceBlock($this->getNegativeBlockCoords(), "minecraft:wool");
        $hawk->save();

        $hawk = new Hawk(blockFiles: $blockFiles, entitiesFiles: $entitiesFiles);
        $block = $hawk->getBlock($this->getNegativeBlockCoords());
        $this->assertEquals("minecraft:wool", $block->getPaletteBlock()->getName());
        $this->closeFiles($blockFiles, $entitiesFiles);
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