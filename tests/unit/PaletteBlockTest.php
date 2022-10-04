<?php

namespace Aternos\Hawk\Tests\Unit;

use Aternos\Hawk\PaletteBlock;
use Aternos\Hawk\Property;
use Aternos\Nbt\Tag\StringTag;
use Exception;
use PHPUnit\Framework\TestCase;

class PaletteBlockTest extends HawkTestCase
{

    /**
     * @return array[]
     */
    public function provideNew():array
    {
        return [
          "default" => ["minecraft:air"],
          "block name but no properties" => ["minecraft:stone"],
          "no block name but properties" => ["minecraft:air", ["facing", (new StringTag())->setValue("west")]],
          "block name and property" => ["minecraft:furnace", ["facing", (new StringTag())->setValue("west")]],
        ];
    }

    public function providePaletteBlocks(): array
    {
        return [
            "air" => [PaletteBlock::new()],
            "stone" => [PaletteBlock::new("minecraft:stone")],
            "dirt" => [PaletteBlock::new("minecraft:dirt")],
            "water" => [PaletteBlock::new("minecraft:water")],
            "bedrock" => [PaletteBlock::new("minecraft:bedrock")],
            "furnace" => [PaletteBlock::new("minecraft:furnace", [new Property("facing", "west")])],
        ];
    }

    /**
     * @dataProvider provideNew
     * @param string $blockName
     * @param array $properties
     * @return void
     */
    public function testNew(string $blockName, array $properties = []): void
    {
        $paletteBlock = PaletteBlock::new($blockName, $properties);
        $this->assertInstanceOf(PaletteBlock::class, $paletteBlock);
    }

    /**
     * @dataProvider providePaletteBlocks
     * @param PaletteBlock $paletteBlock
     * @return void
     * @throws Exception
     */
    public function testCreateTag(PaletteBlock $paletteBlock): void
    {
        $this->assertEquals($paletteBlock->getName(), $paletteBlock->createTag()->getString("Name")->getValue());
        $properties = $paletteBlock->getProperties();
        $tag = $paletteBlock->createTag()->getCompound("Properties");
        if($properties === null || $tag === null){
            return;
        }
        $this->assertSameSize($properties, $tag);
        foreach ($properties as $property) {
            $this->assertTrue(isset($tag[$property->getName()]));
            $this->assertEquals($property->getValue(), $tag->getString($property->getName())->getValue());
        }
    }


}
