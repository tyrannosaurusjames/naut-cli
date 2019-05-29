<?php
namespace Guttmann\NautCli\Command;

use Guttmann\NautCli\ContainerAwareCommand;
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
        $output->writeln('Deployment goes here');
    }
}
