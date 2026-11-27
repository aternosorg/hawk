<?php

declare(strict_types=1);

namespace Aternos\Hawk\Exceptions;

/**
 * Thrown when entities are requested from a block region of a version that stores them in separate entities files
 */
class EntitiesNotInBlockRegionException extends HawkException
{
    /**
     * @param string $regionFileName
     * @param int $version
     */
    public function __construct(string $regionFileName, int $version)
    {
        parent::__construct("Region file \"" . $regionFileName . "\" has version " . $version
            . ", which stores entities in separate entities files.");
    }
}
