<?php
namespace Guttmann\NautCli\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigureCommand extends Command
{

    protected function configure()
    {
        $this->setName('configure')
            ->setDescription('Writes a config file (~/.naut.env) based on provided input')
            ->setHelp('Allows you to create the ~/.naut.env file that configuration items are loaded from.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $homeDir = getenv('HOME');

        if (file_exists($homeDir . '/' . ENV_FILE)) {
            $successfulAnswer = false;

            $output->writeln('Config file already exists at ' . $homeDir . '/' . ENV_FILE);
            $output->writeln('Do you want to overwrite? [yN]');

            while ($successfulAnswer === false) {
                $overwrite = strtolower(readline());

                if ($overwrite === 'n' || $overwrite === '') {
                    $output->writeln('Aborting configuration');
                    exit(0);
                } else if ($overwrite !== 'y') {
                    $output->writeln('Enter y or n');
                } else {
                    $successfulAnswer = true;
                }
            }
        }

        $output->writeln('Deploynaut URL (e.g. https://deploy.domain.com):');
        $url = rtrim(trim(readline()), '/');

        $output->writeln('Username:');
        $username = trim(readline());

        $output->writeln('Token:');
        shell_exec('stty -echo');
        $token = rtrim(fgets(STDIN), "\n");
        shell_exec('stty echo');

        $token = base64_encode($token);

        file_put_contents(
            $homeDir . '/' . ENV_FILE,
<<<ENV
NAUT_URL='$url'
NAUT_USERNAME='$username'
NAUT_TOKEN_B64='$token'

ENV
        );

        $output->writeln('Config file written at ' . $homeDir . '/' . ENV_FILE);
    }

}