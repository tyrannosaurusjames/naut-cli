<?php
namespace Guttmann\NautCli\Command;

use Guttmann\NautCli\Service\SnapshotLogService;
use Guttmann\NautCli\Service\SnapshotService;
use Guttmann\NautCli\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SnapshotCreateCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this->setName('snapshot:create')
            ->setDescription('Create a snapshot')
            ->setHelp('Create a new snapshot for the given environment')
            ->addArgument('stack', InputArgument::REQUIRED, 'The id of your stack')
            ->addArgument('environment', InputArgument::REQUIRED, 'The environment to create a snapshot of')
            ->addOption('mode', 'm', InputOption::VALUE_OPTIONAL, 'Type of snapshot to create: all, db, assets', 'all');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $stack = $input->getArgument('stack');
        $environment = $input->getArgument('environment');
        $mode = $input->getOption('mode');

        $validModes = [
            'all',
            'db',
            'assets'
        ];

        if (!in_array($mode, $validModes)) {
            $output->writeln('<error>Invalid mode provided.</error>');
            $output->writeln('<error>Choose one of: ' . implode(', ', $validModes) . '</error>');
            return 1;
        }

        $container = $this->getContainer();

        /** @var SnapshotService $snapshotService */
        $snapshotService = $container['naut.snapshot'];
        $logLink = $snapshotService->createSnapshot($stack, $environment, $mode);

        /** @var SnapshotLogService $snapshotLogService */
        $snapshotLogService = $container['naut.snapshot_log'];
        $snapshotSuccess = $snapshotLogService->streamLog($logLink, $output);

        if ($snapshotSuccess) {
            $output->writeln('Snapshot complete');
            return 0;
        } else {
            $output->writeln('Snapshot failed');
            return 1;
        }
    }

}
