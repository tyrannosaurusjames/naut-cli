<?php

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;

$application = new Application();

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

$application->addCommands([
    (new Guttmann\NautCli\Command\DeployCommand()),
    (new Guttmann\NautCli\Command\ConfigureCommand())
]);

define('ENV_FILE', '.naut.env');

$application->run();
