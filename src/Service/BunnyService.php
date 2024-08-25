<?php

namespace Survos\BunnyBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Http\Discovery\Psr18Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use ToshY\BunnyNet\BaseAPI;
use ToshY\BunnyNet\Client\BunnyClient;
use ToshY\BunnyNet\EdgeStorageAPI;
use ToshY\BunnyNet\Enum\Region;

class BunnyService
{

    public function __construct(
        private CacheInterface $cache,
        private readonly LoggerInterface $logger,
        private readonly HttpClientInterface $client,
        private ?BunnyClient $bunnyClient=null,
        private ?BaseAPI $baseApi=null,
        private int $cacheTimeout = 0,
        private array $config=[], // comes from config/packages/survos_bunny.yaml
//        private ?string $apiKey = null,
//        private ?string $readonlyPassword = null,
//        private ?string $password = null, // for writing
        private ?EdgeStorageAPI $edgeStorageApi = null,
        private ?string $storageZone = null,
    ) {
// Create a BunnyClient using any HTTP client implementing "Psr\Http\Client\ClientInterface".
        $this->bunnyClient = new BunnyClient(
            client: new \Symfony\Component\HttpClient\Psr18Client(),
        );

        $this->baseApi = new BaseAPI(
            apiKey: $this->config['api_key'],
            client: $this->bunnyClient,
        );
    }

    public function getBaseApi(): ?BaseAPI
    {
        return $this->baseApi;
    }

    public function getEdgeApi(?string $readOnlyPassword=null): EdgeStorageAPI
    {
        if (!$this->edgeStorageApi) {
            $this->edgeStorageApi = new EdgeStorageAPI(
                apiKey: $readOnlyPassword??$this->config['readonly_password'],
                client: $this->bunnyClient,
                region: $this->config['region']
            );
        }

        return $this->edgeStorageApi;
    }

    public function getStorageZone(): ?string
    {
        return $this->storageZone;
    }

    public function getBunnyClient()
    {

// Provide the API key available at the "Account Settings > API" section.
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
