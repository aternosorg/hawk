<?php

namespace Aternos\Hawk;

use Aternos\Nbt\Tag\StringTag;

class Property
{
    protected string $name;

    protected string $value;

    public function __construct(string $name, string $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    public function getTag(): StringTag
    {
        return (new StringTag())->setName($this->getName())->setValue($this->getValue());
    }

    public function __toString(): string
    {
        return "$this->name: $this->value";
    }
}