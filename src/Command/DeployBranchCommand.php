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

        $output->writeln('Fetching latest from git');

        /** @var FetchLatestService $fetchService */
        $fetchService = $container['naut.fetch'];
        $fetchService->fetch($client, $instanceId);

        $environment = $input->getArgument(self::ENVIRONMENT_ARG_NAME);
        $branch = $input->getArgument(self::BRANCH_ARG_NAME);

        $output->writeln('Triggering deployment');

        /** @var DeployService $deployService */
        $deployService = $container['naut.deploy'];
        $deployLogLink = $deployService->deployBranch($client, $instanceId, $environment, $branch);

        $output->writeln([
            'Deployment triggered',
            'Found deploy log link: ' . $deployLogLink,
            'Streaming deploy log'
        ]);

        /** @var DeployLogService $deployLogService */
        $deployLogService = $container['naut.deploy_log'];
        $success = $deployLogService->streamLog($client, $deployLogLink, $output);

        if ($success) {
            $output->writeln('Deployment complete');
            return 0;
        } else {
            $output->writeln('Deployment failed');
            return 1;
        }
    }
}
