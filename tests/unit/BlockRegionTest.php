<?php

declare(strict_types=1);

namespace Aternos\Hawk\Tests\Unit;

use Aternos\Hawk\BlockRegion;
use Aternos\Hawk\DataBlock;
use Aternos\Hawk\Exceptions\VersionNotSupportedException;
use Aternos\Hawk\File;
use Exception;
use PHPUnit\Framework\Attributes\DataProvider;

class BlockRegionTest extends HawkTestCase
{
    /**
     * @param File[] $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    #[DataProvider('provideSingleBlockFile')]
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
     * @param File[] $blockFiles
     * @param array $entitiesFiles
     * @return void
     * @throws Exception
     */
    #[DataProvider('provideSingleBlockFile')]
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