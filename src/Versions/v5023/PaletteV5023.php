<?php

declare(strict_types=1);

namespace Aternos\Hawk\Versions\v5023;

use Aternos\Hawk\Palette;
use Aternos\Hawk\PaletteBlock;
use Aternos\Nbt\Tag\CompoundTag;

class PaletteV5023 extends Palette
{
    /**
     * @param CompoundTag $tag
     * @return PaletteBlock
     */
    protected static function newPaletteBlockFromTag(CompoundTag $tag): PaletteBlock
    {
        return PaletteBlockV5023::newFromTag($tag);
    }
}
