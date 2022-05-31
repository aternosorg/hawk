<?php

namespace Aternos\Hawk;

use Aternos\Hawk\Versions\v2860\DataV2860;
use Aternos\Nbt\Tag\CompoundTag;
use Exception;

class BlockState
{
    protected int $version;

    protected Palette $palette;

    protected Data $data;

    protected string $paletteTagName = "palette";

    protected string $dataTagName = "data";

    /**
     * Overloaded
     */
    private function __construct()
    {
    }

    /**
     * @param CompoundTag $tag
     * @param int $version
     * @param int $sectionY
     * @return BlockState
     */
    public static function newFromTag(CompoundTag $tag, int $version, int $sectionY): BlockState
    {
        $blockState = new static();
        $blockState->version = $version;
        $blockState->palette = Palette::newFromTag($tag->getList($blockState->paletteTagName));
        $blockState->data = DataV2860::newFromTag($tag->getLongArray($blockState->dataTagName), $blockState->palette, $blockState->version, $sectionY);
        return $blockState;
    }

    /**
     * Constructor
     *
     * @param Palette $palette
     * @param Data $data
     * @return BlockState
     */
    public static function new(Palette $palette, Data $data): BlockState
    {
        $blockState = new static();
        $blockState->palette = $palette;
        $blockState->data = $data;
        return $blockState;
    }

    /**
     * @return Data
     */
    public function getData(): Data
    {
        return $this->data;
    }

    /**
     * @return Palette
     */
    public function getPalette(): Palette
    {
        return $this->palette;
    }

    /**
     * @param int $index
     * @return DataBlock
     * @throws Exception
     */
    public function getBlock(int $index): DataBlock
    {
        // If section is empty
        if (isset($this->palette) && $this->getPalette()->getLength() === 1 && $this->getPalette()->getPaletteBlocks()[0]->getName() === "minecraft:air") {
            return new DataBlock(0, $this->getPalette()->getPaletteBlocks()[0]);
        }
        return $this->getData()->getDataBlock($index);
    }

    /**
     * Creates palette block and adds it to palette
     *
     * @param string $name
     * @param array $properties
     * @return PaletteBlock
     */
    public function createPaletteBlock(string $name = "minecraft:air", array $properties = []): PaletteBlock
    {
        $paletteBlock = PaletteBlock::new($name, $properties);
        $this->palette->addPaletteBlock($paletteBlock);
        return $paletteBlock;
    }

    /**
     * Creates new DataBlock with $blockName type
     *
     * @param string $blockName
     * @return DataBlock
     */
    public function createDataBlock(string $blockName = "minecraft:stone"): DataBlock
    {
        $paletteBlock = $this->palette->findPaletteBlock($blockName);
        if ($paletteBlock === null) {
            $paletteBlock = $this->createPaletteBlock($blockName);
        }
        return new DataBlock(array_search($paletteBlock, $this->palette->getPaletteBlocks()), $paletteBlock);
    }

    /**
     * Create "block_states" tag
     *
     * @param int|null $section
     * @return CompoundTag
     * @throws Exception
     */
    public function createTag(int $section = null): CompoundTag
    {
        $blockState = new CompoundTag();
        $palette = $this->palette->createTag();
        $blockState->set($this->paletteTagName, $palette);
        $data = $this->data->createTag($this->palette->getLength(), $section);
        $blockState->set($this->dataTagName, $data);
        return $blockState;
    }
}