<?php

declare(strict_types=1);

namespace Aternos\Hawk\Exceptions;

use Aternos\Hawk\McCoordinates2D;

/**
 * Thrown when a chunk in a region file uses an unknown compression scheme
 */
class InvalidCompressionSchemeException extends HawkException
{
    protected int $compressionScheme;

    protected string $regionFileName;

    protected McCoordinates2D $chunkCoordinates;

    /**
     * @param int $compressionScheme
     * @param string $regionFileName
     * @param McCoordinates2D $chunkCoordinates
     */
    public function __construct(int $compressionScheme, string $regionFileName, McCoordinates2D $chunkCoordinates)
    {
        $this->compressionScheme = $compressionScheme;
        $this->regionFileName = $regionFileName;
        $this->chunkCoordinates = $chunkCoordinates;
        parent::__construct("Unknown compression scheme " . $compressionScheme
            . " for chunk " . $chunkCoordinates . " in region file \"" . $regionFileName . "\".");
    }

    /**
     * @return int
     */
    public function getCompressionScheme(): int
    {
        return $this->compressionScheme;
    }

    /**
     * @return string
     */
    public function getRegionFileName(): string
    {
        return $this->regionFileName;
    }

    /**
     * @return McCoordinates2D
     */
    public function getChunkCoordinates(): McCoordinates2D
    {
        return $this->chunkCoordinates;
    }
}
