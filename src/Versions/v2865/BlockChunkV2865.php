<?php

namespace Aternos\Hawk\Versions\v2865;

use Aternos\Hawk\McCoordinates2D;
use Aternos\Hawk\McCoordinates3D;
use Aternos\Hawk\Section;
use Aternos\Hawk\Versions\v2860\BlockChunkV2860;
use Aternos\Nbt\Tag\CompoundTag;
use Aternos\Nbt\Tag\Tag;
use Exception;

class BlockChunkV2865 extends BlockChunkV2860
{
    /**
     * @param CompoundTag $tag
     * @param McCoordinates2D $coordinates
     * @param int $version
     * @return Section|null
     */
    public function newSectionFromTag(CompoundTag $tag, McCoordinates2D $coordinates, int $version): ?Section
    {
        return SectionV2865::newFromTag($tag, $coordinates, $version);
    }

    /**
     * @param McCoordinates3D $coordinates
     * @param int $version
     * @return Section
     */
    public function newEmptySection(McCoordinates3D $coordinates, int $version): Section
    {
        return SectionV2865::newEmpty($coordinates, $version);
    }
}