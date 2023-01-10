<?php

namespace Aternos\Hawk\Versions\v1139;

use Aternos\Hawk\Data;
use Aternos\Hawk\DataBlock;
use Aternos\Hawk\McCoordinates2D;
use Aternos\Hawk\McCoordinates3D;
use Aternos\Hawk\Palette;
use Aternos\Hawk\PaletteBlock;
use Aternos\Hawk\Section;
use Aternos\Nbt\Tag\ByteTag;
use Aternos\Nbt\Tag\CompoundTag;
use Aternos\Nbt\Tag\ListTag;

class SectionV1139 extends Section
{
    protected Palette $palette;

    protected Data $data;

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
        $section->palette = static::newPalette($paletteBlocks);


        $dataBlocks = [];
        $dataBlock = new DataBlock(0, $paletteBlocks[0]);
        for ($i = 0; $i < static::BLOCKS_PER_SECTION; $i++) {
            $dataBlocks[] = $dataBlock;
        }

        $section->data = static::newData($dataBlocks);
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
        $paletteTag = $tag->getList($section->paletteTagName);
        if ($paletteTag === null) {
            return null;
        }
        $section->palette = $section->newPaletteFromTag($paletteTag);
        $section->data = $section->newDataFromTag($section, $tag, $section->coordinates->y);
        return $section;
    }

    /**
     * @param array $dataBlocks
     * @return Data
     */
    protected static function newData(array $dataBlocks): Data
    {
        return DataV1139::new($dataBlocks);
    }

    /**
     * @param Section $section
     * @param CompoundTag $tag
     * @param int $sectionY
     * @return Data
     */
    protected static function newDataFromTag(Section $section, CompoundTag $tag, int $sectionY): Data
    {
        return DataV1139::newFromTag($tag->getLongArray($section->dataTagName), $section->palette, $section->version, $sectionY);
    }

    /**
     * @param array $paletteBlocks
     * @return Palette
     */
    protected static function newPalette(array $paletteBlocks): Palette
    {
        return Palette::new($paletteBlocks);
    }

    /**
     * @param ListTag $paletteTag
     * @return Palette
     */
    protected static function newPaletteFromTag(ListTag $paletteTag): Palette
    {
        return Palette::newFromTag($paletteTag);
    }

    /**
     * @inheritDoc
     */
    public function getBlock(McCoordinates3D $coordinates): DataBlock
    {
        $index = $this->calcDataBlocksIndex(Section::getBlockCoordinates($coordinates));

        // If section is empty
        if (isset($this->palette) && $this->getPalette()->getLength() === 1 && $this->getPalette()->getPaletteBlocks()[0]->getName() === "minecraft:air") {
            return new DataBlock(0, $this->getPalette()->getPaletteBlocks()[0]);
        }
        return $this->getData()->getDataBlock($index);
    }

    /**
     * @inheritDoc
     */
    public function replaceBlock(McCoordinates3D $coordinates, string $blockName = "minecraft:stone"): void
    {
        $index = $this->calcDataBlocksIndex(Section::getBlockCoordinates($coordinates));
        $dataBlock = $this->createDataBlock($blockName);
        $this->getData()->setDataBlock($index, $dataBlock);
    }

    /**
     * @inheritDoc
     */
    public function createTag(): CompoundTag
    {
        $section = new CompoundTag();
        $section->set("Y", (new ByteTag())->setValue($this->coordinates->y));
        $section->set($this->dataTagName, $this->data->createTag($this->getPalette()->getLength(), $this->coordinates->y));
        $section->set($this->paletteTagName, $this->palette->createTag());
        return $section;
    }

    /**
     * @return Palette
     */
    public function getPalette(): Palette
    {
        return $this->palette;
    }

    /**
     * @return Data
     */
    public function getData(): Data
    {
        return $this->data;
    }

    /**
     * @param string $name
     * @param array $properties
     * @return PaletteBlock
     */
    public function createPaletteBlock(string $name = "minecraft:air", array $properties = []): PaletteBlock
    {
        $paletteBlock = PaletteBlock::new($name, $properties);
        $this->getPalette()->addPaletteBlock($paletteBlock);
        return $paletteBlock;
    }

    /**
     * @param string $blockName
     * @return DataBlock
     */
    public function createDataBlock(string $blockName = "minecraft:stone"): DataBlock
    {
        $paletteBlock = $this->getPalette()->findPaletteBlock($blockName);
        if ($paletteBlock === null) {
            $paletteBlock = $this->createPaletteBlock($blockName);
        }
        $arraySearch = array_search($paletteBlock, $this->palette->getPaletteBlocks());

        return new DataBlock($arraySearch, $this->getPalette()->getPaletteBlocks()[$arraySearch]);
    }
}