<?php

namespace Aternos\Hawk\Tests\Unit;

use Aternos\Hawk\Property;
use Aternos\Hawk\Versions\v5023\PaletteBlockV5023;
use Aternos\Nbt\Tag\CompoundTag;
use Aternos\Nbt\Tag\StringTag;
use Exception;

class PaletteBlockV5023Test extends HawkTestCase
{
    /**
     * @return void
     * @throws Exception
     */
    public function testNewFromTagReadsLowercaseKeys(): void
    {
        $properties = new CompoundTag();
        $properties->set("axis", (new StringTag())->setValue("y"));
        $tag = new CompoundTag();
        $tag->set("id", (new StringTag())->setValue("minecraft:deepslate"));
        $tag->set("properties", $properties);

        $paletteBlock = PaletteBlockV5023::newFromTag($tag);

        $this->assertEquals("minecraft:deepslate", $paletteBlock->getName());
        $this->assertEquals([new Property("axis", "y")], $paletteBlock->getProperties());
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testCreateTagWritesLowercaseKeys(): void
    {
        $paletteBlock = PaletteBlockV5023::new("minecraft:furnace", [new Property("facing", "west")]);

        $tag = $paletteBlock->createTag();

        $this->assertNull($tag->getString("Name"));
        $this->assertNull($tag->getCompound("Properties"));
        $this->assertEquals("minecraft:furnace", $tag->getString("id")->getValue());
        $this->assertEquals("west", $tag->getCompound("properties")->getString("facing")->getValue());
    }
}
