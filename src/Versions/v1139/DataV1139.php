<?php

namespace Aternos\Hawk\Versions\v1139;

use Aternos\Hawk\Data;
use Aternos\Hawk\DataBlock;
use Aternos\Hawk\Section;
use Aternos\Nbt\Tag\LongArrayTag;

class DataV1139 extends Data
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
        //file_put_contents("LongArrayTag_15_2.txt", $tag); //only for AbstractTest.php
        $numberOfBlocks = floor(static::INTEGER_SIZE / $bitLength); // Number of blocks per int64
        $counter = 0; //breakpoint for DataTest.php
        $fraction = 0;
        $fractioned = false;
        foreach ($tag as $key => $int64) {
            //$debugDataBlocks = []; //only for AbstractTest.php, Breakpoint with condition $int64 !== 0
            for ($blockCounter = 0; $blockCounter < $numberOfBlocks && $counter < Section::BLOCKS_PER_SECTION; $blockCounter++, $counter++) {
                $intBitLength = $this->getBitLength($int64);
                //check for fractions
                if ($intBitLength < $bitLength) {
                    $fractioned = true;
                    $fraction = $int64;
                }
                if ($fractioned === true) {
                    // combine both fractions
                    $fractionBitLength = $this->getBitLength($fraction);
                    $diff = $bitLength - $fractionBitLength;
                    $dummyRef = $int64 >> (64 - ($diff));
                    $dummyRef <<= $fractionBitLength;
                    $ref = $dummyRef & $fraction;
                    $fractioned = false;
                    $fraction = 0;
                } else {
                    // Get the last $bitLength bits of int64 which equal the index of the palette block in the palette
                    $ref = $int64 & $bitmask;
                }

                $this->dataBlocks[] = new DataBlock($ref, $paletteBlocks[$ref]);
                //$debugDataBlocks[] = new DataBlock($ref, $paletteBlocks[$ref]); //only for AbstractTest.php

                // Shifts $bitLength bits to the right -> cut off last index
                $int64 = $int64 >> $bitLength;
                $int64 &= 0x7FFFFFFFFFFFFFFF;
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
        // Number of blocks that fit into an int
        $numberOfBlocks = ceil(static::INTEGER_SIZE / $bitLength);
        $dataArrayLength = ceil(Section::BLOCKS_PER_SECTION * $bitLength / static::INTEGER_SIZE);
        $blockCounter = 0; // Counts to max amount of blocks in section
        $offset = 0;
        $fraction = 0; // Part of a block int that overflows
        for ($i = 0; $i < $dataArrayLength; $i++) {
            $int64 = 0; // New compressed int64 filled with 0's
            for ($j = 0; $j < $numberOfBlocks && $blockCounter < Section::BLOCKS_PER_SECTION; $j++, $blockCounter++) {
                $int64Length = $this->getBitLength($int64); // Current bit length of $int64
                $bitLengthDiff = static::INTEGER_SIZE - $int64Length;
                $ref = $this->dataBlocks[$blockCounter]->getId();
                if ($bitLengthDiff < $bitLength) {
                    $fraction = $ref >> $bitLengthDiff; // Datablock ref bit shifted by $bitLengthDiff to get the overflow bit(s)
                    $bitmask = $this->getBitMask($bitLengthDiff);
                    $fillUp = $ref & $bitmask; // Get the bit(s) that fill(s) the gap
                    $fillUp <<= $int64Length; // Shift the bit(s) to the left most position
                    $int64 |= $fillUp; // Add the bit(s) to $int64
                }
                if ($fraction !== 0) {
                    $int64 = $fraction;
                    $offset = $this->getBitLength($fraction);
                    $fraction = 0;
                } else {
                    // Building int64 from right to left.
                    // Left shift $ref to the next zeroed position.
                    $shiftedRef = $ref << ($j * $bitLength + $offset);
                    // Write $shiftedRef into int64
                    $int64 |= $shiftedRef;
                }
            }
            $data[] = $int64;
        }
        return $data;
    }

}