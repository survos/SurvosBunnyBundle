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

    /**
     * @throws \Exception
     */
    public function __invoke(
        IO                                                                                          $io,
        #[Argument(description: 'path within zone')] string $remoteFilename = '',
        #[Argument(description: 'local directory')] ?string        $localDirOrFilename='',
        #[Option(name: 'zone', description: 'zone name')] ?string        $zoneName=null,
        #[Option(description: 'unzip')] ?bool        $unzip=null,

    ): int
    {
        $downloadFilename = pathinfo($remoteFilename, PATHINFO_FILENAME);
        if ($unzip && !str_ends_with($localDirOrFilename, '/')) {
            $localDirOrFilename .= "/";
        }

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

        // if unzip, download to the DIR and unzip
        if (pathinfo($localDirOrFilename, PATHINFO_EXTENSION) === 'zip') {
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

        $ret = $this->bunnyService->downloadFile($remoteShortName, $remotePath, $zoneName);

        file_put_contents($downloadPath, $ret->getContents());

        if ($unzip && pathinfo($remoteFilename, PATHINFO_EXTENSION) === 'zip') {
            $io->info("Unzipped $downloadPath to $localDirOrFilename");
            $this->unzipped($downloadPath, $localDirOrFilename);
            $io->info("Unzipped $downloadPath to $localDirOrFilename done!");
        }

        $io->success($this->getName() . ': downloaded to ' . $downloadPath);
        return self::SUCCESS;
    }

    /**
     * @param string $zipPath
     * @param string $destination
     * @throws \Exception
     */
    private function unzipped(string $zipPath, string $destination): void
    {
        $zip = new \ZipArchive();
        if ($zip->open($zipPath) === true) {
            $zip->extractTo($destination);
            $zip->close();
            return;
        }
        throw new \Exception("Could not unzip $zipPath to $destination");
    }
}
