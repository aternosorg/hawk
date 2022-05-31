<?php

namespace Aternos\Hawk\Versions\v2860;

use Aternos\Hawk\BlockState;
use Aternos\Hawk\Data;
use Aternos\Hawk\DataBlock;
use Aternos\Hawk\McCoordinates2D;
use Aternos\Hawk\McCoordinates3D;
use Aternos\Hawk\Palette;
use Aternos\Hawk\PaletteBlock;
use Aternos\Hawk\Section;
use Aternos\Hawk\Versions\v2724\DataV2724;
use Aternos\Hawk\Versions\v2730\SectionV2730;
use Aternos\Nbt\Tag\ByteTag;
use Aternos\Nbt\Tag\CompoundTag;
use Aternos\Nbt\Tag\ListTag;
use BadMethodCallException;

class SectionV2860 extends SectionV2730
{
    protected BlockState $blockState;

    protected string $blockStateTagName = "block_states";

    protected string $biomesTagName = "biomes";

    protected ?CompoundTag $biomes = null;

    protected const BAD_METHOD_MESSAGE = "Not used anymore due to structural redesign.";

    /**
     * Overloaded with new and newFromTag
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    public static function newEmpty(McCoordinates3D $coordinates, int $version): Section
    {
        $section = new static();
        $section->coordinates = $coordinates;
        $section->version = $version;

        $paletteBlocks[] = PaletteBlock::new();
        $palette = static::newPalette($paletteBlocks);

        $dataBlocks = [];
        $dataBlock = new DataBlock(0, $paletteBlocks[0]);
        for ($i = 0; $i < static::BLOCKS_PER_SECTION; $i++) {
            $dataBlocks[] = $dataBlock;
        }
        $data = static::newData($dataBlocks);

        $section->blockState = static::newBlockState($palette, $data);
        return $section;
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
        $section->biomes = $tag->getCompound($section->biomesTagName);
        $blockStateTag = $tag->getCompound($section->blockStateTagName);
        if($blockStateTag === null){
            return null;
        }
        $section->blockState = static::newBlockStateFromTag($section, $blockStateTag);
        return $section;
    }

    /**
     * @param array $dataBlocks
     * @return Data
     */
    protected static function newData(array $dataBlocks): Data
    {
        return DataV2724::new($dataBlocks);
    }

    /**
     * @param Section $section
     * @param CompoundTag $tag
     * @param int $sectionY
     * @return Data
     */
    protected static function newDataFromTag(Section $section, CompoundTag $tag, int $sectionY): Data
    {
        throw new BadMethodCallException(static::BAD_METHOD_MESSAGE);
    }

    /**
     * @param ListTag $paletteTag
     * @return Palette
     */
    protected static function newPaletteFromTag(ListTag $paletteTag): Palette
    {
        throw new BadMethodCallException(static::BAD_METHOD_MESSAGE);
    }

    /**
     * @param Palette $palette
     * @param Data $data
     * @return BlockState
     */
    protected static function newBlockState(Palette $palette, Data $data): BlockState
    {
        return BlockState::new($palette, $data);
    }

    /**
     * @param Section $section
     * @param CompoundTag $tag
     * @return BlockState
     */
    protected static function newBlockStateFromTag(Section $section, CompoundTag $tag): BlockState
    {
        return BlockState::newFromTag($tag,$section->version, $section->coordinates->y);
    }

    /**
     * @inheritDoc
     */
    public function getBlock(McCoordinates3D $coordinates): DataBlock
    {
        $blockCoords = Section::getBlockCoordinates($coordinates);
        $index = $this->calcDataBlocksIndex($blockCoords);

        return $this->blockState->getBlock($index);
    }

    /**
     * @inheritDoc
     */
    public function replaceBlock(McCoordinates3D $coordinates, string $blockName = "minecraft:stone"): void
    {
        $index = $this->calcDataBlocksIndex(Section::getBlockCoordinates($coordinates));
        $dataBlock = $this->blockState->createDataBlock($blockName);
        $this->blockState->getData()->setDataBlock($index, $dataBlock);
    }

    /**
     * @inheritDoc
     */
    public function createTag(): CompoundTag
    {
        $section = new CompoundTag();
        $section->set("Y", (new ByteTag())->setValue($this->coordinates->y));
        $section->set($this->blockStateTagName, $this->blockState->createTag($this->coordinates->y));
        if($this->biomes !== null){
            $section->set($this->biomesTagName, $this->biomes);
        }
        return $section;
    }

    /**
     * @param string $name
     * @param array $properties
     * @return PaletteBlock
     */
    public function createPaletteBlock(string $name = "minecraft:air", array $properties = []): PaletteBlock
    {
        return $this->blockState->createPaletteBlock($name, $properties);
    }

    /**
     * @param string $blockName
     * @return DataBlock
     */
    public function createDataBlock(string $blockName = "minecraft:stone"): DataBlock
    {
        return $this->blockState->createDataBlock($blockName);
    }
}