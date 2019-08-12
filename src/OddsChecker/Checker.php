<?php
/**
 * Created by PhpStorm.
 * User: he110
 * Date: 09/08/2019
 * Time: 15:11
 */

namespace He110\OddsChecker;


use GuzzleHttp\Client;
use He110\OddsChecker\Exceptions\InvalidResponseException;
use He110\OddsChecker\Exceptions\InvalidResponseStatusException;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\ResponseInterface;

class Checker
{
    /** @var int */
    const ODDS_API_VERSION = 3;

    /** @var int */
    const ODDS_CACHE_LIFETIME_HOURS = 12;

    /** @var string */
    private $apiKey;

    /** @var CacheItemPoolInterface|null */
    private $cachePool;

    /** @var Client */
    private $client;

    /** @var \DateTimeZone */
    private $timezone;

    /**
     * Checker constructor.
     * @param string $apiKey
     * @param CacheItemPoolInterface $cacheItemPool
     */
    public function __construct(string $apiKey, CacheItemPoolInterface $cacheItemPool = null)
    {
        $this->apiKey = $apiKey;
        $this->client = new Client();
        $this->setCachePool($cacheItemPool);
        return $this;
    }

    /**
     * Gets data from an API
     *
     * @param string $sport – Sport key. By default is set to `UPCOMING`
     * @param string $region – Region key with 2 characters. By default is set to `uk`
     * @return array
     * @throws InvalidResponseException
     * @throws InvalidResponseStatusException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getData(string $sport = "UPCOMING", string $region = "uk"): array
    {
        $region = strtolower($region);
        $requestData = [
            "sport" => $sport,
            "region" => $region,
            "mkt" => "h2h",
            "apiKey" => $this->getApiKey()
        ];

        $url = "https://api.the-odds-api.com/v".static::ODDS_API_VERSION."/odds/?".http_build_query($requestData);

        $cacheItemPool = $this->getCachePool();

        $cacheItem = null;
        if ($cacheItemPool) {
            $acceptableHour = $this->getAcceptableHour();

            $cacheKey = md5("odds_cache_" . $acceptableHour . "_" . $url);
            $cacheItem = $this->getCachePool()->getItem($cacheKey);

            if ($cacheItem->isHit()) {
                return $cacheItem->get();
            }
        }

        $apiResponse = $this->getClient()->request("GET", $url);
        $response = $this->validateClientResponse($apiResponse);

        if ($cacheItemPool && !is_null($cacheItem))
            $cacheItem->set($response["data"]);

        return $response["data"];
    }

    /**
     * Filters data array with team name, limit and actual time
     *
     * @param array $data
     * @param string $team
     * @param int $limit
     * @param bool $actual
     * @return array
     */
    public function filterData(array $data, string $team, int $limit = 10, bool $actual = true): array
    {
        $data = array_filter($data, function($item) use ($team, $actual) {
             if ($actual && $item["commence_time"] < time())
                 return false;
             $teams = array_map(function($item) {
                 return strtoupper($item);
             }, $item["teams"]);
             if (!in_array(strtoupper($team), $teams))
                 return false;
             return true;
        });
        return array_slice($data, 0, $limit);
    }

    /**
     * Checks API response for all required fields and their values
     *
     * @param ResponseInterface $apiResponse
     * @return mixed
     * @throws InvalidResponseException
     * @throws InvalidResponseStatusException
     */
    private function validateClientResponse(ResponseInterface $apiResponse)
    {
        if (200 !== $apiResponse->getStatusCode())
            throw new InvalidResponseStatusException("Response from API come with status, different from 200");

        $response = \GuzzleHttp\json_decode($apiResponse->getBody()->getContents(), true);

        if (!array_key_exists("success", $response) || !array_key_exists("data", $response))
            throw new InvalidResponseException("Got unusual struct of response");

        if (true !== $response["success"])
            throw new InvalidResponseException("Unknown error from API");

        return $response;
    }

    /**
     * Allows to get nearest acceptable hour.
     * It's required to minimize API-calls amount and uses as part of cache-item key
     *
     * @return int
     */
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
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * @return CacheItemPoolInterface|null
     */
    public function getCachePool(): ?CacheItemPoolInterface
    {
        return $this->cachePool;
    }

    /**
     * @param CacheItemPoolInterface|null $cachePool
     * @return Checker
     */
    public function setCachePool(CacheItemPoolInterface $cachePool = null): self
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