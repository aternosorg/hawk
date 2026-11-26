<?php

namespace Aternos\Hawk\Versions\v1139;

use Aternos\Hawk\Data;
use Aternos\Hawk\DataBlock;
use Aternos\Hawk\Exceptions\VersionNotSupportedException;
use Aternos\Hawk\McCoordinates2D;
use Aternos\Hawk\McCoordinates3D;
use Aternos\Hawk\Palette;
use Aternos\Hawk\PaletteBlock;
use Aternos\Hawk\Section;
use Aternos\Nbt\Tag\CompoundTag;

/**
 * Sections before the flattening (1.13) store numeric block ids instead of a palette.
 * Reading or changing blocks is not supported, the section is only kept as its raw tag
 * so that it is written back unchanged.
 */
class SectionV1139 extends Section
{
    /**
     * Overloaded with newFromTag
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * @inheritDoc
     * @throws VersionNotSupportedException
     */
    public static function newEmpty(McCoordinates3D $coordinates, int $version): Section
    {
        throw new VersionNotSupportedException((string)$version);
    }

    /**
     * @inheritDoc
     */
    public static function newFromTag(CompoundTag $tag, McCoordinates2D $coordinates, int $version): ?Section
    {
        $section = new static();
        $section->tag = $tag;
        $section->version = $version;
        $section->coordinates = new McCoordinates3D($coordinates->x, $tag->getByte("Y")->getValue(), $coordinates->z);
        return $section;
    }

    /**
     * @inheritDoc
     * @throws VersionNotSupportedException
     */
    protected static function newData(array $dataBlocks): Data
    {
        throw new VersionNotSupportedException("pre-1.13");
    }

    /**
     * @inheritDoc
     * @throws VersionNotSupportedException
     */
    protected static function newDataFromTag(Section $section, CompoundTag $tag, int $sectionY): Data
    {
        throw new VersionNotSupportedException((string)$section->version);
    }

    /**
     * @inheritDoc
     * @throws VersionNotSupportedException
     */
    protected static function newPalette(array $paletteBlocks): Palette
    {
        throw new VersionNotSupportedException("pre-1.13");
    }

    /**
     * @inheritDoc
     * @throws VersionNotSupportedException
     */
    public function getBlock(McCoordinates3D $coordinates): DataBlock
    {
        throw new VersionNotSupportedException((string)$this->version);
    }

    /**
     * @inheritDoc
     * @throws VersionNotSupportedException
     */
    public function replaceBlock(McCoordinates3D $coordinates, string $blockName = "minecraft:stone"): void
    {
        throw new VersionNotSupportedException((string)$this->version);
    }

    /**
     * @inheritDoc
     */
    public function createTag(): CompoundTag
    {
        return $this->tag;
    }

    /**
     * @inheritDoc
     * @throws VersionNotSupportedException
     */
    public function createPaletteBlock(string $name = "minecraft:air", array $properties = []): PaletteBlock
    {
        throw new VersionNotSupportedException((string)$this->version);
    }

    /**
     * @inheritDoc
     * @throws VersionNotSupportedException
     */
    public function createDataBlock(string $blockName = "minecraft:stone"): DataBlock
    {
        throw new VersionNotSupportedException((string)$this->version);
    }
}
