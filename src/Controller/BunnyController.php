<?php

namespace Survos\BunnyBundle\Controller;

use Survos\BunnyBundle\Service\BunnyService;
use Symfony\Bridge\Twig\Attribute\Template;
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
    #[Route('/bunny', name: 'survos_bunny_zones', methods: ['GET'])]
    #[Template('@SurvosBunny/zones.html.twig')]
    public function zones(
    ): Response|array
    {
        $baseApi = $this->bunnyService->getBaseApi();
        return ['zones' => $baseApi->listStorageZones()->getContents()];
    }

    #[Route('/bunny/{zoneName}/{id}/{path}', name: 'survos_bunny_zone', methods: ['GET'])]
    #[Template('@SurvosBunny/zone.html.twig')]
    public function zone(
        string $zoneName,
        string $id,
        ?string $path='/'
    ): Response|array
    {
        $baseApi = $this->bunnyService->getBaseApi();
//        $zone = $baseApi->getStorageZone($id)->getContents();
//        $accessKey = $zone['ReadOnlyPassword'];
//        $accessKey = null;
        $zone = null;
        $edgeStorageApi = $this->bunnyService->getEdgeApi();
        $list = $edgeStorageApi->listFiles(
            storageZoneName: $zoneName,
            path: $path
        );
        return [
            'zone' => $zone,
            'zoneName' => $zoneName,
            'path' => $path,
            'files' => $list->getContents()
        ];

        dd($list);

        $storageZoneName = 'museado';
        foreach ($baseApi->listStorageZones()->getContents() as $zone) {
            $accessKey = $zone['ReadOnlyPassword'];

// Provide the "(Read-Only) Password" available at the "FTP & API Access" section of your specific storage zone.


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
