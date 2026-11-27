<?php

declare(strict_types=1);

namespace Aternos\Hawk\Exceptions;

use Aternos\Hawk\Chunk;

/**
 * Thrown when a region holds a chunk of another type than the operation needs
 */
class UnexpectedChunkTypeException extends HawkException
{
    /**
     * @param Chunk $chunk
     * @param string $expectedClass
     * @param string $regionFileName
     */
    public function __construct(Chunk $chunk, string $expectedClass, string $regionFileName)
    {
        parent::__construct("Expected " . $expectedClass . " but got " . get_class($chunk)
            . " for chunk " . $chunk->getCoordinates() . " in region file \"" . $regionFileName . "\".");
    }
}
