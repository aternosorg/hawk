<?php

namespace Aternos\Hawk\Tests\Unit;

use Aternos\Hawk\BlockRegion;
use Aternos\Hawk\DataBlock;
use Aternos\Hawk\Exceptions\VersionNotSupportedException;
use Aternos\Hawk\File;
use Exception;

class BlockRegionTest extends HawkTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        foreach ($this->files as $index => $file) {
            $file->close();
            unset($this->files[$index]);
        }
    }

    /**
     * @dataProvider provideSingleBlockFile
     * @param File[] $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    public function testGetBlock(array $blockFiles, array $entitiesFiles): void
    {
        $region = new BlockRegion($blockFiles[0]);
        try {
            $block = $region->getBlock($this->getBlockCoords());
        } catch (VersionNotSupportedException $e) {
            $this->expectException(VersionNotSupportedException::class);
            $region->getBlock($this->getBlockCoords());
        }
        $block = $region->getBlock($this->getBlockCoords());
        $this->assertInstanceOf(DataBlock::class, $block);
        $this->closeFiles($blockFiles, $entitiesFiles);
    }

    /**
     * @dataProvider provideSingleBlockFile
     * @param File[] $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    public function testReplaceBlock(array $blockFiles, array $entitiesFiles): void
    {
        $region = new BlockRegion($blockFiles[0]);

        try {
            $block = $region->getBlock($this->getBlockCoords());
        } catch (VersionNotSupportedException $e) {
            $this->expectException(VersionNotSupportedException::class);
            $region->getBlock($this->getBlockCoords());
            return;
        }
        $this->assertInstanceOf(DataBlock::class, $block);
        $this->assertEquals("minecraft:furnace", $block->getPaletteBlock()->getName());
        $region->replaceBlock($this->getBlockCoords(), "minecraft:wool");
        $block = $region->getBlock($this->getBlockCoords());
        $this->assertInstanceOf(DataBlock::class, $block);
        $this->assertEquals("minecraft:wool", $block->getPaletteBlock()->getName());
        $this->closeFiles($blockFiles, $entitiesFiles);
    }


}