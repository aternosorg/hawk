<?php

namespace Aternos\Hawk\Tests\Unit;

use Aternos\Hawk\Versions\v1139\BlockChunkV1139;
use Aternos\Hawk\Versions\v1519\BlockChunkV1519;
use Aternos\Hawk\Versions\v2566\BlockChunkV2566;
use Aternos\Hawk\Versions\v3105\BlockChunkV3105;
use Aternos\Hawk\Versions\v5023\BlockChunkV5023;
use Aternos\Hawk\VersionHelper;
use Exception;

class VersionHelperTest extends HawkTestCase
{
    /**
     * @return array[]
     */
    public function provideChunkClasses(): array
    {
        return [
            "older than 1.12" => [1000, BlockChunkV1139::class],
            "1.12.2" => [1343, BlockChunkV1139::class],
            "1.13" => [1519, BlockChunkV1519::class],
            "1.15.2" => [2230, BlockChunkV1519::class],
            "1.16" => [2566, BlockChunkV2566::class],
            "1.19" => [3105, BlockChunkV3105::class],
            "1.21.4, not listed" => [4189, BlockChunkV3105::class],
            "26.2, not listed" => [4903, BlockChunkV3105::class],
            "26.3" => [5023, BlockChunkV5023::class],
            "newer than 26.3" => [6000, BlockChunkV5023::class],
        ];
    }

    /**
     * @dataProvider provideChunkClasses
     * @param int $dataVersion
     * @param string $expectedClass
     * @return void
     * @throws Exception
     */
    public function testGetChunkClassFromVersion(int $dataVersion, string $expectedClass): void
    {
        $this->assertEquals($expectedClass, VersionHelper::getChunkClassFromVersion($dataVersion));
    }
}
