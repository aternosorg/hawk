<?php

namespace Aternos\Hawk;

class DataBlock
{
    /**
     * @var int
     */
    protected int $id;

    /**
     * @var PaletteBlock
     */
    protected PaletteBlock $paletteBlock;

    /**
     * @param int $id
     * @param PaletteBlock $paletteBlock
     */
    public function __construct(int $id, PaletteBlock $paletteBlock)
    {
        $this->id = $id;
        $this->paletteBlock = $paletteBlock;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @codeCoverageIgnore
     * @return PaletteBlock
     */
    public function getPaletteBlock(): PaletteBlock
    {
        return $this->paletteBlock;
    }

    /**
     * ToString override
     *
     * @codeCoverageIgnore
     * @return string
     */
    public function __toString(): string
    {
        return strval($this->paletteBlock);
    }
}