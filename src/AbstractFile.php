<?php

namespace Aternos\Hawk;

use Exception;

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
     * @throws Exception
     */
    public function __construct(string $path = null)
    {
        if ($path === null) {
            return;
        }
        $this->dir = dirname($path);
        $this->fileName = basename($path);
        $this->fileStream = fopen($this->dir . "/" . $this->fileName, "r+");
        if ($this->fileStream === false) {
            throw new Exception("Error while opening file.");
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
     * @throws Exception "Nothing to unpack"
     */
    public function readStringToUInt32BigEndian(): int
    {
        $result = unpack("N", $this->read(4));
        if ($result === false) {
            throw new Exception("Nothing to unpack");
        }
        return $result[1];
    }

    /**
     * Reads byte string and converts it to Uint8
     *
     * @return int Uint8
     * @throws Exception "Error while reading"
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
    }

    public function getContent(): string
    {
        $content = fgets($this->fileStream);
        if ($content === false) {
            throw new Exception("Error while getting content");
        }
        return $content;
    }

    /**
     * @throws Exception "Error while reading"
     */
    abstract public function read(int $length): string;

    /**
     * @throws Exception "Error while writing"
     */
    abstract public function write(string $data): int;

    /**
     * @throws Exception "Error while seeking"
     */
    abstract public function seek(int $offset, int $seekType): bool;

    /**
     * @throws Exception "Error while getting pointer position."
     */
    abstract public function tell(): int;
}
