<?php

namespace Aternos\Hawk\Tests\Unit;

use Aternos\Hawk\DataBlock;
use Aternos\Hawk\Palette;
use Aternos\Hawk\PaletteBlock;
use Aternos\Hawk\Section;
use Aternos\Hawk\Versions\v1519\DataV1519;
use Aternos\Nbt\Tag\LongArrayTag;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DataV1519Test extends TestCase
{
    /**
     * Builds a palette with $length blocks named "test:0", "test:1", ...
     *
     * @param int $length
     * @return Palette
     */
    protected function createPalette(int $length): Palette
    {
        $paletteBlocks = [];
        for ($id = 0; $id < $length; $id++) {
            $paletteBlocks[] = PaletteBlock::new("test:" . $id);
        }
        return Palette::new($paletteBlocks);
    }

    /**
     * Packs palette indices the way Minecraft did before 1.16:
     * one continuous bit stream, so an index can continue in the next long.
     * Sets every bit on its own to stay independent of the implementation under test.
     *
     * @param int[] $ids
     * @param int $bitLength
     * @return LongArrayTag
     */
    protected function packSpanning(array $ids, int $bitLength): LongArrayTag
    {
        $longCount = (int)ceil(count($ids) * $bitLength / 64);
        $longs = array_fill(0, $longCount, 0);
        foreach ($ids as $index => $id) {
            for ($bit = 0; $bit < $bitLength; $bit++) {
                if (($id >> $bit) & 1) {
                    $streamPosition = $index * $bitLength + $bit;
                    $longs[intdiv($streamPosition, 64)] |= 1 << ($streamPosition % 64);
                }
            }
        }

        $tag = new LongArrayTag();
        foreach ($longs as $long) {
            $tag[] = $long;
        }
        return $tag;
    }

    /**
     * @return array[]
     */
    public static function provideIds(): array
    {
        $everyId = [];
        for ($index = 0; $index < Section::BLOCKS_PER_SECTION; $index++) {
            $everyId[] = $index % 32;
        }

        // Mostly air, so most longs have leading zero bits without being split.
        // Index 12 with 5 bits per block uses bits 60 to 64, so it spans two longs.
        $mostlyAir = array_fill(0, Section::BLOCKS_PER_SECTION, 0);
        $mostlyAir[1] = 3;
        $mostlyAir[12] = 31;
        $mostlyAir[100] = 17;

        // 31 fills the highest bit of the first long, which makes the long negative.
        $negativeLong = array_fill(0, Section::BLOCKS_PER_SECTION, 0);
        $negativeLong[12] = 31;
        $negativeLong[13] = 1;

        // Index 25 puts bit 2 of its id into the highest bit of the second long, which makes it negative.
        // Index 13 then has to be read from that negative long, starting at bit 1.
        $readFromNegativeLong = array_fill(0, Section::BLOCKS_PER_SECTION, 0);
        $readFromNegativeLong[13] = 2;
        $readFromNegativeLong[25] = 4;

        return [
            "every id, 5 bits" => [$everyId, 32, 5],
            "mostly air, 5 bits" => [$mostlyAir, 32, 5],
            "negative long, 5 bits" => [$negativeLong, 32, 5],
            "read from negative long, 5 bits" => [$readFromNegativeLong, 32, 5],
            "every id, 4 bits" => [array_map(fn(int $id) => $id % 16, $everyId), 16, 4],
        ];
    }

    /**
     * @param int[] $ids
     * @param int $paletteLength
     * @param int $bitLength bits per block that Minecraft uses for this palette length
     * @return void
     */
    #[DataProvider('provideIds')]
    public function testReadsIndicesSpanningTwoLongs(array $ids, int $paletteLength, int $bitLength): void
    {
        $palette = $this->createPalette($paletteLength);
        $tag = $this->packSpanning($ids, $bitLength);

        $data = DataV1519::newFromTag($tag, $palette, 1519, 0);

        $readIds = array_map(fn($dataBlock) => $dataBlock->getId(), $data->getDataBlocks());
        $this->assertSame($ids, $readIds);
        $this->assertSame("test:" . $ids[12], $data->getDataBlock(12)->getPaletteBlock()->getName());
    }

    /**
     * @param int[] $ids
     * @param Palette $palette
     * @return DataV1519
     */
    protected function createData(array $ids, Palette $palette): DataV1519
    {
        $paletteBlocks = $palette->getPaletteBlocks();
        $dataBlocks = array_map(fn(int $id) => new DataBlock($id, $paletteBlocks[$id]), $ids);
        return DataV1519::new($dataBlocks);
    }

    /**
     * @param LongArrayTag $tag
     * @return int[]
     */
    protected function getLongs(LongArrayTag $tag): array
    {
        $longs = [];
        foreach ($tag as $long) {
            $longs[] = $long;
        }
        return $longs;
    }

    /**
     * @param int[] $ids
     * @param int $paletteLength
     * @param int $bitLength bits per block that Minecraft uses for this palette length
     * @return void
     */
    #[DataProvider('provideIds')]
    public function testWritesIndicesSpanningTwoLongs(array $ids, int $paletteLength, int $bitLength): void
    {
        $data = $this->createData($ids, $this->createPalette($paletteLength));

        $written = $data->writeInts($bitLength, 0);

        $this->assertSame($this->getLongs($this->packSpanning($ids, $bitLength)), $this->getLongs($written));
    }

    /**
     * @return array[]
     */
    public static function provideBitLengths(): array
    {
        return [
            "4 bits" => [16, 4],
            "5 bits" => [32, 5],
            "7 bits" => [128, 7],
            "13 bits" => [8192, 13],
        ];
    }

    /**
     * @param int $paletteLength
     * @param int $bitLength bits per block that Minecraft uses for this palette length
     * @return void
     */
    #[DataProvider('provideBitLengths')]
    public function testReadsWhatItWrote(int $paletteLength, int $bitLength): void
    {
        $palette = $this->createPalette($paletteLength);
        $ids = [];
        for ($index = 0; $index < Section::BLOCKS_PER_SECTION; $index++) {
            // Spreads the ids over the whole palette, including the highest one
            $ids[] = ($index * 7919 + $paletteLength - 1) % $paletteLength;
        }

        $written = $this->createData($ids, $palette)->createTag($paletteLength, 0);
        $read = DataV1519::newFromTag($written, $palette, 1519, 0);

        $this->assertCount((int)ceil(Section::BLOCKS_PER_SECTION * $bitLength / 64), $this->getLongs($written));
        $this->assertSame($ids, array_map(fn($dataBlock) => $dataBlock->getId(), $read->getDataBlocks()));
    }
}
