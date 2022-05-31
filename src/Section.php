<?php

namespace Aternos\Hawk;

use Aternos\Nbt\Tag\CompoundTag;
use Aternos\Nbt\Tag\Tag;
use Exception;

abstract class Section
{
    const BLOCKS_PER_SECTION = 4096;

    protected int $version;

    protected Tag $tag;

    protected McCoordinates3D $coordinates;

    protected string $paletteTagName = "Palette";

    protected string $dataTagName = "BlockStates";

    /**
     * Calculates section coordinates from $coordinates
     *
     * @param McCoordinates3D $coordinates
     * @return McCoordinates3D
     */
    public static function getSectionCoordinates(McCoordinates3D $coordinates): McCoordinates3D
    {
        return new McCoordinates3D(floor($coordinates->x / 16), floor($coordinates->y / 16), floor($coordinates->z / 16));
    }

    /**
     * Calculates block coordinates relative to its section
     *
     * @param McCoordinates3D $coordinates
     * @return McCoordinates3D
     */
    public static function getBlockCoordinates(McCoordinates3D $coordinates): McCoordinates3D
    {
        return new McCoordinates3D(static::minecraftModulo($coordinates->x, 16), static::minecraftModulo($coordinates->y, 16), static::minecraftModulo($coordinates->z, 16));
    }

    protected static function minecraftModulo(int $dividend, int $divisor): int
    {
        return (($dividend % $divisor) + $divisor) % $divisor;
    }

    /**
     * Overloaded with newEmpty and newFromTag
     */
    protected function __construct()
    {
    }

    /**
     * @param McCoordinates3D $coordinates
     * @param int $version
     * @return Section
     */
    abstract public static function newEmpty(McCoordinates3D $coordinates, int $version): Section;

    /**
     * @param CompoundTag $tag
     * @param McCoordinates2D $coordinates
     * @param int $version
     * @return Section|null
     */
    abstract public static function newFromTag(CompoundTag $tag, McCoordinates2D $coordinates, int $version): ?Section;

    /**
     * @param array $dataBlocks
     * @return Data
     */
    abstract protected static function newData(array $dataBlocks): Data;

    /**
     * @param Section $section
     * @param CompoundTag $tag
     * @param int $sectionY
     * @return Data
     */
    abstract protected static function newDataFromTag(Section $section, CompoundTag $tag, int $sectionY): Data;

    /**
     * @param array $paletteBlocks
     * @return Palette
     */
    abstract protected static function newPalette(array $paletteBlocks): Palette;


    /**
     * @return McCoordinates3D
     */
    public function getCoordinates(): McCoordinates3D
    {
        return $this->coordinates;
    }

    /**
     * @param McCoordinates3D $coordinates
     * @return int Index of block at $coordinates from DataBlocks array
     */
    public function calcDataBlocksIndex(McCoordinates3D $coordinates): int
    {
        return $coordinates->y * 16 * 16 + $coordinates->z * 16 + $coordinates->x;
    }

    /**
     * @codeCoverageIgnore
     * @param McCoordinates3D $coordinates
     * @return DataBlock Block at $coordinates
     * @throws Exception "Result is null."
     */
    abstract public function getBlock(McCoordinates3D $coordinates): DataBlock;

    /**
     * Replaces block at $coordinates with $blockName
     *
     * @codeCoverageIgnore
     * @param McCoordinates3D $coordinates
     * @param string $blockName
     * @return void
     */
    abstract public function replaceBlock(McCoordinates3D $coordinates, string $blockName = "minecraft:stone"): void;

    /**
     * Creates "section" tag
     *
     * @return CompoundTag
     * @throws Exception
     */
    abstract public function createTag(): CompoundTag;

    /**
     * @param string $name
     * @param array $properties
     * @return PaletteBlock
     */
    abstract public function createPaletteBlock(string $name = "minecraft:air", array $properties = []): PaletteBlock;

    /**
     * @param string $blockName
     * @return DataBlock
     */
    abstract public function createDataBlock(string $blockName = "minecraft:stone"): DataBlock;
}