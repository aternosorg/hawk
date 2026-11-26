<?php

namespace Aternos\Hawk\Versions\v1519;

use Aternos\Hawk\Data;
use Aternos\Hawk\DataBlock;
use Aternos\Hawk\Section;
use Aternos\Nbt\Tag\LongArrayTag;

/**
 * Before 1.16, the palette indices of a section form one continuous bit stream,
 * starting at the lowest bit of the first long.
 * An index can therefore start in one long and continue in the next one.
 */
class DataV1519 extends Data
{
    /**
     * @param $tag
     * @param $bitLength
     * @param $bitmask
     * @param $paletteBlocks
     * @param $sectionY
     * @return void
     */
    public function readInts($tag, $bitLength, $bitmask, $paletteBlocks, $sectionY): void
    {
        $longs = [];
        foreach ($tag as $long) {
            $longs[] = $long;
        }
        if (count($longs) === 0) {
            return;
        }

        for ($index = 0; $index < Section::BLOCKS_PER_SECTION; $index++) {
            $ref = $this->readPaletteIndex($longs, $index, $bitLength, $bitmask);
            $this->dataBlocks[] = new DataBlock($ref, $paletteBlocks[$ref]);
        }
    }

    /**
     * Reads the palette index of block number $index from the bit stream in $longs
     *
     * @param int[] $longs
     * @param int $index
     * @param int $bitLength
     * @param int $bitmask
     * @return int
     */
    protected function readPaletteIndex(array $longs, int $index, int $bitLength, int $bitmask): int
    {
        $streamPosition = $index * $bitLength;
        $longIndex = intdiv($streamPosition, static::INTEGER_SIZE);
        $offsetInLong = $streamPosition % static::INTEGER_SIZE;

        // PHP shifts signed, so a negative long fills the high bits with ones.
        // They are cleared by the bitmasks below.
        $ref = $longs[$longIndex] >> $offsetInLong;

        $bitsInFirstLong = static::INTEGER_SIZE - $offsetInLong;
        if ($bitsInFirstLong < $bitLength) {
            // The remaining high bits of the index are the lowest bits of the next long
            $ref &= $this->getBitMask($bitsInFirstLong);
            $ref |= $longs[$longIndex + 1] << $bitsInFirstLong;
        }
        return $ref & $bitmask;
    }

    /**
     * @param int $bitLength
     * @param int $section
     * @return LongArrayTag
     */
    public function writeInts(int $bitLength, int $section): LongArrayTag
    {
        $longCount = (int)ceil(Section::BLOCKS_PER_SECTION * $bitLength / static::INTEGER_SIZE);
        $longs = array_fill(0, $longCount, 0);

        for ($index = 0; $index < Section::BLOCKS_PER_SECTION; $index++) {
            $ref = $this->dataBlocks[$index]->getId();
            $streamPosition = $index * $bitLength;
            $longIndex = intdiv($streamPosition, static::INTEGER_SIZE);
            $offsetInLong = $streamPosition % static::INTEGER_SIZE;

            // Bits that do not fit into this long are shifted out here
            $longs[$longIndex] |= $ref << $offsetInLong;

            $bitsInFirstLong = static::INTEGER_SIZE - $offsetInLong;
            if ($bitsInFirstLong < $bitLength) {
                // ...and written to the lowest bits of the next long
                $longs[$longIndex + 1] |= $ref >> $bitsInFirstLong;
            }
        }

        $data = new LongArrayTag();
        foreach ($longs as $long) {
            $data[] = $long;
        }
        return $data;
    }

}