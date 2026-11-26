<?php

namespace Aternos\Hawk\Tests\Unit;

use Aternos\Hawk\Versions\v1139\BlockChunkV1139;
use Aternos\Hawk\Versions\v3105\BlockChunkV3105;
use Aternos\Hawk\Versions\v5023\BlockChunkV5023;
use Aternos\Hawk\VersionHelper;
use Exception;
use PHPUnit\Framework\Attributes\TestWith;

class VersionHelperTest extends HawkTestCase
{
    /**
     * @param int $dataVersion
     * @param string $expectedClass
     * @return void
     * @throws Exception
     */
    #[TestWith([1000, BlockChunkV1139::class], "older than 1.12")]
    #[TestWith([3105, BlockChunkV3105::class], "1.19")]
    #[TestWith([4189, BlockChunkV3105::class], "1.21.4, not listed")]
    #[TestWith([4903, BlockChunkV3105::class], "26.2, not listed")]
    #[TestWith([5023, BlockChunkV5023::class], "26.3")]
    #[TestWith([6000, BlockChunkV5023::class], "newer than 26.3")]
    public function testGetChunkClassFromVersion(int $dataVersion, string $expectedClass): void
    {
        $this->assertEquals($expectedClass, VersionHelper::getChunkClassFromVersion($dataVersion));
    }
}
