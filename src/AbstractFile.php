<?php

declare(strict_types=1);

namespace Aternos\Hawk;

use Aternos\Hawk\Exceptions\FileException;

abstract class AbstractFile
{
    /**
     * @var false|resource
     */
    protected $fileStream;

    protected ?string $fileName = null;

    protected ?string $dir = null;

    /**
     * @param int $int Uint32 big endian
     * @return string Byte string
     */
    public static function uInt32BigEndianToString(int $int): string
    {
        return pack("N", $int);
    }

    /**
     * @codeCoverageIgnore
     * @param int $uInt8 Uint8
     * @return string Byte
     */
    public static function uInt8ToString(int $uInt8): string
    {
        return pack("C", $uInt8);
    }

    /**
     * @param ?string $path
     * @throws FileException
     */
    public function __construct(?string $path = null)
    {
        if ($path === null) {
            return;
        }
        $this->dir = dirname($path);
        $this->fileName = basename($path);
        $this->fileStream = fopen($this->dir . "/" . $this->fileName, "r+");
        if ($this->fileStream === false) {
            throw new FileException("open", $path);
        }
    }

    public function __destruct()
    {
        if (is_resource($this->fileStream)) {
            fclose($this->fileStream);
        }
    }

    /**
     * Closes file stream
     *
     * @return void
     */
    public function close(): void
    {
        if (is_resource($this->fileStream)) {
            fclose($this->fileStream);
        }
    }

    /**
     * @codeCoverageIgnore
     * @return string|null File dir
     */
    public function getDir(): ?string
    {
        return $this->dir;
    }

    /**
     * @codeCoverageIgnore
     * @return string|null File name
     */
    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    /**
     * @codeCoverageIgnore
     * @param string $fileName
     * @return void
     */
    public function setFileName(string $fileName): void
    {
        $this->fileName = $fileName;
    }

    /**
     * @codeCoverageIgnore
     * @return false|resource
     */
    public function getFileStream(): mixed
    {
        return $this->fileStream;
    }

    /**
     * Reads byte string and converts it to Uint32 big endian
     *
     * @return int Uint32 big endian
     * @throws FileException
     */
    public function readStringToUInt32BigEndian(): int
    {
        $result = unpack("N", $this->read(4));
        if ($result === false) {
            throw new FileException("read an unsigned 32-bit integer from", FileException::getPathOfFile($this));
        }
        return $result[1];
    }

    /**
     * Reads byte string and converts it to Uint8
     *
     * @return int Uint8
     * @throws FileException
     */
    public function readStringToUInt8(): int
    {
        return unpack("C", $this->read(1))[1];
    }

    /**
     * @param string $content
     * @return void
     */
    public function setContent(string $content): void
    {
        $this->fileStream = fopen("php://memory", "r+");
        fputs($this->fileStream, $content);
        fseek($this->fileStream,0,SEEK_SET);
    }

    public function getContent(): string
    {
        fseek($this->fileStream,0,SEEK_SET);
        $content = "";
        while (!feof($this->fileStream)){
            $currentContent = fgets($this->fileStream);
            if ($currentContent === false) {
                throw new FileException("read the content of", FileException::getPathOfFile($this));
            }
            $content .= $currentContent;
        }
        return $content;
    }

    /**
     * @throws FileException
     */
    abstract public function read(int $length): string;

    /**
     * @throws FileException
     */
    abstract public function write(string $data): int;

    /**
     * @throws FileException
     */
    abstract public function seek(int $offset, int $seekType): bool;

    /**
     * @throws FileException
     */
    abstract public function tell(): int;
}
