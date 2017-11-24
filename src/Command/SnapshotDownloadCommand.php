<?php
namespace Guttmann\NautCli\Command;

use Guttmann\NautCli\ContainerAwareCommand;
use Guttmann\NautCli\Service\SnapshotDownloadService;
use Guttmann\NautCli\Service\SnapshotService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SnapshotDownloadCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this->setName('snapshot:download')
            ->setDescription('Download a snapshot')
            ->setHelp('Given a stack and a snapshot id this command will download a snapshot')
            ->addArgument('stack', InputArgument::REQUIRED, 'The id of your stack')
            ->addArgument('snapshot_id', InputArgument::REQUIRED, 'The id of the snapshot')
            ->addOption('destination', null, InputOption::VALUE_REQUIRED, 'File name and path to store download');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $stack = $input->getArgument('stack');
        $snapshotId = $input->getArgument('snapshot_id');

        $destination = $input->getOption('destination');

        $container = $this->getContainer();

        /** @var SnapshotService $snapshotService */
        $snapshotService = $container['naut.snapshot'];
        $snapshotData = $snapshotService->getSnapshotMetadata($stack, $snapshotId);

        $downloadLink = $snapshotData['links']['download_link'];

        /** @var SnapshotDownloadService $downloadService */
        $downloadService = $container['naut.download'];
        $downloadService->download($downloadLink, $output);
    }

}
