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
        private readonly BunnyService $bunnyService,
    ) {
        parent::__construct();
    }

    public function __invoke(
        IO $io,
        #[Argument(description: 'file name to upload')] string $filename = '',
        #[Argument(description: 'path name within zone')] string $path = '',
        #[Option(description: 'zone name')] ?string $zoneName = null,
        #[Option(description: 'dir')] ?string $relativeDir = './',
    ): int {
        $localDir = $relativeDir; //  . $path;
        if (!is_dir($relativeDir)) {
            $io->error($relativeDir . ' is not a directory');
            return self::FAILURE;
        }
        $localFilename = $localDir . $filename;
        // if no zone, we could prompt
        $baseApi = $this->bunnyService->getBaseApi();
        if (!$zoneName) {
            $zoneName = $this->bunnyService->getStorageZone();
        }

        // how to get the password from a zone?
        assert(file_exists($localFilename), $localFilename);
        $content = file_get_contents($localFilename);
        $filename = basename($localFilename);
        $this->logger->info("Uploading $filename to $zoneName ($path)");
//        dd($filename);
        $ret = $this->bunnyService->uploadFile(
            $filename,
            $zoneName,
            body: $content,
            path: $path
        );

        $io->success($localFilename . ' has been uploaded to ' .$filename );
        dump($ret);

        // @todo: download dir default, etc.

        $io->success($this->getName() . 'finished');
        return self::SUCCESS;
    }
}
