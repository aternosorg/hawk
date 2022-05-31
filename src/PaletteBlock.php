<?php

namespace Aternos\Hawk;

use Aternos\Nbt\Tag\CompoundTag;
use Aternos\Nbt\Tag\StringTag;
use Exception;

class PaletteBlock
{
    protected string $name;

    /**
     * @var Property[]
     */
    public ?array $properties = [];

    /**
     * Overloaded
     */
    private function __construct()
    {
    }

    /**
     * Constructor
     *
     * @param string $blockName
     * @param Property[] $properties
     * @return PaletteBlock
     */
    public static function new(string $blockName = "minecraft:air", array $properties = []): PaletteBlock
    {
        $paletteBlock = new static();
        $paletteBlock->name = $blockName;
        if (!empty($properties)) {
            $paletteBlock->properties = $properties;
        }
        return $paletteBlock;
    }

    /**
     * Constructor
     *
     * @param CompoundTag $tag
     * @return PaletteBlock
     */
    public static function newFromTag(CompoundTag $tag): PaletteBlock
    {
        $paletteBlock = new static();
        $paletteBlock->name = $tag->getString("Name")->getValue();
        $compound = $tag->getCompound("Properties");
        if ($compound !== null) {
            foreach ($compound as $propertiesTag) {
                if ($propertiesTag instanceof StringTag){
                    $paletteBlock->properties[] = new Property($propertiesTag->getName(), $propertiesTag->getValue());
                }
            }
        }
        return $paletteBlock;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array|null
     */
    public function getProperties(): ?array
    {
        return $this->properties;
    }

    /**
     * Creates "A block" tag
     *
     * @return CompoundTag
     * @throws Exception
     */
    public function createTag(): CompoundTag
    {
        $block = new CompoundTag();
        $block->set("Name", (new StringTag())->setValue($this->name));
        $properties = new CompoundTag();
        if (empty($this->properties)) {
            return $block;
        }
        foreach ($this->properties as $property) {
            $stringTag = new StringTag();
            $stringTag->setName($property->getName());
            $stringTag->setValue($property->getValue());
            $properties->set($property->getName(),$stringTag);
        }
        $block->set("Properties", $properties);
        return $block;
    }

    /**
     * ToString override
     *
     * @codeCoverageIgnore
     * @return string
     */
    public function __toString(): string
    {
        $properties = (empty($this->properties)? "" :"\nProperties:" );
        $string = "\nBlock:\n\t$this->name" . $properties . "\n";
        foreach ($this->properties as $property){
            $string .= "\t" . strval($property) . "\n";
        }
        return $string;
    }
}