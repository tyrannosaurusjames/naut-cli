<?php
namespace Guttmann\NautCli\Command;

use Guttmann\NautCli\ContainerAwareCommand;
use Guttmann\NautCli\Service\DeployService;
use GuzzleHttp\Client;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeployPromoteFromUatCommand extends ContainerAwareCommand
{
    const STACK_ARG_NAME = 'stack';

    protected function configure()
    {
        $this->setName('deploy:promote-from-uat')
            ->setDescription('Promotes the latest release to UAT to the production environment for a stack')
            ->setHelp('This command allows you to deploy the latest UAT deployment to production for a given stack.')
            ->addArgument(self::STACK_ARG_NAME, InputArgument::REQUIRED, 'The shortcode for your stack');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        $stackId = $input->getArgument(self::STACK_ARG_NAME);

        /** @var Client $client */
        $client = $container['naut.client'];

        $output->writeln('Triggering deployment (promoting UAT to production)');

        /** @var DeployService $deployService */
        $deployService = $container['naut.deploy'];
        $deployLogLink = $deployService->promoteFromUat($client, $stackId);

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
