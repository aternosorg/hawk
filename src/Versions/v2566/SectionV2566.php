<?php

namespace Aternos\Hawk\Versions\v2566;

use Aternos\Hawk\Data;
use Aternos\Hawk\Section;
use Aternos\Hawk\Versions\v1519\SectionV1519;
use Aternos\Nbt\Tag\CompoundTag;

/**
 * Since 1.16, block state indices no longer span across two longs.
 */
class SectionV2566 extends SectionV1519
{
    /**
     * @param array $dataBlocks
     * @return Data
     */
    protected static function newData(array $dataBlocks): Data
    {
        return DataV2566::new($dataBlocks);
    }

    /**
     * @param Section $section
     * @param CompoundTag $tag
     * @param int $sectionY
     * @return Data
     */
    protected static function newDataFromTag(Section $section, CompoundTag $tag, int $sectionY): Data
    {
        return DataV2566::newFromTag($tag->getLongArray($section->dataTagName), $section->palette, $section->version, $sectionY);
    }
}
