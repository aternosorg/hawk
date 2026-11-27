<?php

namespace Aternos\Hawk\Tests\Integration;

use Aternos\Hawk\Exceptions\VersionNotSupportedException;
use Aternos\Hawk\File;
use Aternos\Hawk\Hawk;
use Aternos\Hawk\McCoordinates3D;
use Exception;
use PHPUnit\Framework\Attributes\DataProvider;

class BlockTest extends IntegrationTestCase
{
    protected static function getTestFilesDirectory(): string
    {
        return __DIR__ . "/../files/blocks/versions";
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
        $blockFile = new File(static::getTestFilesDirectory() . "/1.17.1/region/r.0.0.mca");
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
}
