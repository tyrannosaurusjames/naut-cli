<?php
namespace Guttmann\NautCli\Command;

use Guttmann\NautCli\ContainerAwareCommand;
use Guttmann\NautCli\Service\DeployLogService;
use Guttmann\NautCli\Service\DeployService;
use Guttmann\NautCli\Service\FetchLatestService;
use GuzzleHttp\Client;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeployBranchCommand extends ContainerAwareCommand
{
    const STACK_ARG_NAME = 'stack';
    const ENVIRONMENT_ARG_NAME = 'environment';
    const BRANCH_ARG_NAME = 'branch';

    protected function configure()
    {
        $this->setName('deploy:branch')
            ->setDescription('Deploys a specific branch to an environment')
            ->setHelp('This command allows you to deploy the latest version of a branch to a specific environment within a stack.')
            ->addArgument(self::STACK_ARG_NAME, InputArgument::REQUIRED, 'The shortcode for your stack')
            ->addArgument(self::ENVIRONMENT_ARG_NAME, InputArgument::REQUIRED, 'The environment name')
            ->addArgument(self::BRANCH_ARG_NAME, InputArgument::REQUIRED, 'The git branch to deploy');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        /** @var Client $client */
        $client = $container['naut.client'];

        $instanceId = $input->getArgument(self::STACK_ARG_NAME);

        echo 'Fetching latest from git' . PHP_EOL;

        /** @var FetchLatestService $fetchService */
        $fetchService = $container['naut.fetch'];
        $fetchService->fetch($client, $instanceId);

        $environment = $input->getArgument(self::ENVIRONMENT_ARG_NAME);
        $branch = $input->getArgument(self::BRANCH_ARG_NAME);

        echo 'Triggering deployment' . PHP_EOL;

        /** @var DeployService $deployService */
        $deployService = $container['naut.deploy'];
        $deployLogLink = $deployService->deployBranch($client, $instanceId, $environment, $branch);

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
