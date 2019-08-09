<?php
/**
 * Created by PhpStorm.
 * User: he110
 * Date: 09/08/2019
 * Time: 15:11
 */

namespace He110\OddsChecker;


use Psr\Cache\CacheItemPoolInterface;

class Checker
{
    private $sport;

    private $region;

    private $apiKey;

    private $cachePool;
    
    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @return mixed
     */
    public function getSport(): string
    {
        return $this->sport;
    }

    /**
     * @param string $sport
     * @return Checker
     */
    public function setSport(string $sport): self
    {
        $this->sport = $sport;
    }

    /**
     * @return string
     */
    public function getRegion():string
    {
        return $this->region;
    }

    /**
     * @param string $region
     * @return Checker
     */
    public function setRegion(string $region): self
    {
        $this->region = $region;
        return $this;
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


}