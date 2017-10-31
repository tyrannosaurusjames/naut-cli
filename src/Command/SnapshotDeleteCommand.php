<?php
namespace Guttmann\NautCli\Command;

use Guttmann\NautCli\Service\SnapshotService;
use Guttmann\NautCli\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SnapshotDeleteCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this->setName('snapshot:delete')
            ->setDescription('Deletes a snapshot')
            ->setHelp('Given a stack id this command will delete a snapshot with the provided id.' . PHP_EOL . 'Usage: <info>snapshot:delete <stack_id> <snapshot_id></info>')
            ->addArgument('stack', InputArgument::REQUIRED, 'The id of your stack')
            ->addArgument('snapshot_id', InputArgument::REQUIRED, 'The id of the snapshot to delete');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $stack = $input->getArgument('stack');
        $snapshotId = $input->getArgument('snapshot_id');

        $container = $this->getContainer();

        /** @var SnapshotService $snapshotService */
        $snapshotService = $container['naut.snapshot'];
        $success = $snapshotService->deleteSnapshot($stack, $snapshotId);

        if ($success) {
            $output->writeln('<info>Snapshot #' . $snapshotId . ' from stack ' . $stack . ' deleted successfully.</info>');
            return 0;
        } else {
            $output->writeln('<error>Failed to delete snapshot.</error>');
            return 1;
        }
    }

}
