<?php

namespace Aternos\Hawk;

use Aternos\Hawk\Enums\SeekType;
use Aternos\Nbt\IO\Reader\GZipCompressedStringReader;
use Aternos\Nbt\IO\Reader\StringReader;
use Aternos\Nbt\IO\Reader\ZLibCompressedStringReader;
use Aternos\Nbt\NbtFormat;
use Aternos\Nbt\Tag\CompoundTag;
use Aternos\Nbt\Tag\Tag;
use Exception;

abstract class Region
{
    protected string $fileName;

    protected AbstractFile $file;

    protected McCoordinates2D $coordinates;

    protected int $version;

    /**
     * @var Chunk[]
     */
    protected array $chunks = [];

    /**
     * @param McCoordinates3D $blockCoordinates
     * @return string File name calculated from $coordinates
     */
    public static function getRegionFileNameFromBlock(McCoordinates3D $blockCoordinates): string
    {
        $coords = static::getRegionCoordinatesFromBlockCoordinates($blockCoordinates);
        return "r." . $coords->x . "." . $coords->z . ".mca";
    }

    /**
     * @param AbstractFile $file
     * @return McCoordinates2D
     */
    public static function getRegionCoordinatesFromFile(AbstractFile $file): McCoordinates2D
    {
        $fileName = explode(".", $file->getFileName());
        return new McCoordinates2D($fileName[1], $fileName[2]);
    }

    /**
     * @param McCoordinates3D $coordinates
     * @return McCoordinates2D
     */
    public static function getRegionCoordinatesFromBlockCoordinates(McCoordinates3D $coordinates): McCoordinates2D
    {
        return new McCoordinates2D(floor($coordinates->x / 512), floor($coordinates->z / 512));
    }

    /**
     * @param McCoordinates2D $coordinates
     * @return McCoordinates2D
     */
    public static function getRegionCoordinatesFromChunkCoordinates(McCoordinates2D $coordinates): McCoordinates2D
    {
        return new McCoordinates2D(floor($coordinates->x / 32), floor($coordinates->z / 32));
    }

    /**
     * @param AbstractFile $file
     */
    public function __construct(AbstractFile $file)
    {
        $this->coordinates = Region::getRegionCoordinatesFromFile($file);
        $this->fileName = $file->getFileName();
        $this->file = $file;
    }

    /**
     * @codeCoverageIgnore
     * @return McCoordinates2D BlockRegion coordinates
     */
    public function getCoordinates(): McCoordinates2D
    {
        return $this->coordinates;
    }

    /**
     * @codeCoverageIgnore
     * @return AbstractFile
     */
    public function getFile(): AbstractFile
    {
        return $this->file;
    }

    /**
     * @codeCoverageIgnore
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * @param McCoordinates3D $coordinates
     * @return Chunk
     * @throws Exception
     */
    public function getChunkFromBlock(McCoordinates3D $coordinates): Chunk
    {
        return $this->getChunk(Chunk::getChunkCoordinatesFromBlock($coordinates));
    }

    /**
     * @param McCoordinates3D $coordinates
     * @return Chunk
     * @throws Exception
     */
    public function getChunkFromChunk(McCoordinates2D $coordinates): Chunk
    {
        return $this->getChunk($coordinates);
    }

    /**
     * @param McCoordinates2D $coordinates
     * @return Chunk
     * @throws Exception
     */
    public function getChunk(McCoordinates2D $coordinates): Chunk
    {
        foreach ($this->chunks as $chunk) {
            if ($chunk->getCoordinates()->equals($coordinates)) {
                return $chunk;
            }
        }
        return $this->readChunk($coordinates);
    }

    /**
     * @codeCoverageIgnore
     * @return BlockChunk[]
     */
    public function getChunks(): array
    {
        return $this->chunks;
    }

    /**
     * Calculates chunk offset in region file
     *
     * @param McCoordinates2D $coordinates
     * @return int
     */
    protected function calcOffset(McCoordinates2D $coordinates): int
    {
        return 4 * (($coordinates->x & 31) + ($coordinates->z & 31) * 32);
    }

    /**
     * @param McCoordinates2D $coordinates
     * @return Chunk
     * @throws Exception
     */
    protected function readChunk(McCoordinates2D $coordinates): Chunk
    {
        $this->file->seek($this->calcOffset($coordinates),
            SeekType::SEEK_SET
        );
        $location = $this->file->readStringToUInt32BigEndian();
        $offset = $location >> 8;
        $length = ($location & 0xFF) * 4096;
        $this->file->seek($offset * 4096, SeekType::SEEK_SET);
        $compressedDataLength = $this->file->readStringToUInt32BigEndian();
        $compressionScheme = $this->file->readStringToUInt8();
        $reader = match ($compressionScheme) {
            1 => new GZipCompressedStringReader($this->file->read($compressedDataLength - 1), NbtFormat::JAVA_EDITION),
            2 => new ZLibCompressedStringReader($this->file->read($compressedDataLength - 1), NbtFormat::JAVA_EDITION),
            3 => new StringReader($this->file->read($compressedDataLength - 1), NbtFormat::JAVA_EDITION),
            default => throw new Exception("Wrong compression scheme."),
        };
        $tag = Tag::load($reader);
        if(!($tag instanceof CompoundTag)){
            throw new Exception("Wrong tag type.");
        }
        $this->version = $tag->getInt("DataVersion")->getValue();
        return $this->addNewChunk($location, $offset, $compressedDataLength, $compressionScheme, $tag, $coordinates, $this->version);
    }

    /**
     * Replaces all sections of every chunk in this region.
     * Saves the chunks in their region file
     *
     * @codeCoverageIgnore
     * @return void
     * @throws Exception
     */
    public function save(): void
    {
        foreach ($this->getChunks() as $chunk) {
            $this->getFile()->seek(0, SeekType::SEEK_END);
            $chunk->setOffset($this->getFile()->tell() / 4096);
            $chunkData = $chunk->getChunkData();
            $this->getFile()->write($chunkData);
            $this->getFile()->seek($this->calcOffset($chunk->getCoordinates()),
                SeekType::SEEK_SET
            );
            $chunkLocation = $chunk->getLocation();
            $this->getFile()->write($chunkLocation);
        }
    }

    /**
     * @param int $location
     * @param int $offset
     * @param int $compressedDataLength
     * @param int $compressionScheme
     * @param CompoundTag $tag
     * @param McCoordinates2D $coordinates
     * @param int $version
     * @return Chunk
     */
    abstract public function addNewChunk(
        int             $location,
        int             $offset,
        int             $compressedDataLength,
        int             $compressionScheme,
        CompoundTag     $tag,
        McCoordinates2D $coordinates,
        int             $version
    ): Chunk;
}
