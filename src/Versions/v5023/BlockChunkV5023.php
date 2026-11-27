<?php

declare(strict_types=1);

namespace Aternos\Hawk\Versions\v5023;

use Aternos\Hawk\McCoordinates2D;
use Aternos\Hawk\McCoordinates3D;
use Aternos\Hawk\Section;
use Aternos\Hawk\Versions\v3105\BlockChunkV3105;
use Aternos\Nbt\Tag\CompoundTag;

class BlockChunkV5023 extends BlockChunkV3105
{
    /**
     * @param CompoundTag $tag
     * @param McCoordinates2D $coordinates
     * @param int $version
     * @return Section|null
     */
    public function newSectionFromTag(CompoundTag $tag, McCoordinates2D $coordinates, int $version): ?Section
    {
        return SectionV5023::newFromTag($tag, $coordinates, $version);
    }

    /**
     * @param McCoordinates3D $coordinates
     * @param int $version
     * @return Section
     */
    public function newEmptySection(McCoordinates3D $coordinates, int $version): Section
    {
        return SectionV5023::newEmpty($coordinates, $version);
    }
}
