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
        #[Argument(description: 'path within zone')] string $remoteFilename = '',
        #[Argument(description: 'local directory')] ?string        $localDirOrFilename='',
        #[Option(name: 'zone', description: 'zone name')] ?string        $zoneName=null,

    ): int
    {
        $downloadFilename = pathinfo($remoteFilename, PATHINFO_FILENAME);

        if ($localDirOrFilename) {
            $shortFilename = pathinfo($localDirOrFilename, PATHINFO_BASENAME);
            if (str_ends_with($localDirOrFilename, '/')) {
                $downloadFilename = $localDirOrFilename . $shortFilename;
                $downloadDir = trim($localDirOrFilename, '/');
            } else {
                $downloadDir = pathinfo($downloadFilename, PATHINFO_DIRNAME);
                if ($downloadDir === '.') {
//                    $downloadDir = '';
                }
                $downloadFilename = $localDirOrFilename; // pathinfo($remoteFilename, PATHINFO_BASENAME);
                // it's a filename
            }
        } else {
            $downloadFilename = pathinfo($remoteFilename, PATHINFO_BASENAME);
            $downloadDir = pathinfo($remoteFilename, PATHINFO_DIRNAME);
        }

        if ($downloadDir && !is_dir($downloadDir)) {
            mkdir($downloadDir, 0777, true);
        }
        $downloadPath = $downloadDir . "/" . $downloadFilename;
        // if no zone, we could prompt
        if (!$zoneName) {
            $zoneName = $this->bunnyService->getStorageZone();
        }

        // how to get the password from a zone?

        $remotePath = pathinfo($remoteFilename, PATHINFO_DIRNAME);
        $remoteShortName = pathinfo($remoteFilename, PATHINFO_BASENAME);
        dump($remoteFilename, $remotePath, $remoteShortName);

        $ret = $this->bunnyService->downloadFile($remoteShortName, $remotePath, $zoneName);
        file_put_contents($downloadPath, $ret->getContents());

        // @todo: download dir default, etc.

        $io->success($this->getName() . ': downloaded to ' . $downloadPath);
        return self::SUCCESS;
    }




}
