<?php

namespace Aternos\Hawk\Versions\v2724;

use Aternos\Hawk\Data;
use Aternos\Hawk\Section;
use Aternos\Hawk\Versions\v2586\SectionV2586;
use Aternos\Nbt\Tag\CompoundTag;


class SectionV2724 extends SectionV2586
{
    /**
     * Overloaded with new and newFromTag
     */
    protected function __construct()
    {
        parent::__construct();
    }

    protected static function newData(array $dataBlocks): Data
    {
        return DataV2724::new($dataBlocks);
    }

    protected static function newDataFromTag(Section $section, CompoundTag $tag, int $sectionY): Data
    {
        return DataV2724::newFromTag($tag->getLongArray($section->dataTagName), $section->palette, $section->version, $sectionY);
    }
}