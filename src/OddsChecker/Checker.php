<?php
/**
 * Created by PhpStorm.
 * User: he110
 * Date: 09/08/2019
 * Time: 15:11
 */

namespace He110\OddsChecker;


use GuzzleHttp\Client;
use Psr\Cache\CacheItemPoolInterface;

class Checker
{
    /** @var string */
    private $apiKey;

    /** @var int */
    const ODDS_API_VERSION = 3;

    /** @var int */
    const ODDS_CACHE_LIFETIME_HOURS = 12;

    /** @var CacheItemPoolInterface */
    private $cachePool;

    /** @var Client */
    private $client;

    private $timezone;

    /**
     * Checker constructor.
     * @param string $apiKey
     * @param CacheItemPoolInterface $cacheItemPool
     */
    public function __construct(string $apiKey, CacheItemPoolInterface $cacheItemPool)
    {
        $this->apiKey = $apiKey;
        $this->client = new Client();
        $this->setCachePool($cacheItemPool);
        return $this;
    }

    /**
     * @param string $sport
     * @param string $region
     * @return array
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \HttpException
     * @throws \HttpRequestMethodException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getData(string $sport = "UPCOMING", string $region = "uk"): array
    {
        $requestData = [
            "sport" => $sport,
            "region" => $region,
            "mkt" => "h2h",
            "apiKey" => $this->getApiKey()
        ];

        $url = "https://api.the-odds-api.com/v"
            .static::ODDS_API_VERSION
            ."/odds/?"
            .http_build_query($requestData);

        $acceptableHour = $this->getAcceptableHour();

        $cacheKey = md5("odds_cache_".$acceptableHour."_".$url);
        $cacheItem = $this->getCachePool()->getItem($cacheKey);

        if ($cacheItem->isHit())
            return $cacheItem->get();

        try {
            $apiResponse = $this->getClient()->request("GET", $url);
        } catch (\Exception $e) {
            throw new \HttpRequestMethodException("Client got a fatal error");
        }

        if (200 !== $apiResponse->getStatusCode())
            throw new \HttpException("Response from API come with status, different from 200");
        if (!$apiResponse->getBody()->isReadable())
            throw new \HttpException("Can't read API response");
        $response = \GuzzleHttp\json_decode($apiResponse->getBody()->getContents(), true);

        if (!array_key_exists("success", $response) || !array_key_exists("data", $response))
            throw new \HttpException("Got unusual struct of response");

        if (true !== $response["success"])
            throw new \HttpException("Unknown error from API");

        $cacheItem->set($response["data"]);

        return $response["data"];
    }

    public function getAcceptableHour():int
    {
        $nowHours = (new \DateTime())->setTimezone($this->getTimezone())->format("H");
        return floor($nowHours / static::ODDS_CACHE_LIFETIME_HOURS)*static::ODDS_CACHE_LIFETIME_HOURS;
    }

    /**
     * @return \DateTimeZone
     */
    public function getTimezone(): \DateTimeZone
    {
        if (!$this->timezone)
            $this->setTimezone(new \DateTimeZone(date_default_timezone_get()));
        return $this->timezone;
    }

    /**
     * @param \DateTimeZone $timezone
     * @return Checker
     */
    public function setTimezone(\DateTimeZone $timezone): self
    {
        $this->timezone = $timezone;
        return $this;
    }



    /**
     * @return string
     */
    private function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * @return CacheItemPoolInterface
     */
    public function getCachePool(): CacheItemPoolInterface
    {
        return $this->cachePool;
    }

    /**
     * @param CacheItemPoolInterface $cachePool
     * @return Checker
     */
    public function setCachePool(CacheItemPoolInterface $cachePool): self
    {
        $this->cachePool = $cachePool;
        return $this;
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @param Client $client
     * @return Checker
     */
    public function setClient(Client $client): self
    {
        $this->client = $client;
        return $this;
    }


}