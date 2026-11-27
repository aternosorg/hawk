<?php

declare(strict_types=1);

namespace Aternos\Hawk\Exceptions;

/**
 * Thrown when NBT data does not have the tag type Hawk expects, e.g. a chunk that is not a compound tag
 */
class UnexpectedTagTypeException extends HawkException
{

}
