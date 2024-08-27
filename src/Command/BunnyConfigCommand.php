<?php

namespace Survos\BunnyBundle\Command;

use Psr\Log\LoggerInterface;
use Survos\BunnyBundle\Service\BunnyService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Yaml\Yaml;
use Zenstruck\Console\Attribute\Argument;
use Zenstruck\Console\Attribute\Option;
use Zenstruck\Console\InvokableServiceCommand;
use Zenstruck\Console\IO;
use Zenstruck\Console\RunsCommands;
use Zenstruck\Console\RunsProcesses;

#[AsCommand('bunny:config', 'Configure your application with the bunny keys')]
final class BunnyConfigCommand extends InvokableServiceCommand
{
    use RunsCommands;
    use RunsProcesses;

    public function __construct(
        private LoggerInterface       $logger,
        private ParameterBagInterface $bag,
        private readonly BunnyService $bunnyService,
        #[Autowire('%kernel.project_dir%')] private $projectDir,
    )
    {
        parent::__construct();
    }

    public function __invoke(
        IO                                                                                          $io,
        #[Argument(description: 'api key')] ?string        $apiKey=null,

    ): int
    {
        $apiKey = $apiKey??$this->bunnyService->getApiKey();
        if (!$apiKey) {
            $io->error("set api_key in config/packages/survos_bunny.yaml or pass it as the first parameter here.");
            return self::FAILURE;
        }

        // if no zone, we could prompt
        $baseApi = $this->bunnyService->getBaseApi($apiKey);
            $zones = $baseApi->listStorageZones()->getContents();
            $zoneConfig = [];
            $env[] = "BUNNY_API_KEY=$apiKey";
            $table = new Table($io);
            $table->setHeaderTitle("Zones");
            $headers = ['Name', 'StorageUsed','FilesStored','Id'];
            $table->setHeaders($headers);
            foreach ($zones as $zone) {
                $zoneName = strtoupper($zone['Name']);
                // inject slugger?  Or try to avoid dependencies?
                $zoneName = str_replace('-', '_', $zoneName);
                $zoneConfig[$zone['Name']] = [
                    'id' => $zone['Id'],
                    'region' => $zone['Region'],
                    'readonly_password' => "%env(BUNNY_{$zoneName}_READONLY_PASSWORD)%"
                ];
                $env[] = "BUNNY_{$zoneName}_READONLY_PASSWORD=" . $zone['ReadOnlyPassword'];
                $env[] = "BUNNY_{$zoneName}_PASSWORD=" . $zone['Password'];
                $row = [];
                foreach ($headers as $header) {
                    $row[$header] = $zone[$header];
                }
                $table->addRow($row);
            }
            $table->render();
            $config['survos_bunny'] = [
                'api_key' => $apiKey,
                'zones' => $zoneConfig,
            ];

            file_put_contents($filename = $this->projectDir . '/config/packages/survos_bunny.yaml', Yaml::dump($config, inline: 4   ));
        $io->success($filename . ' written, add these to your environment, e.g. .env.local or the vault');
        $io->writeln($env);

        return self::SUCCESS;
    }




}
