<?php
/**
 * Created by PhpStorm.
 * User: he110
 * Date: 09/08/2019
 * Time: 15:29
 */

namespace He110\OddsChecker;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class CheckerTest extends TestCase
{
    /** @var string  */
    private $apiToken = "ff942219cbc53dce33e783316b106371";

    /** @var Checker */
    private $checker;

    public function setUp(): void
    {
        $this->checker = new Checker($this->apiToken);
    }

    public function tearDown(): void
    {
        $this->checker = null;
        unset($this->checker);
    }

    /**
     * @covers \He110\OddsChecker\Checker::getData()
     * @covers \He110\OddsChecker\Checker::setClient()
     */
    public function testGetData()
    {
        $responseMock = $this->getResponseMock();
        $responseMockBody = json_decode($responseMock->getBody()->getContents(), true);
        $responseMockData = $responseMockBody["data"];

        $clientMock = $this->getMockBuilder(Client::class)->getMock();
        $clientMock->expects($this->once())
            ->method("request")->willReturn($responseMock);

        $this->checker->setClient($clientMock);

        $result = $this->checker->getData();
        $this->assertEquals(2, count($result));
        $this->assertEquals("aussierules_afl", $result[0]["sport_key"]);
        $this->assertEquals("AFL", $result[0]["sport_nice"]);
        $this->assertEquals("Greater Western Sydney Giants", $result[0]["teams"][0]);

        $cacheItem = $this->getMockBuilder(CacheItemInterface::class)->getMock();
        $cacheItem->expects($this->any())->method("isHit")->willReturn(true);
        $cacheItem->expects($this->once())->method("get")->willReturn($responseMockData);
        $cachePool = $this->getMockBuilder(CacheItemPoolInterface::class)->getMock();
        $cachePool->expects($this->any())->method("getItem")->willReturn($cacheItem);

        $this->checker = new Checker($this->apiToken, $cachePool);

        $result = $this->checker->getData();
        $this->assertEquals(2, count($result));
        $this->assertEquals("soccer_china_superleague", $result[1]["sport_key"]);
        $this->assertEquals("Super League - China", $result[1]["sport_nice"]);
        $this->assertEquals("Dalian Yifang FC", $result[1]["teams"][0]);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|ResponseInterface
     */
    private function getResponseMock()
    {
        $data = array(
            'success' => true,
            'data' => array(
                0 => array (
                    'sport_key' => 'aussierules_afl',
                    'sport_nice' => 'AFL',
                    'teams' => array(
                        0 => 'Greater Western Sydney Giants',
                        1 => 'Hawthorn Hawks',
                    ),
                    'commence_time' => 1565344200,
                    'home_team' => 'Greater Western Sydney Giants',
                    'sites' => array (),
                    'sites_count' => 0,
                ),
                1 => array(
                    'sport_key' => 'soccer_china_superleague',
                    'sport_nice' => 'Super League - China',
                    'teams' => array(
                        0 => 'Dalian Yifang FC',
                        1 => 'Jiangsu Suning FC',
                    ),
                    'commence_time' => 1565350510,
                    'home_team' => 'Dalian Yifang FC',
                    'sites' =>array(
                        0 => array(
                            'site_key' => 'betfair',
                            'site_nice' => 'Betfair',
                            'last_update' => 1565352764,
                            'odds' => array(
                                'h2h' => array(
                                    0 => 1.05,
                                    1 => 1.01,
                                    2 => 1.01,
                                ),
                                'h2h_lay' => array (
                                    0 => 1000,
                                    1 => 20,
                                    2 => 730,
                                ),
                            ),
                        ),
                    ),
                    'sites_count' => 1,
                )
            )
        );
        $body = $this->getMockBuilder(StreamInterface::class)->getMock();
        $body->method("isReadable")->willReturn(true);
        $body->method("getContents")->willReturn(json_encode($data, JSON_UNESCAPED_UNICODE));

        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $response->method("getStatusCode")->willReturn(200);
        $response->method("getBody")->willReturn($body);

        return $response;
    }

    /**
     * @covers \He110\OddsChecker\Checker::getAcceptableHour()
     */
    public function testGetAcceptableHour()
    {
        $nowHour = (new \DateTime())->format("H");
        $acceptableHour = $this->checker->getAcceptableHour();
        $this->assertIsInt($acceptableHour);

        $comparable = $this->checker::ODDS_CACHE_LIFETIME_HOURS;

        if ($nowHour < $comparable)
            $this->assertEquals(0, $acceptableHour);
        elseif ($nowHour >= $comparable && $nowHour < $comparable*2)
            $this->assertEquals($comparable, $acceptableHour);
    }

    /**
     * @covers \He110\OddsChecker\Checker::getTimezone()
     */
    public function testGetTimezone()
    {
        $defaultTimezone = new \DateTimeZone(date_default_timezone_get());
        $this->assertEquals($defaultTimezone, $this->checker->getTimezone());
    }

    /**
     * @covers \He110\OddsChecker\Checker::setTimezone()
     */
    public function testSetTimezone()
    {
        $newTimezone = new \DateTimeZone("Europe/Moscow");
        $this->checker->setTimezone($newTimezone);
        $this->assertEquals($newTimezone, $this->checker->getTimezone());
    }

    /**
     * @covers \He110\OddsChecker\Checker::getCachePool()
     */
    public function testGetCachePool()
    {
        $this->assertNull($this->checker->getCachePool());
    }

    /**
     * @covers \He110\OddsChecker\Checker::setCachePool()
     */
    public function testSetCachePool()
    {
        $cachePool = $this->getMockBuilder(CacheItemPoolInterface::class)->getMock();
        $this->checker->setCachePool($cachePool);
        $this->assertEquals($cachePool, $this->checker->getCachePool());
    }

    /**
     * @covers \He110\OddsChecker\Checker::getClient()
     */
    public function testGetClient()
    {
        $this->assertEquals(Client::class, get_class($this->checker->getClient()));
    }


}
