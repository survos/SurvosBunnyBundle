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
        if (!$this->storageZone) {
            $this->storageZone=$this->config['storage_zone'];
        }

    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function getApiKey(): ?string
    {
        return $this->config['api_key'];

    }

    public function getEdgeStorageApi(): ?EdgeStorageAPI
    {
        return $this->edgeStorageApi;
    }

    public function getBaseApi(?string $apiKey=null): ?BaseAPI
    {
        if (!$this->baseApi) {
            $this->baseApi = new BaseAPI(
                apiKey: $apiKey??$this->config['api_key'],
                client: $this->bunnyClient,
            );
        }

        return $this->baseApi;
    }

    public function downloadFile(string $filename, string $path, string $storageZone=null)
    {
        $ret = $this->getEdgeApi()->downloadFile(
            storageZoneName: $storageZone??$this->getStorageZone(),
            path: $path,
            fileName: $filename,
        );

        return $ret;
    }

    public function getEdgeApi(string $storageZone=null): EdgeStorageAPI
    {
        if (!$storageZone = $storageZone??$this->getStorageZone()) {
            if (count($this->config['zones']) >= 1) {
                $storageZone = array_key_first($this->config['zones']);
            }
        }
        assert($storageZone, "Missing storageZone!");

        if (!$this->edgeStorageApi) {
            $this->edgeStorageApi = new EdgeStorageAPI(
                apiKey: $readOnlyPassword??$this->config['zones'][$storageZone]['readonly_password'],
                client: $this->bunnyClient,
                region: Region::NY // from($this->config['region']) # Region::NY # $this->config['region']
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
