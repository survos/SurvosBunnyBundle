<?php

namespace Survos\BunnyBundle\Service;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use ToshY\BunnyNet\BaseAPI;
use ToshY\BunnyNet\Client\BunnyClient;
use ToshY\BunnyNet\EdgeStorageAPI;
use ToshY\BunnyNet\Enum\Region;
use ToshY\BunnyNet\Model\Client\Interface\BunnyClientResponseInterface;

class BunnyService
{
    public function __construct(
        private CacheInterface $cache,
        private readonly HttpClientInterface $client,
        private ?BunnyClient $bunnyClient = null,
        private ?BaseAPI $baseApi = null,
        private int $cacheTimeout = 0,
        private array $config = [], // comes from config/packages/survos_bunny.yaml
        //        private ?string $apiKey = null,
        //        private ?string $readonlyPassword = null,
        //        private ?string $password = null, // for writing
        private ?EdgeStorageAPI $edgeStorageApi = null,
        private ?string $storageZone = null,
        private array $zones = []
    ) {
// Create a BunnyClient using any HTTP client implementing "Psr\Http\Client\ClientInterface".
        $this->bunnyClient = new BunnyClient(
            client: new \Symfony\Component\HttpClient\Psr18Client(
                client: $this->client
            ),
        );
        if (!$this->storageZone) {
            $this->storageZone = $this->config['storage_zone'];
        }
        foreach ($this->config['zones'] as $zoneData) {
            if (!array_key_exists('name', $zoneData)) {
                throw new \LogicException($this->storageZone . " is not defined in config/packages/survos_bunny.yaml");
            }
            $this->zones[$zoneData['name']] = $zoneData;
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

    public function getBaseApi(?string $apiKey = null): ?BaseAPI
    {
        if (!$this->baseApi) {
            $apiKey ??= $this->config['api_key'];
            if (!$apiKey) {
                throw new \LogicException('BunnyService requires a base api key');
            }
            $this->baseApi = new BaseAPI(
                apiKey: $apiKey,
                client: $this->bunnyClient,
            );
        }

        return $this->baseApi;
    }

    public function downloadFile(string $filename, string $path, ?string $storageZone = null): BunnyClientResponseInterface
    {
        $ret = $this->getEdgeApi()->downloadFile(
            storageZoneName: $storageZone ?? $this->getStorageZone(),
            path: $path,
            fileName: $filename,
        );

        return $ret;
    }

    public function uploadFile(
        string $fileName, // the filename on bunny
        mixed $body, // content to write
        ?string $storageZoneName = null,
        string $path = '',
        array $headers = [],
    ): BunnyClientResponseInterface {

        $ret = $this->getEdgeApi(writeAccess: true)->uploadFile(
            $storageZoneName,
            $fileName,
            $body,
            $path,
            $headers,
        );
        return $ret;
    }

    public function getEdgeApi(string|null $storageZone = null, bool $writeAccess = false): EdgeStorageAPI
    {
        if (!$storageZone = $storageZone ?? $this->getStorageZone()) {
            if (!$storageZone = $this->config['storage_zone']) {
                if (count($this->config['zones']) >= 1) {
                    $storageZone = $this->config['zones'][0]['name'];
                }
            }
        }
        assert($storageZone, "Missing storageZone!");

        if (!$this->edgeStorageApi) {
            $password = $this->zones[$storageZone][$passwordType = ($writeAccess ? 'password' : 'readonly_password')]??null;
            if (!$password) {
                throw new \LogicException("please configure zone.$storageZone.$passwordType in survos_bunny.yaml" );
            }
            $this->edgeStorageApi = new EdgeStorageAPI(
                apiKey: $password,
                client: $this->bunnyClient,
                region: Region::from($this->zones[$storageZone]['region'])
            );
        }

        return $this->edgeStorageApi;
    }

    public function getStorageZone(): ?string
    {
        return $this->storageZone ?? $this->config['storage_zone'] ?? null;
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

    public function getZones(): array
    {
        return $this->config['zones'];
    }
}
