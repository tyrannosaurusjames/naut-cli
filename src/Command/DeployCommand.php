<?php
namespace Guttmann\NautCli\Command;

use Dotenv\Dotenv;
use Guttmann\NautCli\Service\DeployLogService;
use Guttmann\NautCli\Service\DeployService;
use Guttmann\NautCli\Service\FetchLatestService;
use Guttmann\NautCli\Service\LoginService;
use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeployCommand extends Command
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
        if (file_exists(getenv('HOME') . '/' . ENV_FILE)) {
            $dotenv = new Dotenv(getenv('HOME'), ENV_FILE);
            $dotenv->load();
        }

        $guzzleClient = new Client([
            'base_uri' => getenv('NAUT_URL'),
            'cookies' => true
        ]);

        echo 'Attempting login' . PHP_EOL;

        $loginService = new LoginService();
        $loginService->login($guzzleClient);

        echo 'Logged in' . PHP_EOL;

        $instanceId = $input->getArgument('instance');

        echo 'Fetching latest from git' . PHP_EOL;

        $fetchService = new FetchLatestService();
        $fetchService->fetch($guzzleClient, $instanceId);

        $environment = $input->getArgument('environment');
        $branch = $input->getArgument('branch');

        echo 'Triggering deployment' . PHP_EOL;

        $deployService = new DeployService();
        $deployLogLink = $deployService->deploy($guzzleClient, $instanceId, $environment, $branch);

        echo 'Deployment triggered' . PHP_EOL;
        echo 'Found deploy log link: ' . $deployLogLink . PHP_EOL;

        echo 'Streaming deploy log' . PHP_EOL;
        $deployLogService = new DeployLogService();
        $deployLogService->streamLog($guzzleClient, $deployLogLink);

        $output->writeln('Deployment complete');
    }

}
