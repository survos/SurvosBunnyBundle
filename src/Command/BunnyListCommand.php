<?php

namespace Survos\BunnyBundle\Command;

use Psr\Log\LoggerInterface;
use Survos\BunnyBundle\Service\BunnyService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Zenstruck\Console\Attribute\Argument;
use Zenstruck\Console\Attribute\Option;
use Zenstruck\Console\InvokableServiceCommand;
use Zenstruck\Console\IO;
use Zenstruck\Console\RunsCommands;
use Zenstruck\Console\RunsProcesses;

#[AsCommand('bunny:list', 'list bunny files')]
final class BunnyListCommand extends InvokableServiceCommand
{
    use RunsCommands;
    use RunsProcesses;

    public function __construct(
        private LoggerInterface       $logger,
        private ParameterBagInterface $bag,
        private readonly BunnyService $bunnyService,
    )
    {
        parent::__construct();
    }

    public function __invoke(
        IO                                                                                          $io,
        #[Argument(description: 'zone name')] ?string        $zoneName=null,
        #[Argument(description: 'path name within zone')] string        $path='',

    ): int
    {
        // if no zone, we could prompt
        $baseApi = $this->bunnyService->getBaseApi();
        if (!$zoneName) {
            $zones = $baseApi->listStorageZones()->getContents();
            $table = new Table($io);
            $table->setHeaderTitle($zoneName . "/" . $path);
            $headers = ['Name', 'StorageUsed','FilesStored','Id'];
            $table->setHeaders($headers);
            foreach ($zones as $zone) {
                $row = [];
                foreach ($headers as $header) {
                    $row[$header] = $zone[$header];
                }
                $table->addRow($row);
            }
            $table->render();
            return self::SUCCESS;
        }

        $edgeStorageApi = $this->bunnyService->getEdgeApi();
        $list = $edgeStorageApi->listFiles(
            storageZoneName: $zoneName,
            path: $path
        );

//        $zone = $baseApi->getStorageZone($id)->getContents();
//        $accessKey = $zone['ReadOnlyPassword'];
        $edgeStorageApi = $this->bunnyService->getEdgeApi();
        $list = $edgeStorageApi->listFiles(
            storageZoneName: $zoneName,
            path: $path
        )->getContents();

        $table = new Table($io);
        $table->setHeaderTitle($zoneName . "/" . $path);
        $headers = ['ObjectName', 'Path','Length'];
        $table->setHeaders($headers);
        foreach ($list as $file) {
            $row = [];
            foreach ($headers as $header) {
                $row[$header] = $file[$header];
            }
            $table->addRow($row);
        }
        $table->render();


        $io->success($this->getName() . ' success ' . $zoneName);
        return self::SUCCESS;
    }




}
