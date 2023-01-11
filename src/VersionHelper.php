<?php

namespace Aternos\Hawk;


use Aternos\Hawk\Exceptions\VersionNotSupportedException;
use Aternos\Hawk\Versions\v1139\BlockChunkV1139;
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
    private const BLOCK_SUPPORT = 2566;
    private const DATA_VERSIONS = [

        3218 => [
            "name" => "1.19.3",
            "class" => BlockChunkV3105::class,
            "level" => false,
            "entities" => false,
        ],
        3120 => [
            "name" => "1.19.2",
            "class" => BlockChunkV3105::class,
            "level" => false,
            "entities" => false,
        ],
        3117 => [
            "name" => "1.19.1",
            "class" => BlockChunkV3105::class,
            "level" => false,
            "entities" => false,
        ],
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
        2230 => [
            "name" => "1.15.2",
            "class" => BlockChunkV1139::class,
            "level" => true,
            "entities" => true,
        ],
        2227 => [
            "name" => "1.15.1",
            "class" => BlockChunkV1139::class,
            "level" => true,
            "entities" => true,
        ],
        2225 => [
            "name" => "1.15",
            "class" => BlockChunkV1139::class,
            "level" => true,
            "entities" => true,
        ],
        1976 => [
            "name" => "1.14.4",
            "class" => BlockChunkV1139::class,
            "level" => true,
            "entities" => true,
        ],
        1968 => [
            "name" => "1.14.3",
            "class" => BlockChunkV1139::class,
            "level" => true,
            "entities" => true,
        ],
        1963 => [
            "name" => "1.14.2",
            "class" => BlockChunkV1139::class,
            "level" => true,
            "entities" => true,
        ],
        1957 => [
            "name" => "1.14.1",
            "class" => BlockChunkV1139::class,
            "level" => true,
            "entities" => true,
        ],
        1952 => [
            "name" => "1.14",
            "class" => BlockChunkV1139::class,
            "level" => true,
            "entities" => true,
        ],
        1631 => [
            "name" => "1.13.2",
            "class" => BlockChunkV1139::class,
            "level" => true,
            "entities" => true,
        ],
        1628 => [
            "name" => "1.13.1",
            "class" => BlockChunkV1139::class,
            "level" => true,
            "entities" => true,
        ],
        1519 => [
            "name" => "1.13",
            "class" => BlockChunkV1139::class,
            "level" => true,
            "entities" => true,
        ],
        1343 => [
            "name" => "1.12.2",
            "class" => BlockChunkV1139::class,
            "level" => true,
            "entities" => true,
        ],
        1241 => [
            "name" => "1.12.1",
            "class" => BlockChunkV1139::class,
            "level" => true,
            "entities" => true,
        ],
        1139 => [
            "name" => "1.12",
            "class" => BlockChunkV1139::class,
            "level" => true,
            "entities" => true,
        ],
    ];

    /**
     * @param int $dataVersion
     * @return int
     * @throws Exception
     */
    protected static function getSupportedVersion(int $dataVersion): int
    {
        if (array_key_exists($dataVersion, static::DATA_VERSIONS)) {
            return $dataVersion;
        }
        $latest = max(array_keys(static::DATA_VERSIONS));
        if ($dataVersion > $latest) {
            return $latest;
        }
        throw new VersionNotSupportedException(static::DATA_VERSIONS[$dataVersion]["name"]);
    }

    /**
     * @param int $dataVersion
     * @return bool
     */
    public static function areBlocksSupported(int $dataVersion): bool
    {
        if ($dataVersion >= self::BLOCK_SUPPORT) {
            return true;
        }
        return false;
    }

    /**
     * @param int $dataVersion
     * @return string
     * @throws Exception
     */
    public static function getChunkClassFromVersion(int $dataVersion): string
    {
        $dataVersion = self::getSupportedVersion($dataVersion);
        return static::DATA_VERSIONS[$dataVersion]["class"];
    }

    /**
     * @param int $dataVersion
     * @return bool
     * @throws Exception
     */
    public static function hasLevelTag(int $dataVersion): bool
    {
        $dataVersion = self::getSupportedVersion($dataVersion);
        return static::DATA_VERSIONS[$dataVersion]["level"];
    }

    /**
     * @param int $dataVersion
     * @return bool
     * @throws Exception
     */
    public static function hasEntitiesTag(int $dataVersion): bool
    {
        $dataVersion = self::getSupportedVersion($dataVersion);
        return static::DATA_VERSIONS[$dataVersion]["entities"];
    }
}