<?php

declare(strict_types=1);

namespace Aternos\Hawk;

use Aternos\Hawk\Exceptions\FileException;

class File extends AbstractFile
{
    /**
     * @inheritDoc
     * @throws FileException
     */
    public function read(int $length): string
    {
        if ($length === 0) {
            return "";
        }

        $res = fread($this->fileStream, $length);
        if ($res !== false) {
            return $res;
        }
        throw new FileException("read " . $length . " bytes from", FileException::getPathOfFile($this));
    }

    /**
     * @inheritDoc
     * @throws FileException
     */
    public function write(string $data): int
    {
        $res = fwrite($this->fileStream, $data);
        if ($res !== false) {
            return $res;
        }
        //@codeCoverageIgnoreStart
        throw new FileException("write " . strlen($data) . " bytes to", FileException::getPathOfFile($this));
        //@codeCoverageIgnoreEnd
    }

    /**
     * @inheritDoc
     * @throws FileException
     */
    public function seek(int $offset, int $seekType): bool
    {
        $res = fseek($this->fileStream, $offset, $seekType);
        if ($res === 0) {
            return true;
        }

        throw new FileException("seek to offset " . $offset . " in", FileException::getPathOfFile($this));
    }

    /**
     * @inheritDoc
     * @throws FileException
     */
    public function tell(): int
    {
        $res = ftell($this->fileStream);
        if ($res !== false) {
            return $res;
        }
        //@codeCoverageIgnoreStart
        throw new FileException("get the pointer position in", FileException::getPathOfFile($this));
        //@codeCoverageIgnoreEnd
    }
}