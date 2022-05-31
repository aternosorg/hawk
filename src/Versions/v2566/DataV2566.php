<?php

namespace Aternos\Hawk\Versions\v2566;

use Aternos\Hawk\Data;
use Aternos\Hawk\DataBlock;
use Aternos\Hawk\Section;
use Aternos\Nbt\Tag\LongArrayTag;

class DataV2566 extends Data
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
        $blockCounter = 0;
        $counter = 0;
        foreach ($tag as $int64) {
            $blocks = 0;
            for ($bitsToRead = static::INTEGER_SIZE; $bitsToRead >= $bitLength && $blockCounter < Section::BLOCKS_PER_SECTION; $blockCounter++, $bitsToRead -= $bitLength) {
                $ref = $int64 & $bitmask;
                $this->dataBlocks[] = new DataBlock($ref, $paletteBlocks[$ref]);
                $int64 = $this->unsignedRightShift($int64, $bitLength, $bitsToRead);
                $blocks++;
                $counter++;
            }
        }
    }

    /**
     * @param int $bitLength
     * @param int $section
     * @return LongArrayTag
     */
    public function writeInts(int $bitLength, int $section): LongArrayTag
    {
        $data = new LongArrayTag();
        $dataArrayLength = ceil(4096 / floor(static::INTEGER_SIZE / $bitLength));
        $blockCounter = 0;
        for ($i = 0; $i < $dataArrayLength; $i++) {
            $int64 = 0;
            for ($bitsToWrite = static::INTEGER_SIZE; $bitsToWrite >= $bitLength  && $blockCounter < Section::BLOCKS_PER_SECTION; $bitsToWrite -= $bitLength , $blockCounter++) {
                $ref = $this->dataBlocks[$blockCounter]->getId();
                $shiftedRef = $ref << (static::INTEGER_SIZE - $bitsToWrite);
                $int64 |= $shiftedRef;
            }
            $data[] = $int64;
        }
        return $data;
    }
}