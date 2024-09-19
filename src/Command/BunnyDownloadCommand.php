<?php

namespace Survos\BunnyBundle\Command;

use Psr\Log\LoggerInterface;
use Survos\BunnyBundle\Service\BunnyService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Zenstruck\Console\Attribute\Argument;
use Zenstruck\Console\Attribute\Option;
use Zenstruck\Console\InvokableServiceCommand;
use Zenstruck\Console\IO;
use Zenstruck\Console\RunsCommands;
use Zenstruck\Console\RunsProcesses;

#[AsCommand('bunny:download', 'download remote bunny files')]
final class BunnyDownloadCommand extends InvokableServiceCommand
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
        #[Argument(description: 'object/file name')] string        $filename='',
        #[Argument(description: 'path name within zone')] string        $path='',
        #[Argument(description: 'zone name')] ?string        $zoneName=null,
        #[Argument(description: 'local directory')] ?string        $relativeDir='.',

    ): int
    {
        $downloadedDir = $relativeDir . $path;
        if (!is_dir($downloadedDir)) {
            mkdir($downloadedDir, 0777, true);
        }
        $downloadedFilename = $downloadedDir . $filename;
        // if no zone, we could prompt
        $baseApi = $this->bunnyService->getBaseApi();
        if (!$zoneName) {
            $zoneName = $this->bunnyService->getStorageZone();
        }

        // how to get the password from a zone?

        $ret = $this->bunnyService->downloadFile($filename, $path, $zoneName);
        file_put_contents($downloadedFilename, $ret->getContents());

        // @todo: download dir default, etc.

        $io->success($this->getName() . ': downloaded to ' . $downloadedFilename);
        return self::SUCCESS;
    }




}
