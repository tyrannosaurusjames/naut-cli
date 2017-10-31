<?php
namespace Guttmann\NautCli\Command;

use Guttmann\NautCli\Service\SnapshotService;
use Guttmann\NautCli\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SnapshotListCommand extends ContainerAwareCommand
{

    private $compactOutput = false;

    protected function configure()
    {
        $this->setName('snapshot:list')
            ->setDescription('Shows a list of snapshots for a stack')
            ->setHelp('Given a stack id this command will list any snapshots that exist for that stack.' . PHP_EOL . 'Usage: snapshot:list <stack_id>')
            ->addArgument('stack', InputArgument::REQUIRED, 'The id of your stack')
            ->addOption('compact', 'c', InputOption::VALUE_NONE, 'Use compact output');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $stack = $input->getArgument('stack');
        $this->compactOutput = $input->getOption('compact');

        $container = $this->getContainer();

        /** @var SnapshotService $snapshotService */
        $snapshotService = $container['naut.snapshot'];
        $snapshots = $snapshotService->listSnapshots($stack);

        $output->writeln('Snapshots for stack: <info>' . $stack . '</info>');
        $this->printFormattedOutput($snapshots, $output);
    }

    private function printFormattedOutput($snapshots, OutputInterface $output)
    {
        $table = new Table($output);

        $table->setHeaders([
            'id',
            'environment',
            'mode',
            'size',
            'created'
        ]);

        foreach ($snapshots as $snapshot) {
            $id = $snapshot['id'];
            $environment = $snapshot['relationships']['source']['data'][0]['id'];
            $mode = $snapshot['attributes']['mode'];
            $size = $this->formatSizeUnits($snapshot['attributes']['size']);
            $created = $snapshot['attributes']['created'];

            $table->addRow([
                $id,
                $environment,
                $mode,
                $size,
                $created
            ]);
        }

        if ($this->compactOutput) {
            $table->setStyle('compact');
        }

        $table->render();
    }

    private function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }

}
