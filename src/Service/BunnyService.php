<?php

namespace Survos\BunnyBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use ToshY\BunnyNet\BaseAPI;
use ToshY\BunnyNet\Client\BunnyClient;

class BunnyService
{
    private BunnyClient $bunnyClient;

    public function __construct(
        private CacheInterface $cache,
        private readonly LoggerInterface $logger,
        private readonly HttpClientInterface $client,
        private readonly int $searchLimit,
        private ?string $apiKey = null,
        private ?string $storageZone = null,
        private int $cacheTimeout = 0,
    ) {
// Create a BunnyClient using any HTTP client implementing "Psr\Http\Client\ClientInterface".
        $this->bunnyClient = new BunnyClient(
            client: new \Symfony\Component\HttpClient\Psr18Client(),
        );
    }

    public function getBunnyClient()
    {

// Provide the API key available at the "Account Settings > API" section.
        $baseApi = new BaseAPI(
            apiKey: $this->apiKey,
            client: $this->bunnyClient,
        );
        return $this->bunnyClient;
    }
    public function getCacheTimeout(): int
    {
        return $this->cacheTimeout;
    }

    public function setCacheTimeout(int $cacheTimeout): BunnyService
    {
        $this->cacheTimeout = $cacheTimeout;
        return $this;
    }

    public function getCache(): CacheInterface
    {
        return $this->cache;
    }

    public function setCache(CacheInterface $cache): BunnyService
    {
        $this->cache = $cache;
        return $this;
    }


}
