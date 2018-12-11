<?php
namespace Guttmann\NautCli\Command;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use Guttmann\NautCli\ContainerAwareCommand;
use Guttmann\NautCli\Service\DeployLogService;
use Guttmann\NautCli\Service\DeployService;
use Guttmann\NautCli\Service\FetchLatestService;
use GuzzleHttp\Client;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeployCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this->setName('deploy')
            ->setDescription('Runs a deployment')
            ->setHelp('This command allows you to deploy the latest version of a branch to a specific environment within an instance.')
            ->addArgument('instance', InputArgument::REQUIRED, 'The shortcode for your instance')
            ->addArgument('environment', InputArgument::REQUIRED, 'The environment name')
            ->addArgument('branch', InputArgument::REQUIRED, 'The git branch to deploy');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        /** @var Client $client */
        $client = $container['naut.client'];

        $instanceId = $input->getArgument('instance');

        echo 'Fetching latest from git' . PHP_EOL;

        /** @var FetchLatestService $fetchService */
        $fetchService = $container['naut.fetch'];
        $fetchService->fetch($client, $instanceId);

        $environment = $input->getArgument('environment');
        $branch = $input->getArgument('branch');

        echo 'Triggering deployment' . PHP_EOL;

        /** @var DeployService $deployService */
        $deployService = $container['naut.deploy'];
        $deployLogLink = $deployService->deploy($client, $instanceId, $environment, $branch);

        echo 'Deployment triggered' . PHP_EOL;
        echo 'Found deploy log link: ' . $deployLogLink . PHP_EOL;

        echo 'Streaming deploy log' . PHP_EOL;

        /** @var DeployLogService $deployLogService */
        $deployLogService = $container['naut.deploy_log'];
        $success = $deployLogService->streamLog($client, $deployLogLink);

        if ($success) {
            $output->writeln('Deployment complete');
            return 0;
        } else {
            $output->writeln('Deployment failed');
            return 1;
        }
    }
}
