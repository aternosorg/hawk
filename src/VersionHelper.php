<?php

namespace Aternos\Hawk;


use Aternos\Hawk\Versions\v2566\BlockChunkV2566;
use Aternos\Hawk\Versions\v2567\BlockChunkV2567;
use Aternos\Hawk\Versions\v2578\BlockChunkV2578;
use Aternos\Hawk\Versions\v2580\BlockChunkV2580;
use Aternos\Hawk\Versions\v2584\BlockChunkV2584;
use Aternos\Hawk\Versions\v2586\BlockChunkV2586;
use Aternos\Hawk\Versions\v2724\BlockChunkV2724;
use Aternos\Hawk\Versions\v2730\BlockChunkV2730;
use Aternos\Hawk\Versions\v2860\BlockChunkV2860;
use Aternos\Hawk\Versions\v2865\BlockChunkV2865;
use Aternos\Hawk\Versions\v2975\BlockChunkV2975;
use Aternos\Hawk\Versions\v3105\BlockChunkV3105;
use Exception;

class VersionHelper
{
    private const VERSIONS = [

        3105 => [
            "name" => "1.19",
            "class" => BlockChunkV3105::class,
            "level" => false,
            "entities" => false,
        ],
        2975 => [
            "name" => "1.18.2",
            "class" => BlockChunkV2975::class,
            "level" => false,
            "entities" => false,
        ],
        2865 => [
            "name" => "1.18.1",
            "class" => BlockChunkV2865::class,
            "level" => false,
            "entities" => false,
        ],
        2860 => [
            "name" => "1.18",
            "class" => BlockChunkV2860::class,
            "level" => false,
            "entities" => false,
        ],
        2730 => [
            "name" => "1.17.1",
            "class" => BlockChunkV2730::class,
            "level" => true,
            "entities" => false,
        ],
        2724 => [
            "name" => "1.17",
            "class" => BlockChunkV2724::class,
            "level" => true,
            "entities" => false,
        ],
        2586 => [
            "name" => "1.16.5",
            "class" => BlockChunkV2586::class,
            "level" => true,
            "entities" => true,
        ],
        2584 => [
            "name" => "1.16.4",
            "class" => BlockChunkV2584::class,
            "level" => true,
            "entities" => true,
        ],
        2580 => [
            "name" => "1.16.3",
            "class" => BlockChunkV2580::class,
            "level" => true,
            "entities" => true,
        ],
        2578 => [
            "name" => "1.16.2",
            "class" => BlockChunkV2578::class,
            "level" => true,
            "entities" => true,
        ],
        2567 => [
            "name" => "1.16.1",
            "class" => BlockChunkV2567::class,
            "level" => true,
            "entities" => true,
        ],
        2566 => [
            "name" => "1.16",
            "class" => BlockChunkV2566::class,
            "level" => true,
            "entities" => true,
        ],

        /* Unsupported
        1343 => [
            "name" => "1.12.2",
            "class" => BlockChunkV1343::class,
            "level" => true,
            "entities" => true,
        ],
        1139 => [
            "name" => "1.12",
            "class" => BlockChunkV1139::class,
            "level" => true,
            "entities" => true,
        ],*/
    ];

    /**
     * @param int $version
     * @return void
     * @throws Exception
     */
    protected static function versionSupported(int $version): void
    {
        if (!array_key_exists($version, self::VERSIONS)) {
            throw new Exception("Version " . $version . " is not supported.");
        }
    }

    /**
     * @param int $version
     * @return string
     * @throws Exception
     */
    public static function getChunkClassFromVersion(int $version): string
    {
        self::versionSupported($version);
        return self::VERSIONS[$version]["class"];
    }

    /**
     * @param int $version
     * @return bool
     * @throws Exception
     */
    public static function hasLevelTag(int $version): bool
    {
        self::versionSupported($version);
        return self::VERSIONS[$version]["level"];
    }

    /**
     * @param int $version
     * @return bool
     * @throws Exception
     */
    public static function hasEntitiesTag(int $version): bool
    {
        self::versionSupported($version);
        return self::VERSIONS[$version]["entities"];
    }
}