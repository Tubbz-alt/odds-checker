<?php
/**
 * Created by PhpStorm.
 * User: he110
 * Date: 09/08/2019
 * Time: 15:29
 */

namespace He110\OddsChecker;

use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;

class CheckerTest extends TestCase
{

    private $apiToken = "ff942219cbc53dce33e783316b106371";

    /**
     * @covers \He110\OddsChecker\Checker::getData()
     */
    public function testGetData()
    {
        $cacheItemPool = $this->getCacheItemPoolMock();
        $checker = new Checker($this->apiToken, $cacheItemPool);
        $checker->getData();
    }

    /**
     * @covers \He110\OddsChecker\Checker::getAcceptableHour()
     */
    public function testGetAcceptableHour()
    {
        $cacheItemPool = $this->getCacheItemPoolMock();
        $checker = new Checker($this->apiToken, $cacheItemPool);
        $nowHour = (new \DateTime())->format("H");
        $acceptableHour = $checker->getAcceptableHour();
        $this->assertIsInt($acceptableHour);

        $comparable = $checker::ODDS_CACHE_LIFETIME_HOURS;

        if ($nowHour < $comparable)
            $this->assertEquals(0, $acceptableHour);
        elseif ($nowHour >= $comparable && $nowHour < $comparable*2)
            $this->assertEquals($comparable, $acceptableHour);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|CacheItemPoolInterface
     */
    private function getCacheItemPoolMock()
    {
        return $this->getMockBuilder(CacheItemPoolInterface::class)->getMock();
    }
}
