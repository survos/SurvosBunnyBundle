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

#[AsCommand('bunny:upload', 'upload remote bunny files')]
final class BunnyUploadCommand extends InvokableServiceCommand
{
    use RunsCommands;
    use RunsProcesses;

    public function __construct(
        private LoggerInterface $logger,
        private ParameterBagInterface $bag,
        #[Autowire('%kernel.project_dir%')] private string $projectDir,
        private readonly BunnyService $bunnyService,
    ) {
        parent::__construct();
        $this->setHelp(<<<END
# if local path is within project, mirror the file structure, otherwise pass it as second argument
bin/console bunny:upload local/path/filename.zip [remote/path/filename.zip] --zone=zoneName 
# change name and path
bin/console bunny:upload local/path/filename.zip remote/path/newFilename.zip 
# change remote path only, needs slash at the end!
bin/console bunny:upload local/path/filename.zip remote/path/

remote path is required unless a mirror of local
END
    );
    }

    public function __invoke(
        IO $io,
        #[Argument(description: 'file name to upload')] string $filename = '',
        #[Argument(description: 'path within zone')] string $remoteDirOrFilename = '',
        #[Option(name: 'zone', description: 'zone name')] ?string $zoneName = null,
//        #[Option(description: 'dir')] ?string $relativeDir = './',
    ): int {

        if (!file_exists($filename)) {
            $io->error("File $filename does not exist");
            return self::FAILURE;
        }

        $remoteFilename = pathinfo($filename, PATHINFO_BASENAME);
        if (!$remoteDirOrFilename) {
            // if the real path contains the project dir, this is a candidate for sync
            $realPath = realpath($filename);
            if (str_starts_with($realPath, $this->projectDir)) {
                $remotePath = pathinfo(
                    str_replace($this->projectDir . '/', '', $realPath), PATHINFO_DIRNAME);
                if ($remotePath === '.') {
                    $remotePath = '';
                }
            }
        } else {
            // keep the filename but change the dir
            if (str_ends_with($remoteDirOrFilename, '/')) {
                $remotePath = $remoteDirOrFilename;
            }
        }

        if (!$zoneName) {
            $zoneName = $this->bunnyService->getStorageZone();
        }

        $content = file_get_contents($filename);

        // remotePath should have the slash
        $io->error("Uploading $filename to $zoneName/$remotePath$remoteFilename");
        $ret = $this->bunnyService->uploadFile(
            $remoteFilename,
            $zoneName,
            body: $content,
            path: $remotePath
        );

        $io->info($ret->getStatusCode() . ' ' . $ret->getReasonPhrase());
        $io->success($filename . " has been uploaded to $zoneName/$remotePath$filename" );

        // @todo: download dir default, etc.

        $io->success($this->getName() . ' finished');
        return self::SUCCESS;
    }
}
