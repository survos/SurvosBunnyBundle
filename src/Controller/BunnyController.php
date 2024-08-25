<?php

namespace Survos\BunnyBundle\Controller;

use Survos\BunnyBundle\Service\BunnyService;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BunnyController extends AbstractController
{
    public function __construct(
        private BunnyService $bunnyService
    )
    {

    }
    #[Route('/zones', name: 'survos_bunny_zones', methods: ['GET'])]
    #[Template('@SurvosBunny/zones.html.twig')]
    public function zones(
    ): Response|array
    {
        $baseApi = $this->bunnyService->getBaseApi();
        return ['zones' => $baseApi->listStorageZones()->getContents()];
    }

    #[Route('/{zoneName}/{id}/{path}', name: 'survos_bunny_zone', methods: ['GET'])]
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
    }
}
