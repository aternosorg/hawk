<?php

declare(strict_types=1);

namespace Aternos\Hawk\Versions\v5023;

use Aternos\Hawk\BlockState;
use Aternos\Hawk\Palette;
use Aternos\Hawk\PaletteBlock;
use Aternos\Nbt\Tag\ListTag;

class BlockStateV5023 extends BlockState
{
    /**
     * @param ListTag $tag
     * @return Palette
     */
    protected static function newPaletteFromTag(ListTag $tag): Palette
    {
        return PaletteV5023::newFromTag($tag);
    }

    /**
     * @param string $name
     * @param array $properties
     * @return PaletteBlock
     */
    protected function newPaletteBlock(string $name, array $properties): PaletteBlock
    {
        return PaletteBlockV5023::new($name, $properties);
    }
}
