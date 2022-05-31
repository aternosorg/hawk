<?php

namespace Aternos\Hawk;

use Aternos\Nbt\Tag\LongArrayTag;
use Exception;

abstract class Data
{
    const INTEGER_SIZE = 64;

    protected int $version;

    /**
     * @var DataBlock[]
     */
    protected array $dataBlocks = [];

    /**
     * Overloaded
     */
    private function __construct()
    {
    }

    /**
     * @param array $dataBlocks
     * @return Data
     */
    public static function new(array $dataBlocks): Data
    {
        $data = new static();
        $data->dataBlocks = $dataBlocks;
        return $data;
    }

    /**
     * @param LongArrayTag|null $tag
     * @param Palette $palette
     * @param int $version
     * @param int $sectionY
     * @return Data
     */
    public static function newFromTag(?LongArrayTag $tag, Palette $palette, int $version, int $sectionY): Data
    {
        $data = new static();
        $data->version = $version;
        if ($tag === null) return $data;
        $bitLength = $data->getBitLength($palette->getLength() - 1);
        $bitmask = $data->getBitMask($bitLength);
        $paletteBlocks = $palette->getPaletteBlocks();
        $data->readInts($tag, $bitLength, $bitmask, $paletteBlocks, $sectionY);
        return $data;
    }

    /**
     * @param int $number
     * @return int
     */
    public function getBitLength(int $number): int
    {
        // Shifts $number until it is null
        for ($length = 0; $number; $length++) {
            $number >>= 1;
            $number &= 0x7FFFFFFFFFFFFFFF;
        }
        // Min bit length according to Minecraft wiki
        if ($length < 4) return 4;
        return $length;
    }

    /**
     * Creates bitmask full of 1's with the length of $number
     *
     * @param int $number
     * @return int
     */
    public function getBitMask_old(int $number): int
    {
        $multipleOfTwo = pow(2, $number);
        return $multipleOfTwo - 1; // 16-1 = 15 -> 1111
    }

    /**
     * @param int $bitLength
     * @return int
     */
    public function getBitMask(int $bitLength): int
    {
        return ((1 << $bitLength) - 1);
    }

    /**
     * @param int $int64
     * @param int $bitLength
     * @param int $bitCounter
     * @return int
     */
    public function unsignedRightShift(int $int64, int $bitLength, int $bitCounter): int
    {
        if ($int64 >= 0) {
            return $int64 >> $bitLength;
        }
        $int64 = $int64 >> $bitLength;
        $bitmask = $this->getBitMask($bitCounter - $bitLength);
        $int64 &= $bitmask;
        return $int64;
    }

    /**
     * @param int $index
     * @return DataBlock
     * @throws Exception
     */
    public function getDataBlock(int $index): DataBlock
    {

        if (isset($this->dataBlocks[$index])) {
            return $this->dataBlocks[$index];
        }
        throw new Exception("DataBlocks[" . $index . "] is null.");
    }

    /**
     * @return DataBlock[]
     */
    public function getDataBlocks(): array
    {
        return $this->dataBlocks;
    }

    /**
     * @codeCoverageIgnore
     * @return int
     */
    public function getDataBlockLength(): int
    {
        return count($this->dataBlocks);
    }

    /**
     * Looks for empty sections.
     *
     *
     * Minecraft does not save sections completely filled with air.
     * Every section has to have 4096 blocks.
     *
     * @codeCoverageIgnore
     * @return bool
     */
    public function isDataBlocksFilled(): bool
    {
        return $this->getDataBlockLength() === Section::BLOCKS_PER_SECTION;
    }

    /**
     * @codeCoverageIgnore
     * @param int $index
     * @param DataBlock $dataBlock
     * @return void
     */
    public function setDataBlock(int $index, DataBlock $dataBlock): void
    {
        $this->dataBlocks[$index] = $dataBlock;
    }

    /**
     * Creates "data" tag
     *
     * @param int $paletteLength
     * @param int|null $section Only for debug
     * @return LongArrayTag
     */
    public function createTag(int $paletteLength, int $section = null): LongArrayTag
    {
        $data = new LongArrayTag();
        if (!$this->isDataBlocksFilled()) {
            return $data;
        }
        $bitLength = $this->getBitLength($paletteLength - 1);

        return $this->writeInts($bitLength, $section);
    }

    /**
     * @param $tag
     * @param int $bitLength
     * @param int $bitmask
     * @param array $paletteBlocks
     * @param int $sectionY
     * @return void
     */
    abstract public function readInts($tag, int $bitLength, int $bitmask, array $paletteBlocks, int $sectionY): void;

    /**
     * @param int $bitLength
     * @param int $section
     * @return LongArrayTag
     */
    abstract public function writeInts(int $bitLength, int $section): LongArrayTag;
}