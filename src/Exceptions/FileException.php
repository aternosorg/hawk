<?php

declare(strict_types=1);

namespace Aternos\Hawk\Exceptions;

use Aternos\Hawk\AbstractFile;

/**
 * Thrown when a region file cannot be opened, read, written or positioned
 */
class FileException extends HawkException
{
    protected ?string $path;

    /**
     * @param string $action What failed, e.g. "read 4 bytes from"
     * @param string|null $path Path of the affected file, null for in-memory files
     */
    public function __construct(string $action, ?string $path)
    {
        $this->path = $path;
        parent::__construct("Could not " . $action . " " . static::describePath($path) . ".");
    }

    /**
     * @param AbstractFile $file
     * @return string|null Path of $file, null for in-memory files
     */
    public static function getPathOfFile(AbstractFile $file): ?string
    {
        if ($file->getFileName() === null) {
            return null;
        }
        if ($file->getDir() === null) {
            return $file->getFileName();
        }
        return $file->getDir() . "/" . $file->getFileName();
    }

    /**
     * @param string|null $path
     * @return string
     */
    protected static function describePath(?string $path): string
    {
        return $path === null ? "in-memory file" : "file \"" . $path . "\"";
    }

    /**
     * @return string|null Path of the affected file, null for in-memory files
     */
    public function getPath(): ?string
    {
        return $this->path;
    }
}
