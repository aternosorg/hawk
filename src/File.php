<?php

namespace Aternos\Hawk;

use Exception;

class File extends AbstractFile
{
    /**
     * @inheritDoc
     * @throws Exception "Error while reading"
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
        throw new Exception("Error while reading.");
    }

    /**
     * @inheritDoc
     * @throws Exception "Error while writing"
     */
    public function write(string $data): int
    {
        $res = fwrite($this->fileStream, $data);
        if ($res !== false) {
            return $res;
        }
        //@codeCoverageIgnoreStart
        throw new Exception("Error while writing.");
        //@codeCoverageIgnoreEnd
    }

    /**
     * @inheritDoc
     * @throws Exception "Error while seeking"
     */
    public function seek(int $offset, int $seekType): bool
    {
        $res = fseek($this->fileStream, $offset, $seekType);
        if ($res === 0) {
            return true;
        }

        throw new Exception("Error while seeking.");
    }

    /**
     * @inheritDoc
     * @throws Exception "Error while getting pointer position."
     */
    public function tell(): int
    {
        $res = ftell($this->fileStream);
        if ($res !== false) {
            return $res;
        }
        //@codeCoverageIgnoreStart
        throw new Exception("Error while getting pointer position.");
        //@codeCoverageIgnoreEnd
    }
}