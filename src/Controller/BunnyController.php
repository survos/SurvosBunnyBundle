<?php

namespace Survos\BunnyBundle\Controller;

use Survos\BunnyBundle\Service\BunnyService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use ToshY\BunnyNet\BaseAPI;
use ToshY\BunnyNet\Client\BunnyClient;
use ToshY\BunnyNet\EdgeStorageAPI;
use ToshY\BunnyNet\Enum\Region;

class BunnyController extends AbstractController
{
    public function __construct(
        private BunnyService $bunnyService
    )
    {

    }
    #[Route('/bunny', name: 'survos_bunny_list', methods: ['GET'])]
    public function list(


                          HttpClientInterface $httpClient,
    ): Response
    {

        $this->bunnyService->getBunnyClient();

//        $httpClient =  Psr18ClientDiscovery::find();
        $httpClient = new \Symfony\Component\HttpClient\Psr18Client();
// Create a BunnyClient using any HTTP client implementing "Psr\Http\Client\ClientInterface".
        $bunnyClient = new BunnyClient(
            client: $httpClient
        );

// Provide the API key available at the "Account Settings > API" section.
        $baseApi = new BaseAPI(
            apiKey: $apiKey,
            client: $bunnyClient,
        );
//        dd($baseApi->listCountries());
        $storageZoneName = 'museado';
        foreach ($baseApi->listStorageZones()->getContents() as $zone) {
            $accessKey = $zone['ReadOnlyPassword'];

// Provide the "(Read-Only) Password" available at the "FTP & API Access" section of your specific storage zone.
            $edgeStorageApi = new EdgeStorageAPI(
                apiKey: $accessKey,
                client: $bunnyClient,
                region: Region::NY
            );
            $list = $edgeStorageApi->listFiles(
                storageZoneName: $storageZoneName,
                path: '/'
            );


//            +        $zoneDto = $serializer->denormalize($zoneArray, Zone::class, context: [
//                +//            AbstractNormalizer::
//                +        ]);


//            $client = new Client($accessKey, 'museado', Region::NEW_YORK);
//            $list = $client->listFiles('/');
            foreach ($list->getContents() as $fileInfo) {
                $subList = $edgeStorageApi->listFiles(
                    $storageZoneName,
                    path: $fileInfo['ObjectName']
                );
                dd($subList);
//                dd($client->getContents('/'));

            }
        };
        dd();
    }
}
