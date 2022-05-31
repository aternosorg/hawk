<?php

namespace Aternos\Hawk;

use Aternos\Nbt\IO\Writer\ZLibCompressedStringWriter;
use Aternos\Nbt\NbtFormat;
use Aternos\Nbt\Tag\CompoundTag;
use Aternos\Nbt\Tag\IntTag;
use Aternos\Nbt\Tag\ListTag;
use Aternos\Nbt\Tag\Tag;
use Exception;

abstract class BlockChunk extends Chunk
{
    protected int $version;

    protected string $sectionsTagName = "Sections";

    protected string $blockEntitiesTagName = "TileEntities";

    protected bool $hasLevelTag = true;

    /**
     * @var Section[]
     */
    protected array $sections = [];

    /**
     * @var BlockEntity[]
     */
    protected array $blockEntities = [];

    /**
     * @param int $location
     * @param int $offset
     * @param int $compressedDataLength
     * @param int $compressionScheme
     * @param Tag $tag
     * @param McCoordinates2D $coordinates
     * @param int $version
     * @throws Exception
     */
    public function __construct(int $location, int $offset, int $compressedDataLength, int $compressionScheme, Tag $tag, McCoordinates2D $coordinates, int $version)
    {
        parent::__construct($location, $offset, $compressedDataLength, $compressionScheme, $tag, $coordinates, $version);
        $this->version = $version;
        $this->loadSections();
        $this->loadBlockEntities();
    }

    /**
     * @return void
     * @throws Exception
     */
    public function loadSections(): void
    {
        $sectionsTag = $this->readSectionsTag();
        if ($sectionsTag !== null) {
            foreach ($sectionsTag as $sectionTag) {
                $section = $this->newSectionFromTag($sectionTag, $this->coordinates, $this->version);
                if($section !== null){
                    $this->sections[] = $section;
                }
            }
            return;
        }
        throw new Exception("No section loaded.");
    }

    /**
     * @return void
     * @throws Exception
     */
    public function loadBlockEntities(): void
    {
        $blockEntities = $this->readBlockEntitiesTag();
        if ($blockEntities !== null) {
            foreach ($blockEntities as $blockEntity) {
                $this->blockEntities[] = new BlockEntity($blockEntity);
            }
        }
    }

    /**
     * @param McCoordinates3D $coordinates
     * @return Section|null Section at $coordinates if found, otherwise null
     * @throws Exception
     */
    public function getSection(McCoordinates3D $coordinates): ?Section
    {
        foreach ($this->sections as $section) {
            if ($section->getCoordinates()->equals(Section::getSectionCoordinates($coordinates))) {
                return $section;
            }
        }
        return null;
    }

    /**
     * @return Section[]
     */
    public function getSections(): array
    {
        return $this->sections;
    }

    /**
     * @param McCoordinates3D $coordinates
     * @return DataBlock
     * @throws Exception
     */
    public function getBlock(McCoordinates3D $coordinates): DataBlock
    {
        $section = $this->getSection($coordinates);
        if ($section === null) {
            throw new Exception("No such section.");
        }
        return $section->getBlock($coordinates);
    }

    /**
     * Searches for section, if no section is found(sections full of air do not get saved) it creates a new section
     * Replaces block at $coordinates with $blockName
     *
     * @codeCoverageIgnore
     * @param McCoordinates3D $coordinates
     * @param string $blockName
     * @return void
     * @throws Exception
     */
    public function replaceBlock(McCoordinates3D $coordinates, string $blockName = "minecraft:stone"): void
    {
        $section = $this->getSection($coordinates);
        if ($section === null) {
            $section = $this->addEmptySection($coordinates);
        }
        $section->replaceBlock($coordinates, $blockName);
        $this->deleteBlockEntity($coordinates);
    }

    /**
     * Deletes block entity
     *
     * @param McCoordinates3D $coordinates
     * @return void
     */
    public function deleteBlockEntity(McCoordinates3D $coordinates): void
    {
        foreach ($this->blockEntities as $index => $blockEntity) {
            if ($coordinates->equals($blockEntity->getCoordinates())) {
                unset($this->blockEntities[$index]);
                return;
            }
        }
    }

    /**
     * Creates empty section and adds it to $this->sections
     *
     * @param McCoordinates3D $coordinates
     * @return Section
     */
    public function addEmptySection(McCoordinates3D $coordinates): Section
    {
        $section = $this->newEmptySection(Section::getSectionCoordinates($coordinates), $this->version);
        $this->sections[] = $section;
        return $section;
    }

    /**
     * Overwrites "sections"-tag in chunk tag
     * Caches compressed data and its length
     *
     * @codeCoverageIgnore
     * @return void
     * @throws Exception
     */
    public function replaceTags(): void
    {
        $writer = (new ZLibCompressedStringWriter())->setFormat(NbtFormat::JAVA_EDITION);
        $this->setTags();
        $this->tag->write($writer);
        $this->compressedData = $writer->getStringData();
        $this->compressedDataLength = strlen($this->compressedData);
    }

    /**
     * @return BlockEntity[]
     */
    public function getBlockEntities(): array
    {
        return $this->blockEntities;
    }

    /**
     * @return ListTag|null
     */
    public function readSectionsTag(): ?ListTag
    {
        return $this->tag->getList($this->sectionsTagName, CompoundTag::TYPE);
    }

    /**
     * @return ListTag|null
     */
    public function readBlockEntitiesTag(): ?ListTag
    {
        return $this->tag->getList($this->blockEntitiesTagName, CompoundTag::TYPE);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function setTags(): void
    {
        $this->tag->set($this->sectionsTagName, $this->createSectionsTag());
        $this->tag->set($this->blockEntitiesTagName, $this->createBlockEntitiesTag());
        $this->tag->set($this->entitiesTagName, $this->createEntitiesTag());
        if($this->hasLevelTag){
            $tag = new CompoundTag();
            $tag->set("Level", $this->tag);
            $tag->set("DataVersion", (new IntTag())->setValue($this->version));
            $this->tag = $tag;
        }
    }

    /**
     * Creates "block_entities" tag
     *
     * @return ListTag
     * @throws Exception
     */
    protected function createBlockEntitiesTag(): ListTag
    {
        $blockEntities = new ListTag();
        $blockEntities->setContentTag(CompoundTag::TYPE);
        foreach ($this->blockEntities as $blockEntity) {
            $blockEntities[] = $blockEntity->createTag();
        }
        return $blockEntities;
    }
    /**
     * Creates "entities" tag
     *
     * @return ListTag
     * @throws Exception
     */
    protected function createEntitiesTag(): ListTag
    {
        $entities = new ListTag();
        $entities->setContentTag(CompoundTag::TYPE);
        foreach ($this->entities as $entity) {
            $entities[] = $entity->createTag();
        }
        return $entities;
    }

    /**
     * Creates "sections" tag
     *
     * @return ListTag
     * @throws Exception
     */
    protected function createSectionsTag(): ListTag
    {
        $sections = new ListTag();
        $sections->setContentTag(CompoundTag::TYPE);
        foreach ($this->sections as $section) {
            $sections[] = $section->createTag();
        }
        return $sections;
    }

    /**
     * @param CompoundTag $tag
     * @param McCoordinates2D $coordinates
     * @param int $version
     * @return Section|null
     */
    abstract public function newSectionFromTag(CompoundTag $tag, McCoordinates2D $coordinates, int $version): ?Section;

    /**
     * @param McCoordinates3D $coordinates
     * @param int $version
     * @return Section
     */
    abstract public function newEmptySection(McCoordinates3D $coordinates, int $version): Section;
}
