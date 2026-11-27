<?php

declare(strict_types=1);

namespace Aternos\Hawk\Versions\v5023;

use Aternos\Hawk\BlockState;
use Aternos\Hawk\Data;
use Aternos\Hawk\Palette;
use Aternos\Hawk\PaletteBlock;
use Aternos\Hawk\Section;
use Aternos\Hawk\Versions\v3105\SectionV3105;
use Aternos\Nbt\Tag\CompoundTag;

class SectionV5023 extends SectionV3105
{
    /**
     * @return PaletteBlock
     */
    protected static function newPaletteBlock(): PaletteBlock
    {
        return PaletteBlockV5023::new();
    }

    /**
     * @param Palette $palette
     * @param Data $data
     * @return BlockState
     */
    protected static function newBlockState(Palette $palette, Data $data): BlockState
    {
        return BlockStateV5023::new($palette, $data);
    }

    /**
     * @param Section $section
     * @param CompoundTag $tag
     * @return BlockState
     */
    protected static function newBlockStateFromTag(Section $section, CompoundTag $tag): BlockState
    {
        return BlockStateV5023::newFromTag($tag, $section->version, $section->coordinates->y);
    }
}
