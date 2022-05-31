<?php

namespace Aternos\Hawk;

use Aternos\Nbt\Tag\CompoundTag;
use Aternos\Nbt\Tag\ListTag;
use Exception;

class Palette
{
    protected int $length;

    protected array $paletteBlocks = [];

    /**
     * Overloaded
     */
    public function __construct()
    {
    }

    /**
     * Constructor
     *
     * @param array $paletteBlocks
     * @return Palette
     */
    public static function new(array $paletteBlocks): Palette
    {
        $palette = new static();
        $palette->paletteBlocks = $paletteBlocks;
        return $palette;
    }

    /**
     * Constructor
     *
     * @param ListTag $tag
     * @return Palette
     */
    public static function newFromTag(ListTag $tag): Palette
    {
        $palette = new static();
        foreach ($tag as $block) {
            $palette->paletteBlocks[] = PaletteBlock::newFromTag($block);
        }
        return $palette;
    }

    /**
     * @param PaletteBlock $paletteBlock
     * @return int
     */
    public function addPaletteBlock(PaletteBlock $paletteBlock): int
    {
        $length = $this->getLength();
        $this->paletteBlocks[] = $paletteBlock;
        return $length;
    }

    /**
     * @param string $name
     * @return PaletteBlock|null
     */
    public function findPaletteBlock(string $name): ?PaletteBlock
    {
        foreach ($this->getPaletteBlocks() as $paletteBlock) {
            if ($paletteBlock->getName() === $name) return $paletteBlock;
        }
        return null;
    }

    /**
     * @return PaletteBlock[]
     */
    public function getPaletteBlocks(): array
    {
        return $this->paletteBlocks;
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return count($this->paletteBlocks);
    }

    /**
     * Creates "palette" tag
     *
     * @return ListTag
     * @throws Exception
     */
    public function createTag(): ListTag
    {
        $palette = new ListTag();
        $palette->setContentTag(CompoundTag::TYPE);
        foreach ($this->getPaletteBlocks() as $paletteBlock) {
            $palette[] = $paletteBlock->createTag();
        }
        return $palette;
    }
}