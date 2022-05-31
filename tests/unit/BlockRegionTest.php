<?php

namespace Aternos\Hawk\Tests\Unit;

use Aternos\Hawk\BlockRegion;
use Aternos\Hawk\DataBlock;
use Aternos\Hawk\File;
use Exception;

class BlockRegionTest extends HawkTestCase
{
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
        $block = $region->getBlock($this->getBlockCoords());
        $this->assertInstanceOf(DataBlock::class, $block);
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
        $block = $region->getBlock($this->getBlockCoords());
        $this->assertInstanceOf(DataBlock::class, $block);
        $this->assertEquals("minecraft:furnace", $block->getPaletteBlock()->getName());
        $region->replaceBlock($this->getBlockCoords(), "minecraft:wool");
        $block = $region->getBlock($this->getBlockCoords());
        $this->assertInstanceOf(DataBlock::class, $block);
        $this->assertEquals("minecraft:wool", $block->getPaletteBlock()->getName());
    }


}