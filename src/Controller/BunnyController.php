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
        private BunnyService $bunnyService,
        private $simpleDatatablesInstalled = false
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

    #[Route('/{zoneName}}/{path}/{fileName}', name: 'survos_bunny_download', methods: ['GET'], requirements: ['path'=> ".+"])]
    #[Template('@SurvosBunny/zone.html.twig')]
    public function download(string $zoneName, string $path, string $fileName): Response
    {
        dd(get_defined_vars());
        $response = $this->bunnyService->downloadFile($fileName,$path,$zoneName);
        dd($response);
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
        $edgeStorageApi = $this->bunnyService->getEdgeApi($zoneName);
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
