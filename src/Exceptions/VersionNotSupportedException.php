<?php

namespace Aternos\Hawk\Exceptions;

class VersionNotSupportedException extends \Exception
{
    protected string $version;

    /**
     * @param string $version
     */
    public function __construct(string $version)
    {
        $this->version = $version;
        parent::__construct("Version " . $version . " is not supported.");
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }
}