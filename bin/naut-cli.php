<?php

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Guttmann\NautCli\Kernel;

$kernel = new Kernel('cli', false);
$application = new Application($kernel);

$application->setName(
<<<TXT
\e[31m
 ▐ ▄  ▄▄▄· ▄• ▄▌▄▄▄▄▄     ▄▄· ▄▄▌  ▪  
•█▌▐█▐█ ▀█ █▪██▌•██      ▐█ ▌▪██•  ██ 
▐█▐▐▌▄█▀▀█ █▌▐█▌ ▐█.▪    ██ ▄▄██▪  ▐█·
██▐█▌▐█ ▪▐▌▐█▄█▌ ▐█▌·    ▐███▌▐█▌▐▌▐█▌
▀▀ █▪ ▀  ▀  ▀▀▀  ▀▀▀     ·▀▀▀ .▀▀▀ ▀▀▀\e[39m
TXT
);

$application->add(
    (new \Guttmann\NautCli\Command\ConfigureCommand()),
    (new \Guttmann\NautCli\Command\DeployCommand()),
    (new \Guttmann\NautCli\Command\SnapshotListCommand()),
    (new \Guttmann\NautCli\Command\SnapshotDeleteCommand()),
    (new \Guttmann\NautCli\Command\SnapshotCreateCommand())
);

/** @var \Symfony\Component\Console\Command\Command[] $commands */
$commands = $application->all();

$visibleCommands = [
    'help',
    'list',
    'about',
    'configure',
    'deploy',
    'snapshot:list',
    'snapshot:delete',
    'snapshot:create'
];

foreach ($commands as $command) {
    if (!in_array($command->getName(), $visibleCommands)) {
        $command->setHidden(true);
    }
}

define('ENV_FILE', '.naut.env');

$application->run();
